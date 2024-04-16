<?php

namespace Nix\WoocommerceNixpay\Utils;

use WC_Logger;

class Logger {

	public const FILE_NAME = 'NixPay-Payments';
	private WC_Logger $logger;

	public function __construct() {
		$this->logger = wc_get_logger();

	}

	public function debug( $message ): void {
		$this->logger->debug( $message, [ 'source' => $this::FILE_NAME ] );
	}

	public function info( $message ): void {
		$this->logger->info( $message, [ 'source' => $this::FILE_NAME ] );
	}

	public function notice( $message ): void {
		$this->logger->notice( $message, [ 'source' => $this::FILE_NAME ] );

	}

	public function warning( $message ): void {
		$this->logger->warning( $message, [ 'source' => $this::FILE_NAME ] );

	}

	public function alert( $message ): void {
		$this->logger->alert( $message, [ 'source' => $this::FILE_NAME ] );

	}

	public function error( $message ): void {
		$this->logger->error( $message, [ 'source' => $this::FILE_NAME ] );

	}


	public function critical( $message ): void {
		$this->logger->critical( $message, [ 'source' => $this::FILE_NAME ] );

	}

	public function emergency( $message ): void {
		$this->logger->emergency( $message, [ 'source' => $this::FILE_NAME ] );

	}


}