<?php

namespace Nix\WoocommerceNixpay;

use Nix\WoocommerceNixpay\Blocks\CreditBlock;
use Nix\WoocommerceNixpay\Gateways\CreditGateway;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Nix\WoocommerceNixpay\Webhooks\CreditGatewayWebhook;

class StartWooCommerceNixPay {

	public static function init(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'create_subscriber_premium_role' ), 1 );
		add_action( 'plugins_loaded', array( __CLASS__, 'register_webhooks' ), 2 );
		add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'register_gateways' ) );
		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'register_blocks' ) );

	}

	public static function register_webhooks(): void {
		$credit_webhook = new CreditGatewayWebhook();
		$credit_webhook->register_webhook();

	}

	public static function register_gateways( array $gateways ): array {
		$gateways[] = CreditGateway::WOOCOMMERCE_CLASS_NAME;

		return $gateways;

	}

	public static function register_blocks(): void {
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new CreditBlock() );
			}
		);

	}

	public static function create_subscriber_premium_role(): void {
		$role = get_role( 'subscriber_premium' );
		if ( ! $role ) {
			add_role(
				'subscriber_premium',
				'Assinante Premium',
				[
					'read' => true,
				]
			);
		}

	}

}