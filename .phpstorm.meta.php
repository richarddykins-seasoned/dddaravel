<?php

declare(strict_types=1);

namespace PHPSTORM_META {

    // Enhanced config() helper with type hints
    override(\config(0), map([
        'app.name' => '',
        'app.env' => '',
        'app.debug' => false,
        'app.url' => '',
        'app.version' => '',
        'database.default' => '',
        'cache.default' => '',
        'queue.default' => '',
        'mail.default' => '',
        'session.driver' => '',
        'health.notifications.enabled' => false,
        'activitylog.database_connection' => '',
        'activitylog.table_name' => '',
    ]));

    // Enhanced auth() helper with type hints
    override(\auth()->user(), type(0));

    // Enhanced request() helper with type hints  
    override(\request()->input(0), map([
        '' => mixed,
    ]));

    // Model factory type hints
    override(\factory(0), type(0));

    // Collection method type hints
    override(\collect(0)->first(), elementType(0));
    override(\collect(0)->last(), elementType(0));
    override(\collect(0)->get(1), elementType(0));

    // Enhanced Eloquent query builder
    override(\Domain\User\Models\User::query()->first(), \Domain\User\Models\User::class | null);
    override(\Domain\User\Models\User::query()->find(0), \Domain\User\Models\User::class | null);
    override(\Domain\User\Models\User::query()->findOrFail(0), \Domain\User\Models\User::class);

    // Response helpers
    override(\response()->json(0), \Illuminate\Http\JsonResponse::class);
    override(\redirect(0), \Illuminate\Http\RedirectResponse::class);

    // Container bindings
    override(\app(0), map([
        'cache' => \Illuminate\Cache\CacheManager::class,
        'db' => \Illuminate\Database\DatabaseManager::class,
        'log' => \Illuminate\Log\LogManager::class,
        'mail' => \Illuminate\Mail\MailManager::class,
        'queue' => \Illuminate\Queue\QueueManager::class,
        'events' => \Illuminate\Events\Dispatcher::class,
    ]));

    // Validation rule helpers
    expectedArguments(\validator()->make(), 1, [
        'required',
        'string',
        'integer', 
        'numeric',
        'email',
        'min:1',
        'max:255',
        'unique:users',
        'exists:users,id',
        'confirmed',
        'nullable',
        'array',
        'boolean',
        'date',
        'json',
        'url',
        'uuid',
    ]);

    // Expected arguments for common Laravel methods
    expectedArguments(\Route::middleware(), 0, [
        'auth',
        'guest', 
        'throttle',
        'signed',
        'verified',
    ]);

    expectedArguments(\Gate::allows(), 0, [
        'view',
        'create',
        'update', 
        'delete',
        'restore',
        'forceDelete',
    ]);

    // Pest testing helpers
    expectedArguments(\it(), 0, '');
    expectedArguments(\test(), 0, '');
    expectedArguments(\describe(), 0, '');

    // Expected return types for test methods
    override(\expect(0), \Pest\Expectation::class);

    // Activity log helpers
    override(\activity()->log(0), \Spatie\Activitylog\Models\Activity::class);
    override(\activity()->withProperties(0), \Spatie\Activitylog\ActivityLogger::class);
}