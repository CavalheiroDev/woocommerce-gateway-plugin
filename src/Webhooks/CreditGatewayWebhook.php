<?php

namespace Nix\WoocommerceNixpay\Webhooks;

use Nix\WoocommerceNixpay\Abstracts\Webhook;

class CreditGatewayWebhook extends Webhook {
	protected string $webhook_endpoint = 'nix_credit_gateway_webhook';

	public function process_webhook(): void {
		$body = $this->handle_body();
		if ( $body == null ) {
			$this->logger->error( 'Null request body received' );
			$this->error_response();

			return;
		}

		$order_id = explode( '--', $body["merchantOrderId"] )[1];

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			$this->logger->warning( "Order Not Found for ID $order_id" );
			$this->success_response();

			return;
		}

		$this->logger->info( "Webhook received for order_id: $order_id" );

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

		$newStatus = $matchPattern[ $body['payment']['paymentStatus'] ];
		if ( $newStatus == 'wc-completed' ) {
			$order->payment_complete();
		}
		$order->update_status( $newStatus );

		$this->logger->info( "Order $order_id status updated to $newStatus" );

		$this->success_response();

	}

}