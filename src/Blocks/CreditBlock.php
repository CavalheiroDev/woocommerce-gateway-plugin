<?php

namespace Nix\WoocommerceNixpay\Blocks;

use Nix\WoocommerceNixpay\Abstracts\Block;

class CreditBlock extends Block {
	protected $name = 'woo-nixpay-credit-gateway';
	protected string $block_name = 'woo-nixpay-credit-block';

	protected string $block_path = 'build/credit.block.js';
	protected string $assets_path = '/build/credit.block.asset.php';
	protected string $styles_path = '/assets/css/styles.min.css';

	public function get_payment_method_data(): array {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			'params'      => $this->gateway->get_blocks_params()
		];

	}


}