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
use InvalidArgumentException;

use Caldera\Validation\Condition;

class Validation {

	/**
	 * Conditions array
	 * @var array
	 */
	protected $conditions = [];

	/**
	 * Error bag
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Get conditions array
	 * @return array
	 */
	public function getConditions(): array {
		return $this->conditions;
	}

	/**
	 * Add condition
	 * @param  string $field   Field name
	 * @param  mixed  $rules   Condition rules
	 * @return $this
	 */
	public function condition(string $field, $rules) {
		if (! isset( $this->conditions[$field] ) ) {
			$this->conditions[$field] = new Condition();
		}
		if ( is_array($rules) || is_string($rules) || $rules instanceof Closure ) {
			$this->conditions[$field]->rule($rules);
		} else {
			throw new InvalidArgumentException('Invalid rule type specified');
		}
		return $this;
	}

	/**
	 * Validate fields
	 * @param  array $fields Fields array
	 * @param  bool  $bail   Bail flag
	 * @return bool
	 */
	public function validate(array $fields, bool $bail = false): bool {
		$this->errors = [];
		$passed = true;
		foreach ($this->conditions as $key => $condition) {
			$ret = $condition->check($fields, $key, $bail);
			if (! $ret ) {
				$passed = false;
				$this->errors = array_merge($this->errors, $condition->getErrors());
				if ($bail) {
					break;
				}
			}
		}
		if (! $passed ) {
			throw new ValidationException($this->errors, 'Validation not passed');
		}
		return $passed;
	}
}
