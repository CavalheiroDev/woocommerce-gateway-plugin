<?php

namespace Nix\WoocommerceNixpay\Abstracts;

use Nix\WoocommerceNixpay\Utils\Logger;

abstract class Webhook {

	protected string $webhook_endpoint;
	protected Logger $logger;

	public function __construct() {
		$this->logger = new Logger();

	}

	public function register_webhook(): void {
		add_action( 'woocommerce_api_' . strtolower( $this->webhook_endpoint ), [ $this, 'process_webhook' ] );

	}

	protected function handle_body(): ?array {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return null;
		}
		$body = file_get_contents( 'php://input' );
		$data = trim( $body );

		return json_decode( $data, true );

	}

	abstract function process_webhook(): void;

	protected function success_response(): void {
		header( "HTTP/1.1 204 NO CONTENT", response_code: 204 );

	}

	protected function error_response(): void {
		header( "HTTP/1.1 400 BAD REQUEST", response_code: 400 );

	}

}