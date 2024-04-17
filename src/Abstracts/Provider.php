<?php

namespace Nix\WoocommerceNixpay\Abstracts;

use Exception;
use Nix\WoocommerceNixpay\Providers\NixPayAuthentication;
use Nix\WoocommerceNixpay\Utils\Logger;

abstract class Provider {
	protected string $base_url;
	protected string $path;
	protected NixPayAuthentication $authentication_provider;
	protected Logger $logger;

	public function __construct( string $base_url, string $authentication_url, string $username, string $password ) {
		$this->base_url                = $base_url;
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

	protected function get_url( array $args = [] ): string {
		$formatted_args = array_map( function ( $arg ) {
			return strval( $arg );
		}, $args );

		$args_ = '/' . implode( '/', $formatted_args );

		return "{$this->base_url}/{$this->path}{$args_}";
	}

}