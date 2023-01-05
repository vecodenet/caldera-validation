<?php

declare(strict_types = 1);

/**
 * Caldera Validation
 * Validation layer, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2023 Vecode. All rights reserved
 */

namespace Caldera\Tests\Validation;

use Closure;
use Exception;
use InvalidArgumentException;
use RuntimeException;

use PHPUnit\Framework\TestCase;

use Caldera\Validation\Condition;
use Caldera\Validation\RuleInterface;
use Caldera\Validation\Validation;
use Caldera\Validation\ValidationException;

class ValidationTest extends TestCase {

	public function testInvalidArgumentOnValidation() {
		$validation = new Validation();
		$this->expectException(InvalidArgumentException::class);
		$validation->condition('name', 5);
	}

	public function testInvalidArgumentOnCondition() {
		$condition = new Condition();
		$this->expectException(InvalidArgumentException::class);
		$condition->rule(5);
	}

	public function testAddConditions() {
		$validation = new Validation();
		$validation->condition('name', 'required')
			->condition('email', ['required', 'email'])
			->condition('first_name', 'required')
			->condition('last_name', 'required')
			->condition('password', 'required')
			->condition('password_confirm', ['required', 'same:password']);
		$conditions = $validation->getConditions();
		$this->assertIsArray($conditions);
		$this->assertCount(6, $conditions);
		$this->assertContainsOnlyInstancesOf(Condition::class, $conditions);
	}

	public function testAddClosureCondition() {
		$validation = new Validation();
		$validation->condition('name', function() {});
		$conditions = $validation->getConditions();
		$this->assertIsArray($conditions);
	}

	public function testAddCustomRuleCondition() {
		$validation = new Validation();
		$validation->condition('name', CustomRule::class);
		$conditions = $validation->getConditions();
		$this->assertIsArray($conditions);
	}

	public function testAddInvalidCustomRuleCondition() {
		$validation = new Validation();
		$this->expectException(RuntimeException::class);
		$validation->condition('name', Condition::class)->validate([]);
	}

	public function testValidateBail() {
		$validation = new Validation();
		$validation->condition('test', ['required', 'max:255']);
		$this->expectException(ValidationException::class);
		$validation->validate([], true);
	}

	public function testValidateInvalidType() {
		$validation = new Validation();
		$validation->condition('test', ['crispy', 'max:255']);
		$this->expectException(RuntimeException::class);
		$validation->validate([], true);
	}

	public function testValidateRequired() {
		$validation = new Validation();
		$validation->condition('test', 'required');
		$validation->validate(['test' => 'foo']);
		$this->expectException(ValidationException::class);
		$validation->validate([]);
	}

