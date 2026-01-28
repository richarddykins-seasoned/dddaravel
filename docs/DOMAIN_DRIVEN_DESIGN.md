# Domain Driven Design Architecture

This project has been refactored to follow the Domain Driven Design (DDD) patterns as described in Martin Joo's "Domain Driven Laravel" book. This approach emphasizes organizing code around business domains rather than technical layers.

## Project Structure

```
dddaravel/
├── app/                          # Laravel framework layer
│   ├── Http/                     # HTTP layer (organized by domain)
│   │   └── Controllers/          # Domain-based controllers
│   │       ├── Health/           # Health domain controllers
│   │       └── Users/            # Users domain controllers
│   └── Providers/               # Laravel service providers
├── Domain/                       # Domain layer (business logic)
│   ├── Health/                   # Health domain
│   │   ├── Actions/              # Business use cases
│   │   └── DataTransferObjects/  # Data structures
│   ├── Shared/                   # Cross-domain shared code
│   │   ├── Actions/              # Base action class
│   │   ├── DataTransferObjects/  # Base DTO class
│   │   ├── Events/               # Base domain event class
│   │   ├── Exceptions/           # Base domain exception class
│   │   └── ValueObjects/         # Base value object class
│   └── Users/                    # Users domain
│       ├── Actions/              # User business use cases
│       ├── DataTransferObjects/  # User-specific DTOs
│       ├── Events/               # User domain events
│       ├── Exceptions/           # User-specific exceptions
│       ├── Models/               # Enhanced User model
│       └── ValueObjects/         # User-specific value objects
└── tests/                        # Domain-organized tests
    ├── Feature/                  # Feature tests by domain
    │   ├── Health/               # Health domain feature tests
    │   ├── Users/                # Users domain feature tests
    │   ├── DomainArchitectureTest.php  # Cross-domain architecture tests
    │   └── ...                   # Other shared tests
    └── Unit/                     # Unit tests by domain
        ├── Health/               # Health domain unit tests
        ├── Users/                # Users domain unit tests
        └── ...                   # Other shared tests
```

## Core Concepts

### 1. Actions

Actions encapsulate business use cases and operations. They represent what the system *does* rather than what it *is*.

**Characteristics:**
- Named with verbs + "Action" suffix (e.g., `CreateUserAction`, `UpdateUserAction`, `AuthenticateUserAction`)
- Single responsibility principle
- Return structured data (DTOs) rather than primitive types
- Handle business logic validation and orchestration
- Dispatch domain events for side effects

**Example (CQRS Style):**
```php
<?php

declare(strict_types=1);

namespace Domain\Users\Actions;

use Domain\Shared\Actions\Action;
use Domain\Users\DataTransferObjects\UserData;
use Domain\Users\Events\UserCreated;

final class CreateUserAction extends Action
{
    public static function execute(UserData $data): void
    {
        // Business logic implementation
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);
        
        // Trigger domain events
        event(new UserCreated($user));
    }
}
```

**Usage:**
```php
// Commands are void
CreateUserAction::execute($userData);

// Queries return data
$user = User::query()->findByIdOrFail($id);
return UserData::fromModel($user);
```

### 2. Data Transfer Objects (DTOs)

DTOs are immutable data structures that carry data between layers and processes. Built using Spatie Laravel Data.

**Characteristics:**
- Immutable data containers
- Built-in validation using Laravel validation rules
- Type-safe data transformation
- Support for nested data structures

**Example:**
```php
<?php

declare(strict_types=1);

namespace Domain\Users\DataTransferObjects;

use Domain\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;

final class UserData extends DataTransferObject
{
    public function __construct(
        #[Required]
        public readonly int $id,
        
        #[Required]
        public readonly string $name,
        
        #[Required, Email]
        public readonly string $email,
        
        public readonly bool $emailVerified,
        
        #[Hidden, Min(8)]  // Hidden from responses but accepts input
        public readonly ?string $password = null,
        
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
    
    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            emailVerified: $user->email_verified_at !== null,
            createdAt: $user->created_at?->toISOString(),
            updatedAt: $user->updated_at?->toISOString(),
        );
    }
}
```

