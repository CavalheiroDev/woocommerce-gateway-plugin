<?php

class CreditWebhookCallback extends WP_REST_Controller
{

    public const VERSION = '1';

    public const NAMESPACE = 'nix/v' . self::VERSION;

    public const ENDPOINT = '/credit-webhook';


    public function register_routes()
    {
        register_rest_route(self::NAMESPACE, self::ENDPOINT, [
            'methods' => 'POST',
            'callback' => [$this, 'webhook'],
            'permission_callback' => [$this, 'permission_handler'],
            'args' => []
        ]);

    }

    public function webhook(WP_REST_Request $request): WP_REST_Response
    {

        $body = $request->get_json_params();

        $order_id = explode('-', $body["merchantOrderId"])[1];

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("Order Not Found for ID $order_id");
            return new WP_REST_Response([], 204);
        }

        error_log("Webhook receivied for order_id: $order_id");

        $matchPattern = [
            1 => 'wc-pending',
            2 => 'wc-processing',
            3 => 'wc-cancelled',
            5 => 'wc-pending',
            6 => 'wc-refunded',
            7 => 'wc-failed',
            8 => 'wc-failed',
            9 => 'wc-failed',
        ];

        $newStatus = $matchPattern[$body["payment"]["paymentStatus"]];
        if ($newStatus == 'wc-completed') {
            $order->payment_complete();
        }
        $order->update_status($newStatus);

        error_log("Order $order_id status updated to $newStatus");

        return new WP_REST_Response([], 204);

    }

    public function permission_handler(): bool
    {

        return true;

    }

}