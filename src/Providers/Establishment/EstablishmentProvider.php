<?php

namespace Nix\WoocommerceNixpay\Providers\Establishment;

use Nix\WoocommerceNixpay\Abstracts\Provider;

class EstablishmentProvider extends Provider {
	protected string $path = 'nix-pay/v2/RecurrencePlans';


	/**
	 * @throws \Exception
	 */
	public function get_plans(): ?array {
		$url = $this->get_url( [] );

		$options = [
			'headers' => $this->get_headers(),
			'timeout' => 30
		];

		$response = wp_remote_get( $url, $options );

		$response_body = wp_remote_retrieve_body( $response );
		$status_code   = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 or ! $response_body ) {
			return null;
		}

		return json_decode( $response_body, true );


	}

}