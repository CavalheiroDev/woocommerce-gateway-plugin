<?php

namespace Nix\WoocommerceNixpay\Abstracts;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class Block extends AbstractPaymentMethodType {

	protected $name;
	protected string $block_name = '';
	protected string $block_path = '';
	protected string $assets_path = '';
	protected string $styles_path = '';
	protected $path_utils;
	protected Gateway $gateway;

	public function __construct() {
		global $path_utils;

		$this->path_utils = $path_utils;
		$this->gateway    = $this->set_gateway();
	}

	public function initialize(): void {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );

	}

	public function get_payment_method_data(): array {
		return [];

	}

	public function get_payment_method_script_handles(): array {
		$this->enqueue_styles();

		$assets_path = $this->path_utils->plugin_abspath() . $this->assets_path;

		$dependencies = [];
		$version      = '1.0';

		$exists_assets = file_exists( $assets_path );
		if ( $exists_assets ) {
			$assets       = require( $assets_path );
			$dependencies = $assets['dependencies'];
			$version      = $assets['version'];
		}

		$block = $this->path_utils->plugin_url() . $this->block_path;

		wp_register_script(
			$this->block_name,
			$block,
			$dependencies,
			$version,
			true
		);

		return [ $this->block_name ];

	}

	protected function enqueue_styles(): void {
		$styles = $this->path_utils->plugin_url() . $this->styles_path;

		wp_enqueue_style(
			'wc-blocks-checkout-style',
			$styles,
			[],
			'1.0'
		);

	}

	protected function set_gateway(): ?Gateway {
		$payment_gateways_class = WC()->payment_gateways();
		$payment_gateways       = $payment_gateways_class->payment_gateways();


		return $payment_gateways[ $this->name ] ?? null;

	}

}