### 3. Value Objects

Value objects represent domain concepts that are defined by their value rather than identity.

**Characteristics:**
- Immutable
- Self-validating
- Equality based on value, not identity
- Encapsulate domain rules and constraints

**Example:**
```php
<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObjects;

final class Email extends ValueObject
{
    protected function validate(mixed $value): void
    {
        Assert::string($value, 'Email must be a string');
        Assert::email($value, 'Invalid email format: %s');
    }
}
```

### 4. Domain Events

Events represent something significant that happened in the domain.

**Characteristics:**
- Immutable
- Past tense naming (e.g., `UserCreated`, `UserAuthenticated`)
- Return structured DTOs via `getEventData()` method
- Contain minimal data needed by event handlers
- Enable loose coupling between domain operations
- Type-safe event data through DTOs

**Example:**
```php
<?php

declare(strict_types=1);

namespace Domain\Users\Events;

use Domain\Shared\DataTransferObjects\DataTransferObject;
use Domain\Shared\Events\DomainEvent;
use Domain\Users\DataTransferObjects\UserCreatedEventData;
use Domain\Users\Models\User;

final class UserCreated extends DomainEvent
{
    public function __construct(
        public readonly User $user
    ) {
        parent::__construct();
    }

    public function getEventData(): DataTransferObject
    {
        return UserCreatedEventData::fromUser(
            $this->user,
            $this->getOccurredAt()
        );
    }
}
```

### 5. Domain Models

Rich domain models that contain business logic, not just data.

**Characteristics:**
- Business methods and behavior
- Domain-specific functionality
- Validation and business rules
- Relationships and interactions

**Example:**
```php
<?php

declare(strict_types=1);

namespace Domain\Users\Models;

use Domain\Shared\ValueObjects\Email;
use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable
{
    public function getEmailValueObject(): Email
    {
        return Email::from($this->email);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    // ... other business methods
}
```

### 6. Controllers (Domain-Organized)

Controllers are organized by domain rather than by functionality, following Martin Joo's approach.

**Characteristics:**
- Located in `app/Http/Controllers/{Domain}`
- Thin controllers that delegate to Actions
- Domain-specific namespacing
- Clear separation of concerns

**Example:**
```php
<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use Domain\Health\Actions\HealthCheckAction;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $result = HealthCheckAction::make()->execute();
        
        return response()->json(
            $result->toApiResponse(),
            $result->getHttpStatusCode()
        );
    }
}
```

### 7. Domain Exceptions

Specific exceptions for domain operations that provide meaningful error context.

**Example:**
```php
<?php

declare(strict_types=1);

namespace Domain\Users\Exceptions;

use Domain\Shared\Exceptions\DomainException;
use Domain\Shared\ValueObjects\Email;

final class UserAlreadyExistsException extends DomainException
{
    public static function forEmail(Email $email): self
    {
        return new self("User with email '{$email->value}' already exists");
    }
}
```

## Guidelines and Best Practices

### Naming Conventions

- **Actions**: Verb-based names with "Action" suffix (`CreateUserAction`, `SendNotificationAction`)
- **DTOs**: Descriptive data names (`UserData`, `UserProfileData`)
- **Events**: Past tense (`UserCreated`, `OrderPlaced`)
- **Exceptions**: Descriptive with Exception suffix (`UserNotFoundException`)
- **Value Objects**: Domain concept names (`Email`, `PhoneNumber`)

### Action Guidelines

1. **Single Responsibility**: Each action should do one thing well
2. **Return DTOs**: Always return structured data, never primitives
3. **Handle Events**: Dispatch domain events for side effects
4. **Validate Input**: Use DTOs with validation for input data
5. **Use Factory Method**: Provide `make()` static method for fluent usage

