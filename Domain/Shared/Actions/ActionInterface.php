<?php

declare(strict_types=1);

namespace Domain\Shared\Actions;

/**
 * Action Interface
 *
 * All actions should implement this interface to ensure consistency
 * across the domain layer while allowing flexible method signatures.
 *
 * Actions encapsulate business logic and use cases, providing a single
 * entry point for complex operations that might involve multiple models,
 * external services, or domain logic.
 *
 * Each action should have a single responsibility and be named with
 * a verb that describes what it does (e.g., CreateUser, SendNotification).
 */
interface ActionInterface
{
    /**
     * Execute the action.
     *
     * Concrete implementations can define specific parameter and return types.
     *
     * @return mixed The result of the action
     */
    public function execute(): mixed;

    /**
     * Static factory method for fluent execution.
     *
     * Allows actions to be called like: CreateUser::make()->execute($data)
     */
    public static function make(): static;
}
