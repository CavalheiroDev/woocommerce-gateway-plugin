<?php
/**
 * Plugin Name: WooCommerce NixPay
 * Description: Adds the NixPay Payments gateway to your WooCommerce website.
 * Version: 1.0.0
 *
 * Author: Nix
 * Author URI: https://www.minhanix.com.br/
 *
 * Text Domain: woocommerce-nixpay
 * Domain Path: /i18n/languages/
 *
 * Requires at least: 5.4
 * Tested up to: 6.4.3
 *
 * Requires Plugins: woocommerce
 * WC requires at least: 8.6.0
 * WC tested up to: 8.6
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' );

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Nix\WoocommerceNixpay\StartWooCommerceNixPay;
use Nix\WoocommerceNixpay\Utils\PathUtils;

add_action( 'before_woocommerce_init', function () {
	FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
	FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__ );
} );

StartWooCommerceNixPay::init();
if ( ! class_exists( 'PathUtils' ) ) {
	$GLOBALS['path_utils'] = new PathUtils();
}