### Data Flow

```
Controller -> Action -> Domain Logic -> Repository/External Services
    ↓            ↓           ↓
   DTO     ->  Result   ->  Events
```

### Event-Driven Architecture

Actions dispatch events for:
- Audit logging
- Notifications
- Cache invalidation
- Integration with external systems
- Triggering other business processes

### Testing Strategy

Tests are organized by domain to maintain clear boundaries and improve navigation.

**Organization:**
- **Feature Tests**: `tests/Feature/{Domain}/` - End-to-end domain workflows
- **Unit Tests**: `tests/Unit/{Domain}/` - Isolated domain class testing
- **Shared Tests**: Root level - Cross-domain and architectural tests

**Test Types:**
- **Unit Tests**: Test individual domain classes in isolation
- **Integration Tests**: Test actions with their dependencies
- **Architectural Tests**: Ensure compliance with DDD patterns
- **Feature Tests**: Test complete user workflows

**Domain Test Examples:**
- `tests/Feature/Health/HealthControllerIntegrationTest.php`
- `tests/Unit/Health/HealthControllerTest.php`
- `tests/Feature/Users/ApiAuthenticationTest.php`
- `tests/Unit/Users/UserModelTest.php`

**Example Action Test:**
```php
<?php

namespace Tests\Unit\Domain\Users\Actions;

use Domain\Users\Actions\CreateUserAction;
use Domain\Users\DataTransferObjects\UserData;
use Domain\Users\Events\UserCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_new_user(): void
    {
        Event::fake();
        
        $data = UserData::from([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'emailVerified' => false,
            'password' => 'password123'
        ]);
        
        CreateUserAction::execute($data);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe'
        ]);
        
        Event::assertDispatched(UserCreated::class);
    }
}
```

## Domain Boundaries

Current domains in the system:

### Health Domain
- **Purpose**: Application health and monitoring
- **Actions**: `HealthCheckAction`
- **DTOs**: `HealthCheckData`

### Users Domain
- **Purpose**: User management and authentication
- **Actions**: `CreateUserAction`, `UpdateUserAction`, `DeleteUserAction`
- **Builders**: `UserBuilder` (custom query builder)
- **Models**: `User` (rich domain model)
- **Events**: `UserCreated`
- **Value Objects**: `Email`

## Development Workflow

### Adding a New Action

1. Create the action class extending `Domain\Shared\Actions\Action`
2. Define input/output DTOs
3. Implement business logic in `execute()` method
4. Dispatch relevant domain events
5. Create tests
6. Update architectural tests if needed

### Adding a New Domain

1. Create domain folder structure
2. Add domain-specific actions, DTOs, events, exceptions
3. Update `DomainServiceProvider` if needed
4. Create architectural tests
5. Document the new domain

## Architectural Tests

The project includes architectural tests that ensure:
- Actions extend the base Action class
- DTOs extend the base DataTransferObject class
- Events extend the base DomainEvent class
- Exceptions extend the base DomainException class
- All classes follow proper inheritance patterns

Run architectural tests:
```bash
vendor/bin/pest tests/Feature/DomainArchitectureTest.php
```

## Benefits of This Architecture

1. **Clear Separation of Concerns**: Business logic separated from framework code
2. **Testability**: Easy to unit test business logic in isolation
3. **Maintainability**: Code organized by business domains
4. **Flexibility**: Easy to change implementation without affecting business rules
5. **Event-Driven**: Loose coupling through domain events
6. **Type Safety**: Strong typing throughout the domain layer
7. **Self-Documenting**: Code structure reflects business requirements

## Resources

- [Martin Joo's "Domain Driven Laravel"](https://github.com/domain-driven-laravel)
- [Spatie Laravel Data Documentation](https://spatie.be/docs/laravel-data)
- [Domain-Driven Design Reference](https://www.domainlanguage.com/ddd/reference/)
