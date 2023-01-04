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
use RuntimeException;

use Caldera\Validation\RuleInterface;
use Caldera\Validation\ValidatesData;

class Condition {

	use ValidatesData;

	/**
	 * Rules array
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Error bag
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Get errors
	 * @return array
	 */
	public function getErrors(): array {
		return $this->errors;
	}

	/**
	 * Add a new rule
	 * @param  mixed  $rule    Rule to add
	 * @return $this
	 */
	public function rule($rule) {
		if ( is_array($rule) ) {
			foreach ($rule as $item) {
				$this->rule($item);
			}
		} else if ( is_string($rule) || $rule instanceof Closure ) {
			if ( is_string($rule) ) {
				if ( preg_match('/^([^:]+):?(.*)$/', $rule, $matches) === 1 ) {
					$type = trim( $matches[1] );
					$options = trim( $matches[2] ?? null );
					$options = $options ? preg_split('/,\s*/', $options) : null;
					if ($options) {
						array_unshift( $options, $matches[2] );
					}
					$this->rules[] = [
						'type' => $type,
						'options' => $options,
					];
				} else {
					throw new InvalidArgumentException('Invalid rule expression specified');
				}
			} else {
				$this->rules[] = [
					'type' => $rule,
					'options' => null,
				];
			}
		} else {
			throw new InvalidArgumentException('Invalid rule type specified');
		}
		return $this;
	}

	/**
	 * Check condition
	 * @param  array  $fields Fields array
	 * @param  string $key    Field key
	 * @param  bool   $bail   Bail flag
	 * @return bool
	 */
	public function check(array $fields, string $key, bool $bail = false): bool {
		$passed = true;
		foreach ($this->rules as $rule) {
			$ret = true;
			$type = $rule['type'] ?? null;
			$options = $rule['options'] ?? [];
			$message = $rule['message'] ?? null;
			# Resolve the callable
			$callable = null;
			if ( $type instanceof Closure ) {
				$callable = $type;
			} else if ( is_string($type) ) {
				if ( class_exists($type) ) {
					$instance = new $type();
					if ($instance instanceof RuleInterface) {
						$callable = $instance;
					} else {
						throw new RuntimeException('Must implement RuleInterface');
					}
				} else {
					$method = sprintf('validate_%s', $type);
					$method = lcfirst( str_replace( ' ', '', ucwords( str_replace( ['-', '_'], ' ', $method ) ) ) );
					if ( method_exists($this, $method) ) {
						$callable = [$this, $method];
					} else {
						throw new RuntimeException('Unknown rule type');
					}
				}
			}
			# Call the callable, if any
			if ( is_callable( $callable ) ) {
				call_user_func($callable, $fields, $key, $options, function($str = '') use (&$message, &$ret) {
					$message = $str;
					$ret = false;
				});
			}
			# Check result and add to error bag if required
			if (! $ret ) {
				$passed = false;
				if (! isset( $this->errors[$key] ) ) {
					$this->errors[$key] = [];
				}
				$this->errors[$key][] = $message ?? ( is_object($type) ? sprintf('Failed validation for %s rule', get_class($type)) : sprintf('validation.%s', $type) );
				if ($bail) {
					break;
				}
			}
		}
		return $passed;
	}
}