	public function testValidateAlpha() {
		$validation = new Validation();
		$validation->condition('test', 'alpha');
		$validation->validate(['test' => 'test']);
		$validation->validate(['test' => 'Test']);
		$validation->validate(['test' => 'TEST']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'test123']);
	}

	public function testValidateAlphaNum() {
		$validation = new Validation();
		$validation->condition('test', 'alphanum');
		$validation->condition('test', 'aLPhAnum');
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'test123!']);
	}

	public function testValidateNum() {
		$validation = new Validation();
		$validation->condition('test', 'num');
		$validation->validate(['test' => '123']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'test']);
	}

	public function testValidateSlug() {
		$validation = new Validation();
		$validation->condition('test', 'slug');
		$validation->validate(['test' => 'valid-slug']);
		$validation->validate(['test' => 'valid_slug']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'non valid slug']);
	}

	public function testValidateRegex() {
		$validation = new Validation();
		$validation->condition('test', 'regex:/[a-z0-9]{32}/i');
		$validation->validate(['test' => '0bc4a2771a9bd8d23053cbe022f21ea2']);
		$validation->validate(['test' => '0BC4A2771A9BD8D23053CBE022F21EA2']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'non matching']);
	}

	public function testValidateEmail() {
		$validation = new Validation();
		$validation->condition('test', 'email');
		$validation->validate(['test' => 'test@example.com']);
		$validation->validate(['test' => 'another.test@example.com']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'not an email']);
	}

	public function testValidateSame() {
		$validation = new Validation();
		$validation->condition('test', 'same:other');
		$validation->validate(['test' => 'FooBarBaz!', 'other' => 'FooBarBaz!']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'FooBarBaz!', 'other' => 'fOObARbAZ!']);
	}

	public function testValidateDifferent() {
		$validation = new Validation();
		$validation->condition('test', 'different:other');
		$validation->validate(['test' => 'FooBarBaz!', 'other' => 'fOObARbAZ!']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'FooBarBaz!', 'other' => 'FooBarBaz!']);
	}

	public function testValidateAfter() {
		$validation = new Validation();
		$validation->condition('test', 'after:1986-03-26');
		$validation->validate(['test' => '1987-01-01']);
		$validation->validate(['test' => '1991-01-01']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => '1912-04-14']);
	}

	public function testValidateBefore() {
		$validation = new Validation();
		$validation->condition('test', 'before:1986-03-26');
		$validation->validate(['test' => '1985-07-19']);
		$validation->validate(['test' => '1912-04-14']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => '1991-01-01']);
	}

	public function testValidateBetween() {
		$validation = new Validation();
		$validation->condition('test', 'between:5,10');
		# Test with integer, string and array
		$validation->validate(['test' => 6]);
		$validation->validate(['test' => 'Contosso']);
		$validation->validate(['test' => [1, 2, 3, 4, 5, 6]]);
		# Out of range integer
		try {
			$validation->validate(['test' => 12]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range string
		try {
			$validation->validate(['test' => 'Lorem ipsum dolor sit amet']);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range array
		try {
			$validation->validate(['test' => [1, 2, 3]]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Unsupported type
		try {
			$validation->validate(['test' => $this]);
			$this->fail('Should have thrown a ValidationException');
		} catch (ValidationException $e) {
			$this->assertIsArray( $e->getErrors() );
		} catch (Exception $e) {
			$this->fail('This should have been a ValidationException');
		}
	}

	public function testValidateMin() {
		$validation = new Validation();
		$validation->condition('test', 'min:3');
		# Test with integer, string and array
		$validation->validate(['test' => 6]);
		$validation->validate(['test' => 'Contosso']);
		$validation->validate(['test' => [1, 2, 3, 4, 5, 6]]);
		# Out of range integer
		try {
			$validation->validate(['test' => 2]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range string
		try {
			$validation->validate(['test' => 'No']);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range array
		try {
			$validation->validate(['test' => [1, 2]]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Unsupported type
		try {
			$validation->validate(['test' => $this]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
	}

	public function testValidateMax() {
		$validation = new Validation();
		$validation->condition('test', 'max:5');
		# Test with integer, string and array
		$validation->validate(['test' => 3]);
		$validation->validate(['test' => 'Yes']);
		$validation->validate(['test' => [1, 2, 3, 4]]);
		# Out of range integer
		try {
			$validation->validate(['test' => 6]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range string
		try {
			$validation->validate(['test' => 'Lorem ipsum']);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range array
		try {
			$validation->validate(['test' => [1, 2, 3, 4, 5, 6]]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Unsupported type
		try {
			$validation->validate(['test' => $this]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
	}

	public function testValidateSize() {
		$validation = new Validation();
		$validation->condition('test', 'size:3');
		# Test with integer, string and array
		$validation->validate(['test' => 3]);
		$validation->validate(['test' => 'Yes']);
		$validation->validate(['test' => [1, 2, 3]]);
		# Out of range integer
		try {
			$validation->validate(['test' => 6]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range string
		try {
			$validation->validate(['test' => 'Lorem ipsum']);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Out of range array
		try {
			$validation->validate(['test' => [1, 2, 3, 4, 5, 6]]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
		# Unsupported type
		try {
			$validation->validate(['test' => $this]);
			$this->fail('Should have thrown a ValidationException');
		} catch (Exception $e) {
			$this->assertInstanceOf(ValidationException::class, $e);
		}
	}

	public function testValidateArray() {
		$validation = new Validation();
		$validation->condition('test', 'array');
		$validation->validate(['test' => [1, 2, 3, 4, 5, 6]]);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => '1, 2, 3, 4, 5, 6']);
	}

	public function testValidateNumeric() {
		$validation = new Validation();
		$validation->condition('test', 'numeric');
		$validation->validate(['test' => '6']);
		$validation->validate(['test' => 6]);
		$validation->validate(['test' => '6.0']);
		$validation->validate(['test' => '-6']);
		$validation->validate(['test' => '-6.0']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'v6.0']);
	}

	public function testValidateString() {
		$validation = new Validation();
		$validation->condition('test', 'string');
		$validation->validate(['test' => 'Lorem ipsum']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => ['Lorem ipsum']]);
	}

	public function testValidateClosure() {
		$validation = new Validation();
		$validation->condition('test', function(array $fields, string $key, array $options, Closure $fail) {
			$value = $fields[$key] ?? '';
			$valid = $value && filter_var($value, FILTER_VALIDATE_URL);;
			if ( !$valid ) {
				$fail('Invalid Website specified');
			}
		});
		$validation->validate(['test' => 'https://vecode.net']);
		$validation->validate(['test' => 'https://caldera.vecode.net']);
		$validation->validate(['test' => 'http://localhost:8080']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'Lorem ipsum']);
	}

	public function testValidateCustomRule() {
		$validation = new Validation();
		$validation->condition('test', CustomRule::class);
		$validation->validate(['test' => 'https://vecode.net']);
		$validation->validate(['test' => 'https://caldera.vecode.net']);
		$validation->validate(['test' => 'http://localhost:8080']);
		$this->expectException(ValidationException::class);
		$validation->validate(['test' => 'Lorem ipsum']);
	}
}

class CustomRule implements RuleInterface {

	public function __invoke(array $fields, string $key, array $options, Closure $fail): void {
		$value = $fields[$key] ?? '';
		$valid = $value && filter_var($value, FILTER_VALIDATE_URL);;
		if ( !$valid ) {
			$fail('Invalid Website specified');
		}
	}
}
