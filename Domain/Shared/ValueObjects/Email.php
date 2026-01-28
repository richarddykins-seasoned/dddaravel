<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;
use Webmozart\Assert\Assert;

/**
 * Email Value Object
 *
 * Represents an email address with validation using webmozart/assert.
 * Demonstrates how to build robust value objects with domain validation.
 */
final class Email extends ValueObject implements JsonSerializable
{
    private function __construct(private readonly string $value)
    {
        $this->validate($value);
    }

    /**
     * Create a new Email value object.
     */
    public static function from(string $email): self
    {
        return new self($email);
    }

    /**
     * Get the email value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the local part of the email (before @).
     */
    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Get the domain part of the email (after @).
     */
    public function getDomain(): string
    {
        return explode('@', $this->value)[1];
    }

    /**
     * Get the email value for JSON serialization.
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * Validate the email using webmozart/assert.
     */
    private function validate(string $email): void
    {
        try {
            Assert::string($email, 'Email must be a string');
            Assert::notEmpty($email, 'Email cannot be empty');
            Assert::maxLength($email, 254, 'Email cannot be longer than 254 characters');
            Assert::regex($email, '/^[^\s@]+@[^\s@]+\.[^\s@]+$/', 'Email format is invalid');

            // Additional validation using filter_var for RFC compliance
            $filtered = filter_var($email, FILTER_VALIDATE_EMAIL);
            Assert::same($filtered, $email, 'Email format is invalid according to RFC standards');
        } catch (\Webmozart\Assert\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), 0, $e);
        }
    }
}
