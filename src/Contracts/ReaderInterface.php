<?php

namespace Ability\ComposerReader\Contracts;

use Ability\ComposerReader\Context;

interface ReaderInterface {

	/**
	 * @return Context
	 */
	public function parse(): Context;
}