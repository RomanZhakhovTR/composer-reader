<?php

namespace Ability\ComposerReader;

use ArrayAccess;
use JsonSerializable;
use RuntimeException;

class Context implements ArrayAccess, JsonSerializable {

	/** @var string */
	private string $delimiter = '.';

	/**
	 * @param array $items
	 */
	public function __construct( private readonly array $items ) {

	}

	/**
	 * @param array $array
	 * @param string|int $key
	 *
	 * @return bool
	 */
	protected function exists( array $array, string|int $key ): bool {
		return array_key_exists( $key, $array );
	}

	/**
	 * @param string|null $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public function get( string $key = null, mixed $default = null ): mixed {
		if ( $key === null ) {
			return $this->items;
		}

		if ( $this->exists( $this->items, $key ) ) {
			return $this->items[ $key ];
		}

		if ( ! is_string( $key ) || ! str_contains( $key, $this->delimiter ) ) {
			return $default;
		}

		$items = $this->items;

		foreach ( explode( $this->delimiter, $key ) as $segment ) {
			if ( ! is_array( $items ) || ! $this->exists( $items, $segment ) ) {
				return $default;
			}

			$items = &$items[ $segment ];
		}

		return $items;
	}

	/**
	 * @param mixed $keys
	 *
	 * @return bool
	 */
	public function has( mixed $keys ): bool {
		$keys = (array) $keys;

		if ( ! $this->items || $keys === [] ) {
			return false;
		}

		foreach ( $keys as $key ) {
			$items = $this->items;

			if ( $this->exists( $items, $key ) ) {
				continue;
			}

			foreach ( explode( $this->delimiter, $key ) as $segment ) {
				if ( ! is_array( $items ) || ! $this->exists( $items, $segment ) ) {
					return false;
				}

				$items = $items[ $segment ];
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( mixed $offset ): bool {
		return $this->has( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( mixed $offset ): mixed {
		return $this->get( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( mixed $offset, mixed $value ): void {
		throw new RuntimeException( 'Array modification not allowed' );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( mixed $offset ): void {
		throw new RuntimeException( 'Array modification not allowed' );
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): array {
		return $this->items;
	}
}