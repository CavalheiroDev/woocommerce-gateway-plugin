<?php

namespace Nix\WoocommerceNixpay\Providers;


use Exception;
use Nix\WoocommerceNixpay\Utils\Logger;

class NixPayAuthentication {
	public string $authentication_url;
	public string $username;
	public string $password;
	private Logger $logger;

	public function __construct( string $authentication_url, string $username, string $password ) {
		$this->authentication_url = $authentication_url;
		$this->username           = $username;
		$this->password           = $password;

		$this->logger = new Logger();

	}

	/**
	 * @throws Exception
	 */
	public function perform_authenticate(): string {
		$payload = wp_json_encode( [
			'user'     => $this->username,
			'password' => $this->password
		] );

		$options = [
			'body'    => $payload,
			'headers' => [
				'Content-Type' => 'application/json',
			]
		];

		$response = wp_remote_post( $this->authentication_url, $options );

		$response_body = wp_remote_retrieve_body( $response );

		if ( wp_remote_retrieve_response_code( $response ) > 299 or ! $response_body ) {
			$this->logger->error( $response_body );
			throw new Exception( 'Ocorreu um erro no processamento do seu pagamento.' );
		}

		$decoded_body = json_decode( wp_remote_retrieve_body( $response ) );
		$this->logger->info( 'Authenticated with success.' );

		return $decoded_body->access_token;

	}

}