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

trait ValidatesData {

	/**
	 * Validate a required field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateRequired(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$valid = !empty($value);
		if (! $valid ) {
			$fail('validation.required');
		}
	}

	/**
	 * Validate an alphabetic-only field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateAlpha(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = "/^[a-zA-Z]+$/";
		$valid = $value ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.alpha');
		}
	}

	/**
	 * Validate an alpha-numeric field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateAlphanum(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = "/^[a-zA-Z0-9]+$/";
		$valid = $value ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.alphanum');
		}
	}

	/**
	 * Validate a numeric-only field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateNum(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = "/^[0-9]+$/";
		$valid = $value ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.num');
		}
	}

	/**
	 * Validate an slug field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateSlug(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = "/^[a-z-_]+$/";
		$valid = $value ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.slug');
		}
	}

	/**
	 * Validate a field using a regular expression
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateRegex(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = $options[0] ?? null;
		$valid = $value && $pattern ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.regex');
		}
	}

	/**
	 * Validate an email field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateEmail(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$pattern = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
		$valid = $value ? preg_match($pattern, $value) == 1 : false;
		if (! $valid ) {
			$fail('validation.email');
		}
	}

	/**
	 * Validate a same-to-another field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateSame(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$compare = $options[1] ?? null;
		$other = $fields[$compare] ?? null;
		$valid = $value == $other;
		if (! $valid ) {
			$fail('validation.same');
		}
	}

	/**
	 * Validate a different-to-another field
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateDifferent(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$compare = $options[1] ?? null;
		$other = $fields[$compare] ?? null;
		$valid = $value != $other;
		if (! $valid ) {
			$fail('validation.different');
		}
	}

	/**
	 * Validate a field with a date after the one specified
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateAfter(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$time = $value ? strtotime($value) : time();
		$limit = $options[1] ?? null;
		$valid = $limit ? ($time - strtotime($limit) > 0) : false;
		if (! $valid ) {
			$fail('validation.after');
		}
	}

	/**
	 * Validate a field with a date before the one specified
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateBefore(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$time = $value ? strtotime($value) : time();
		$limit = $options[1] ?? null;
		$valid = $limit ? ($time - strtotime($limit) < 0) : false;
		if (! $valid ) {
			$fail('validation.before');
		}
	}

	/**
	 * Validate a field with a number or length between the given range
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateBetween(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$min = $options[1] ?? 0;
		$max = $options[2] ?? 0;
		if ( is_numeric($value) ) {
			$valid = $value >= $min && $value <= $max;
		} else if ( is_string($value) ) {
			$size = mb_strlen($value);
			$valid = $size >= $min && $size <= $max;
		} else if ( is_array($value) ) {
			$size = count($value);
			$valid = $size >= $min && $size <= $max;
		} else {
			$valid = false;
		}
		if (! $valid ) {
			$fail('validation.between');
		}
	}

	/**
	 * Validate a field with a number or length that is at least the given one
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateMin(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$specified = $options[1] ?? 0;
		if ( is_numeric($value) ) {
			$valid = $value >= $specified;
		} else if ( is_string($value) ) {
			$size = mb_strlen($value);
			$valid = $size >= $specified;
		} else if ( is_array($value) ) {
			$size = count($value);
			$valid = $size >= $specified;
		} else {
			$valid = false;
		}
		if (! $valid ) {
			$fail('validation.min');
		}
	}

	/**
	 * Validate a field with a number or length that is at most the given one
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateMax(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$specified = $options[1] ?? 0;
		if ( is_numeric($value) ) {
			$valid = $value <= $specified;
		} else if ( is_string($value) ) {
			$size = mb_strlen($value);
			$valid = $size <= $specified;
		} else if ( is_array($value) ) {
			$size = count($value);
			$valid = $size <= $specified;
		} else {
			$valid = false;
		}
		if (! $valid ) {
			$fail('validation.max');
		}
	}

	/**
	* Validate a field with a number or length that is exactly the given one
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateSize(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$specified = $options[1] ?? 0;
		if ( is_numeric($value) ) {
			$valid = $value == $specified;
		} else if ( is_string($value) ) {
			$size = mb_strlen($value);
			$valid = $size == $specified;
		} else if ( is_array($value) ) {
			$size = count($value);
			$valid = $size == $specified;
		} else {
			$valid = false;
		}
		if (! $valid ) {
			$fail('validation.size');
		}
	}

	/**
	 * Validate that a field is an array
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateArray(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$valid = is_array($value);
		if (! $valid ) {
			$fail('validation.array');
		}
	}

	/**
	 * Validate that a field is numeric
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateNumeric(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$valid = is_numeric($value);
		if (! $valid ) {
			$fail('validation.numeric');
		}
	}

	/**
	 * Validate that a field is a string
	 * @param  array   $fields  Fields array
	 * @param  string  $key     Field name
	 * @param  array   $options Rule options
	 * @param  Closure $fail    Failure callback
	 */
	public function validateString(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? null;
		$valid = is_string($value);
		if (! $valid ) {
			$fail('validation.string');
		}
	}
}
