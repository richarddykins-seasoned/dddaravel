<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * Base Value Object class following DDD principles with webmozart/assert.
 *
 * Value Objects represent domain concepts that are identified by their value
 * rather than their identity. They are immutable and should be compared by
 * their value, not by reference.
 *
 * This base class provides assertion utilities for robust validation
 * using the webmozart/assert library.
 *
 * Examples: Email, Money, Address, PhoneNumber, etc.
 */
abstract class ValueObject
{
    /**
     * String representation of the value object.
     */
    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * Compare two value objects for equality.
     */
    public function equals(ValueObject $other): bool
    {
        return $this::class === $other::class && $this->getValue() === $other->getValue();
    }

    /**
     * Get the value of this value object.
     */
    abstract public function getValue(): mixed;

    /**
     * JSON representation of the value object.
     */
    public function jsonSerialize(): mixed
    {
        return $this->getValue();
    }

    /**
     * Assert helper for validation in value object constructors.
     */
    protected static function assert(): Assert
    {
        return new Assert;
    }
}
