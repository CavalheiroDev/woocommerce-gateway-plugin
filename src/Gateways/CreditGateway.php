<?php

namespace Nix\WoocommerceNixpay\Gateways;

use Exception;
use Nix\WoocommerceNixpay\Abstracts\Gateway;
use Nix\WoocommerceNixpay\Providers\CardProviders\CreditCardProvider;
use WC_Admin_Settings;
use WC_Order;

class CreditGateway extends Gateway {
	public const WOOCOMMERCE_CLASS_NAME = 'Nix\WoocommerceNixpay\Gateways\CreditGateway';

	public const GATEWAY_ENDPOINT = '/nix-pay/v2/Orders/CardPayments/Authorize';
	private ?string $site_url;
	private CreditCardProvider $provider;

	public function __construct() {

		// WooCommerce settings
		parent::__construct();
		$this->id                 = 'woo-nixpay-credit-gateway';
		$this->icon               = plugin_dir_url( __DIR__ ) . '../assets/images/black-logo.png';
		$this->method_title       = 'NixPay - Cartão de Crédito';
		$this->method_description = 'Permita que seus clientes paguem com cartão de crédito.';

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Administrator settings
		$this->title     = $this->get_option( 'title', 'Cartão de Crédito' );
		$this->test_mode = $this->get_option( 'test_mode', 'no' ) == 'yes';

		$this->site_url = home_url( '/wc-api/nix_credit_gateway_webhook' );
		$this->provider = new CreditCardProvider(
			payment_url: $this->get_gateway_url(),
			authentication_url: $this->get_authentication_url(),
			username: $this->get_api_user(),
			password: $this->get_api_password()
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );


	}

	public function init_form_fields(): void {
		parent::init_form_fields();
		$this->form_fields += [
			'total_installments'      => [
				'title'             => 'Parcelas',
				'type'              => 'number',
				'description'       => 'Informe aqui o numero de parcelas que deseja oferecer em sua loja',
				'default'           => 12,
				'custom_attributes' => array(
					'min' => 1,
					'max' => 12
				)
			],
			'signature_group_slug'    => [
				'title'       => 'Slug da categoria de assinatura',
				'type'        => 'text',
				'description' => 'Informe aqui o Slug da categoria de assinatura. Para informar o plano da recorrência do Nix Empresas, 
                é necessário criar uma tag com o nome do plano e associar aos seus produtos.',
			],
			'recurrence_default_plan' => [
				'title'       => 'Nome do plano no Nix Empresas.',
				'type'        => 'text',
				'description' => ''
			]
		];
	}

	public function validate_total_installments_field( $key, $value ) {
		if ( $value == '' or $value == null ) {
			WC_Admin_Settings::add_error( 'É necessário informar o número de parcelas' );
			$value = 1;
		}

		return $value;

	}

	/**
	 * @throws Exception
	 */
	public function process_payment( $order_id ): array {
		$this->logger->info( 'Starting payment processing' );

		$order = wc_get_order( $order_id );

		$has_recurrence = $this->any_recurrence( $order->get_items() );
		if ( $has_recurrence ) {
			$payload = $this->recurrence_payment_payload( $order, 0 );
		} else {
			$amount  = number_format( $order->get_total() * 100.0, 0, '.', '' );
			$payload = $this->payment_payload( $order, $amount );
		}

		$encoded_payload = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );

		$this->logger->debug( $encoded_payload );

		$response      = $this->provider->create_payment( $encoded_payload );
		$payment_token = $response['payment']['paymentToken'];

		$order->add_payment_token( $payment_token );
		$order->add_order_note( "PaymentToken: " . $payment_token );

		WC()->cart->empty_cart();

		$this->logger->info( "Payment for order ID {$order_id} was successful." );

		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		];

	}

	public function payment_payload( WC_Order $order, int $amount ): array {
		$zip_code = str_replace( '-', '', $order->get_billing_postcode() );

		return array(
			'merchantOrderId' => "woocommerceOrder-{$order->get_id()}",
			'transactionType' => 1,
			'callbackUrl'     => $this->site_url,
			'returnUrl'       => $this->site_url,
			'customer'        => array(
				"tag"          => $order->get_formatted_billing_full_name(),
				"name"         => $order->get_formatted_billing_full_name(),
				"identity"     => $_POST['holder_document_number'],
				"identityType" => $_POST['holder_document_type'],
				"email"        => $order->get_billing_email(),
				"birthdate"    => "2002-08-27T00:00:00",
				"address"      => array(
					"country"    => $order->get_billing_country(),
					"zipCode"    => $zip_code,
					"number"     => "01",
					"street"     => $order->get_billing_address_1(),
					"complement" => $order->get_billing_address_2(),
					"city"       => $order->get_billing_city(),
					"state"      => $order->get_billing_state(),
				)
			),
			'amount'          => $amount,
			'capture'         => true,
			'installments'    => $_POST['installments_transaction'],
			'card'            => array(
				'number'         => $_POST['card_number'],
				'securityCode'   => $_POST['card_security_code'],
				'expirationDate' => array(
					'year'  => '20' . $_POST['expiration_card_year'],
					'month' => $_POST['expiration_card_month']
				),
				'holder'         => array(
					'name'         => $_POST['holder_name'],
					'socialNumber' => $_POST['holder_document_number']
				)
			)
		);
	}

	private function has_recurrence_product( $product_id ): bool {
		$categories = get_the_terms( $product_id, 'product_cat' );
		foreach ( $categories as $category ) {
			$category_slug = $category->slug;
			if ( $category_slug == $this->get_option( 'signature_group_slug' ) ) {
				return true;
			}
		}

		return false;

	}

	public function recurrence_payment_payload( WC_Order $order, int $amount ): array {
		$payload = $this->payment_payload( $order, $amount );

		$payload['recurrence'] = [
			'merchantPlanId' => $this->get_option( 'recurrence_default_plan' ),
			'items'          => []
		];

		foreach ( $order->get_items() as $item_id => $item ) {
			$item_amount = number_format( $item->get_data()['total'] * 100.0, 0, '.', '' );


			$recurrence_item = [
				'description' => $item->get_name(),
				'amount'      => $item_amount,
				'quantity'    => $item->get_quantity(),
				'isRecurrent' => $this->has_recurrence_product( $item['product_id'] )
			];

			$payload['recurrence']['items'][] = $recurrence_item;

		}

		return $payload;
	}

	public function get_blocks_params(): array {
		global $woocommerce;

		return [
			'test_mode'          => $this->test_mode,
			'total_installments' => $this->get_option( 'total_installments', 12 ),
			'total_cart_amount'  => $woocommerce->cart->total,
		];

	}

}