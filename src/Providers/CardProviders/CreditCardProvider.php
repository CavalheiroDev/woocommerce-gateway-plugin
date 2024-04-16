<?php

namespace Nix\WoocommerceNixpay\Providers\CardProviders;

use Exception;
use Nix\WoocommerceNixpay\Abstracts\Provider;

class CreditCardProvider extends Provider {

	/**
	 * @throws Exception
	 */
	public function create_payment( string $payload ): array {
		$options = [
			'body'    => $payload,
			'headers' => $this->get_headers(),
		];

		$response = wp_remote_post( $this->payment_url, $options );

		$response_body = wp_remote_retrieve_body( $response );
		$status_code   = wp_remote_retrieve_response_code( $response );

		error_log( $response_body );

		if ( $status_code > 299 ) {
			if ( ! empty( $decoded_body->errors )
			     and $decoded_body->errors[0] == 'Data.Card.Number: The Number field is not a valid credit card number.' ) {
				$this->logger->info( 'Invalid credit card number' );
				throw new Exception( 'O cartão informado é inválido.' );

			}
			$this->logger->error( $response_body );
			throw new Exception( 'Ocorreu um erro no processamento do seu pagamento.' );
		}

		return json_decode( $response_body, true );


	}

}