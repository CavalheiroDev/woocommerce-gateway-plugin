<?php

namespace Nix\WoocommerceNixpay\Abstracts;

use Nix\WoocommerceNixpay\Providers\NixPayAuthentication;
use Nix\WoocommerceNixpay\Utils\Logger;
use WC_Admin_Settings;
use WC_Order;
use WC_Order_Item;
use WC_Payment_Gateway;

abstract class Gateway extends WC_Payment_Gateway {
	public const WOOCOMMERCE_CLASS_NAME = '';

	public const GATEWAY_ENDPOINT = '';

	public $id;
	public $icon;
	public $has_fields;
	public $supports;
	public $method_title = 'NixPay Payments';
	public $method_description = 'Allows NixPay payments';

	public $title;
	public bool $test_mode;
	public string $sandbox_base_url = 'https://apigateway-qa.nexxera.com';
	public string $production_base_url = 'https://apigateway.nexxera.com';
	protected Logger $logger;

	public function __construct() {
		$this->has_fields = false;
		$this->supports   = array(
			'products',
			'subscription',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'multiple_subscriptions'
		);

		$this->logger = new Logger();

	}

	abstract function get_blocks_params(): array;

	/**
	 * @param WC_Order_Item[] $order_items
	 *
	 * @return bool
	 */
	protected function any_recurrence( array $order_items ): bool {
		$any_recurrence       = false;
		$signature_group_slug = $this->get_option( 'signature_group_slug' );
		foreach ( $order_items as $order_item ) {
			$product_id = $order_item->get_product_id();
			if ( has_term( $signature_group_slug, 'product_cat', $product_id ) ) {
				$any_recurrence = true;
				break;
			}
		}

		return $any_recurrence;

	}

	abstract function recurrence_payment_payload( WC_Order $order, int $amount ): array;

	abstract function payment_payload( WC_Order $order, int $amount ): array;

	public function get_api_user(): string {
		if ( $this->test_mode ) {
			return $this->get_option( 'test_api_user' );
		}

		return $this->get_option( 'production_api_user' );
	}

	public function get_api_password(): string {
		if ( $this->test_mode ) {
			return $this->get_option( 'test_api_password' );
		}

		return $this->get_option( 'production_api_password' );

	}

	public function get_authentication_url(): string {
		return $this->get_base_url() . '/nix/cadun/empresas/auth';
	}

	public function get_gateway_url(): string {
		return $this->get_base_url() . $this::GATEWAY_ENDPOINT;
	}

	public function get_base_url(): string {
		if ( $this->test_mode ) {
			return $this->sandbox_base_url;
		} else {
			return $this->production_base_url;
		}
	}

	public function validate_test_api_password_field( $key, $value ) {
		if ( ! $this->test_mode ) {
			return $value;
		}

		$nix_pay_authentication = new NixPayAuthentication(
			authentication_url: $this->get_authentication_url(),
			username: $this->get_api_user(),
			password: $value
		);

		$response = $nix_pay_authentication->authenticate();
		if ( wp_remote_retrieve_response_code( $response ) > 299 or ! wp_remote_retrieve_body( $response ) ) {
			WC_Admin_Settings::add_error( 'Usuário ou senha de testes incorretos.' );
		}

		return $value;

	}

	public function validate_production_api_password_field( $key, $value ) {
		if ( $this->test_mode ) {
			return $value;
		}

		$nix_pay_authentication = new NixPayAuthentication(
			authentication_url: $this->get_authentication_url(),
			username: $this->get_api_user(),
			password: $value
		);

		$response = $nix_pay_authentication->authenticate();
		if ( wp_remote_retrieve_response_code( $response ) > 299 or ! wp_remote_retrieve_body( $response ) ) {
			WC_Admin_Settings::add_error( 'Usuário ou senha de produção incorretos.' );
		}

		return $value;

	}

	public function init_form_fields(): void {
		$this->form_fields = [
			'title'                   => [
				'title'       => 'Título',
				'type'        => 'text',
				'description' => 'Infome aqui o título que o usuário vê durante o checkout',
				'default'     => 'Cartão de Crédito',
			],
			'production_api_user'     => [
				'title'       => 'Usuário API de produção',
				'type'        => 'text',
				'description' => 'Credenciais criadas dentro do Nix Empresa em configurações > Integração via API'
			],
			'production_api_password' => [
				'title'       => 'Senha do usuário API de produção',
				'type'        => 'password',
				'description' => 'Credenciais criadas dentro do Nix Empresa em configurações > Integração via API'
			],
			'test_mode'               => [
				'title'       => 'Modo de teste',
				'type'        => 'checkbox',
				'description' => 'Habilita/Desabilita o modo de teste.',
			],
			'test_api_user'           => [
				'title'       => 'Usuário API de teste',
				'type'        => 'text',
				'description' => 'Caso não tenha conta de teste, solicite em nosso suporte'
			],
			'test_api_password'       => [
				'title'       => 'Senha do usuário API de teste',
				'type'        => 'password',
				'description' => 'Caso não tenha conta de teste, solicite em nosso suporte'
			]
		];
	}

}