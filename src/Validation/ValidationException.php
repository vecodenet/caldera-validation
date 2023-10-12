<?php

declare(strict_types = 1);

/**
 * Caldera Validation
 * Validation layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2023 Vecode. All rights reserved
 */

namespace Caldera\Validation;

use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException {

	/**
	 * Error bag
	 */
	protected array $errors;

	/**
	 * Constructor
	 * @param array $errors Error bag
	 */
	public function __construct(array $errors, string $message = '', int $code = 0, ?Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->errors = $errors;
	}

	/**
	 * Get errors instance
	 */
	public function getErrors(): array {
		return $this->errors;
	}
}
