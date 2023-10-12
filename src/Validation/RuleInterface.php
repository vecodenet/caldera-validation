<?php

declare(strict_types = 1);

/**
 * Caldera Validation
 * Validation layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2023 Vecode. All rights reserved
 */

namespace Caldera\Validation;

use Closure;

interface RuleInterface {

	/**
	 * Invoke rule
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field key
	 * @param  array   $options Options array
	 * @param  Closure $fail    Failure callback
	 */
	public function __invoke(array $fields, string $key, array $options, Closure $fail): void;
}
