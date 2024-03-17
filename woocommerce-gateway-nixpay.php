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
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC NixPay Payment gateway plugin class.
 *
 * @class WC_NixPay_Payments
 */
class WC_NixPay_Payments
{

    /**
     * Plugin bootstrapping.
     */
    public static function init()
    {

        // NixPay Payments gateway class.
        add_action('plugins_loaded', array(__CLASS__, 'includes'), 0);

        add_action('plugins_loaded', array(__CLASS__, 'create_subscriber_premium_role'), 1);

        // Make the NixPay Payments gateway available to WC.
        add_filter('woocommerce_payment_gateways', array(__CLASS__, 'add_gateway'));

        // Registers WooCommerce Blocks integration.
        add_action('woocommerce_blocks_loaded', array(__CLASS__, 'woocommerce_gateway_nixpay_woocommerce_block_support'));

        add_action('rest_api_init', array(__CLASS__, 'add_custom_endpoints'));

    }

    public static function create_subscriber_premium_role(): void
    {
        $role = get_role('subscriber_premium');
        if (!$role) {
            add_role('subscriber_premium', 'Assinante Premium', [
                'read' => true,
            ]);
        }
    }

    public static function add_custom_endpoints()
    {
        require_once(plugin_dir_path(__FILE__) . 'includes/endpoints/CreditWebhookCallback.php');
        $credit_webhook = new CreditWebhookCallback();
        $credit_webhook->register_routes();

    }

    /**
     * Add the NixPay Payment gateway to the list of available gateways.
     *
     * @param array
     */
    public static function add_gateway($gateways)
    {
        $gateways[] = 'WC_Gateway_NixPay';
        return $gateways;
    }

    /**
     * Plugin includes.
     */
    public static function includes()
    {

        // Make the WC_Gateway_NixPay class available.
        if (class_exists('WC_Payment_Gateway')) {
            require_once 'includes/class-wc-gateway-nixpay.php';
        }
    }

    /**
     * Plugin url.
     *
     * @return string
     */
    public static function plugin_url()
    {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Plugin url.
     *
     * @return string
     */
    public static function plugin_abspath()
    {
        return trailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Registers WooCommerce Blocks integration.
     *
     */
    public static function woocommerce_gateway_nixpay_woocommerce_block_support()
    {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            require_once 'includes/blocks/class-wc-nixpay-payments-blocks.php';
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new WC_Gateway_NixPay_Blocks_Support());
                }
            );
        }
    }
}

WC_NixPay_Payments::init();
