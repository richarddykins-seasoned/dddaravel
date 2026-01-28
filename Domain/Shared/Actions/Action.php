<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

/**
 * Base Action class following Martin Joo's Domain Driven Laravel approach.
 *
 * Actions encapsulate business logic and use cases, providing a single
 * entry point for complex operations that might involve multiple models,
 * external services, or domain logic.
 *
 * Each action should have a single responsibility and be named with
 * a verb that describes what it does (e.g., CreateUser, SendNotification).
 */
abstract class Action
{
    /**
     * Execute the action.
     *
     * This method should be implemented by concrete actions to define
     * their specific business logic. Concrete implementations can define
     * their own method signatures.
     */

    /**
     * Static factory method for fluent execution.
     *
     * Allows actions to be called like: CreateUser::make()->execute($data)
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Helper method to resolve dependencies from the container.
     *
     * @template T
     *
     * @param  class-string<T>  $abstract
     * @return T
     */
    protected function resolve(string $abstract): mixed
    {
        return app($abstract);
    }
}
