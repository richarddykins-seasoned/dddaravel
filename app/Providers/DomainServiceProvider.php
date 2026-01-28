<?php

declare(strict_types=1);

namespace app\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Domain Service Provider
 *
 * Handles dependency injection and service bindings for the Domain layer.
 * Following Martin Joo's approach, this provider organizes domain-specific
 * bindings and configurations.
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register domain-specific bindings here
        $this->registerActions();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Boot domain-specific services here
        $this->bootEventListeners();
    }

    /**
     * Register domain actions as singletons for better performance.
     */
    private function registerActions(): void
    {
        $this->app->singleton(\Domain\Health\Actions\HealthCheckAction::class);
    }

    /**
     * Register domain services and other dependencies.
     */
    private function registerServices(): void
    {
        // Register any domain services here
        // Example:
        // $this->app->bind(SomeServiceInterface::class, SomeServiceImplementation::class);
    }

    /**
     * Boot event listeners for domain events.
     */
    private function bootEventListeners(): void
    {
        // Register domain event listeners here
        // Example listeners that could be created:

        // $this->app['events']->listen(
        //     \Domain\Users\Events\UserCreated::class,
        //     [SendWelcomeEmailListener::class, 'handle']
        // );
    }
}
