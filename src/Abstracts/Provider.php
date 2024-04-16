<?php

namespace Nix\WoocommerceNixpay\Abstracts;

use Exception;
use Nix\WoocommerceNixpay\Providers\NixPayAuthentication;
use Nix\WoocommerceNixpay\Utils\Logger;

abstract class Provider {
	protected string $payment_url;
	protected NixPayAuthentication $authentication_provider;
	protected Logger $logger;

	public function __construct( string $payment_url, string $authentication_url, string $username, string $password ) {
		$this->payment_url             = $payment_url;
		$this->authentication_provider = new NixPayAuthentication( $authentication_url, $username, $password );

		$this->logger = new Logger();

	}

	/**
	 * @throws Exception
	 */
	protected function get_headers(): array {
		return [
			'Content-Type'  => 'application/json',
			'Authorization' => $this->authentication_provider->perform_authenticate(),
			'RequestId'     => wp_generate_uuid4(),
		];

	}

	abstract function create_payment( string $payload ): array;
}