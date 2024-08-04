<?php

namespace Ability\ComposerReader;

use Ability\ComposerReader\Contracts\ReaderInterface;
use InvalidArgumentException;
use RuntimeException;

class Reader implements ReaderInterface {

	/** @var string */
	private const COMPOSER_FILE = 'composer.json';

	/** @var string */
	private string $content;

	/**
	 * @param string $path
	 */
	private function __construct( string $path ) {
		if ( ! file_exists( $path ) ) {
			throw new InvalidArgumentException( sprintf( "Composer file not found: %s", $path ) );
		}

		$this->content = file_get_contents( $path );
	}

	/**
	 * @inheritDoc
	 */
	public function parse(): Context {
		$data = json_decode( $this->content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new RuntimeException( sprintf( "Error parsing composer file: %s", json_last_error_msg() ) );
		}

		return new Context( $data );
	}

	/**
	 * @param string $source
	 *
	 * @return Context
	 */
	static public function create( string $source ): Context {
		if ( ! str_contains( $source, self::COMPOSER_FILE ) ) {
			$source = rtrim( $source, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . self::COMPOSER_FILE;
		}

		return ( new self( $source ) )->parse();
	}
}