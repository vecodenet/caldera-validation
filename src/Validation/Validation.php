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
	 */
	protected array $conditions = [];

	/**
	 * Rules array
	 */
	protected array $rules = [];

	/**
	 * Error bag
	 */
	protected array $errors = [];

	/**
	 * Get conditions array
	 */
	public function getConditions(): array {
		return $this->conditions;
	}

	/**
	 * Get rules array
	 */
	public function getRules(): array {
		return $this->rules;
	}

	/**
	 * Check if rule exists
	 */
	public function hasRule(string $name): bool {
		return isset( $this->rules[$name] );
	}

	/**
	 * Get rule handler
	 */
	public function getRule(string $name): mixed {
		if (! isset( $this->rules[$name] ) ) {
			throw new InvalidArgumentException("Rule {$name} does not exist");
		}
		return $this->rules[$name];
	}

	/**
	 * Add condition
	 * @param  string $field   Field name
	 * @param  mixed  $rules   Condition rules
	 * @return $this
	 */
	public function condition(string $field, mixed $rules) {
		if (! isset( $this->conditions[$field] ) ) {
			$this->conditions[$field] = new Condition($this);
		}
		if ( is_array($rules) || is_string($rules) || $rules instanceof Closure ) {
			$this->conditions[$field]->rule($rules);
		} else {
			throw new InvalidArgumentException('Invalid rule type specified');
		}
		return $this;
	}

	/**
	 * Add rule
	 * @param  string $name    Rule name
	 * @param  mixed  $handler Rule handler
	 * @return $this
	 */
	public function rule(string $name, mixed $handler) {
		if ( is_string($handler) || $handler instanceof Closure ) {
			$this->rules[$name] = $handler;
		} else {
			throw new InvalidArgumentException('Invalid handler type specified');
		}
		return $this;
	}

	/**
	 * Validate fields
	 * @param  array $fields Fields array
	 * @param  bool  $bail   Bail flag
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
