<?php

namespace Nix\WoocommerceNixpay\Utils;

class PathUtils {

	public function plugin_abspath(): string {
		return untrailingslashit( rtrim( plugin_dir_path( plugin_dir_path( __FILE__ ) ), '/src' ) );
	}

	public function plugin_url(): string {
		return trailingslashit( rtrim( plugin_dir_url( plugin_dir_path( __FILE__ ) ), '/src' ) );

	}

}