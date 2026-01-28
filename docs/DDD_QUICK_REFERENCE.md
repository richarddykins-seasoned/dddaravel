# DDD Quick Reference Guide

## File Structure Template

When adding new functionality, follow this structure:

```
# Domain Layer
Domain/{DomainName}/
├── Actions/              # Business use cases
├── DataTransferObjects/  # Data structures
├── Events/              # Domain events
├── Exceptions/          # Domain-specific exceptions
├── Listeners/           # Domain event listeners
├── Models/              # Rich domain models
└── ValueObjects/        # Domain value objects

# HTTP Layer
app/Http/Controllers/{DomainName}/
└── {Feature}Controller.php

# Test Layer
tests/Feature/{DomainName}/
├── {Feature}IntegrationTest.php
└── {Feature}Test.php
tests/Unit/{DomainName}/
├── {Feature}Test.php
└── {Model}Test.php
```

## Code Templates

### Action Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\Actions;

use Domain\Shared\Actions\Action;
use Domain\{Domain}\DataTransferObjects\{Input}Data;
use Domain\{Domain}\DataTransferObjects\{Output}Result;
use Domain\{Domain}\Events\{SomethingHappened};

final class {ActionName} extends Action
{
    public function execute({Input}Data $data): {Output}Result
    {
        // 1. Validate business rules
        // 2. Perform business logic
        // 3. Dispatch domain events
        // 4. Return structured result
        
        event(new {SomethingHappened}($result));
        
        return {Output}Result::success($result);
    }
}
```

### DTO Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\DataTransferObjects;

use Domain\Shared\DataTransferObjects\DataTransferObject;
use Spatie\LaravelData\Attributes\Validation\Required;

final class {Name}Data extends DataTransferObject
{
    public function __construct(
        #[Required]
        public readonly string $property,
    ) {}
}
```

### Result DTO Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\DataTransferObjects;

use Domain\Shared\DataTransferObjects\DataTransferObject;

final class {Name}Result extends DataTransferObject
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly mixed $data = null,
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(success: true, message: $message, data: $data);
    }

    public static function failed(string $message): self
    {
        return new self(success: false, message: $message);
    }
}
```

### Domain Event Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\Events;

use Domain\Shared\DataTransferObjects\DataTransferObject;
use Domain\Shared\Events\DomainEvent;
use Domain\{Domain}\DataTransferObjects\{Something}HappenedEventData;

final class {Something}Happened extends DomainEvent
{
    public function __construct(
        public readonly mixed $payload
    ) {
        parent::__construct();
    }

    public function getEventData(): DataTransferObject
    {
        return {Something}HappenedEventData::fromPayload(
            $this->payload,
            $this->getOccurredAt()
        );
    }
}
```

### Event Data DTO Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\DataTransferObjects;

use Domain\Shared\DataTransferObjects\DataTransferObject;

final class {Something}HappenedEventData extends DataTransferObject
{
    public function __construct(
        public readonly int $entity_id,
        public readonly string $occurred_at,
        // Add other relevant fields
    ) {}

    public static function fromPayload(
        mixed $payload,
        \DateTimeInterface $occurredAt
    ): self {
        return new self(
            entity_id: $payload->id,
            occurred_at: $occurredAt->format('c')
        );
    }
}
```

### Domain Exception Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\Exceptions;

use Domain\Shared\Exceptions\DomainException;

final class {Something}Exception extends DomainException
{
    public static function because(string $reason): self
    {
        return new self("Operation failed because: {$reason}");
    }
}
```

### Value Object Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\ValueObjects;

use Domain\Shared\ValueObjects\ValueObject;
use Webmozart\Assert\Assert;

final class {Name} extends ValueObject
{
    protected function validate(mixed $value): void
    {
        Assert::string($value, '{Name} must be a string');
        // Add specific validation rules
    }
}
```

### Controller Template
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\{Domain};

use App\Http\Controllers\Controller;
use Domain\{Domain}\Actions\{ActionName};
use Domain\{Domain}\DataTransferObjects\{Input}Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {Name}Controller extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = {Input}Data::from($request->validated());
        $result = {ActionName}::make()->execute($data);
        
        if (!$result->success) {
            return response()->json([
                'error' => $result->message
            ], 400);
        }
        
        return response()->json($result->data, 200);
    }
}
```

## Usage Patterns

### Controller Integration (CQRS Style)
```php
public function store(Request $request): void
{
    CreateUserAction::execute(
        UserData::from($request)
    );
}

public function show(int $id): UserData
{
    $user = User::query()->findByIdOrFail($id);
    return UserData::fromModel($user);
}
```

### Testing Actions (Static Style)
```php
<?php

namespace Tests\Unit\Domain\Users\Actions;

use Domain\Users\Actions\CreateUserAction;
use Domain\Users\DataTransferObjects\UserData;
use Domain\Users\Events\UserCreated;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
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

    public function test_cannot_create_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        
        $data = UserData::from([
            'id' => 1,
            'name' => 'Jane Doe',
            'email' => 'existing@example.com',
            'emailVerified' => false,
            'password' => 'password123'
        ]);
        
        $this->expectException(UserAlreadyExistsException::class);
        
        CreateUserAction::execute($data);
    }
}
```

### Testing DTOs (Class-Based)
```php
<?php

namespace Tests\Unit\Domain\Users\DataTransferObjects;

use Domain\Users\DataTransferObjects\UserData;
use Domain\Users\Models\User;
use Spatie\LaravelData\Exceptions\ValidationException;
use Tests\TestCase;

class UserDataTest extends TestCase
{
    public function test_creates_valid_dto(): void
    {
        $data = UserData::from([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'emailVerified' => false
        ]);

        $this->assertEquals(1, $data->id);
        $this->assertEquals('John Doe', $data->name);
        $this->assertEquals('john@example.com', $data->email);
        $this->assertFalse($data->emailVerified);
    }

    public function test_creates_from_model(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);
        
        $data = UserData::fromModel($user);
        
        $this->assertEquals($user->id, $data->id);
        $this->assertEquals('Jane Doe', $data->name);
        $this->assertEquals('jane@example.com', $data->email);
    }

    public function test_validates_email_format(): void
    {
        $this->expectException(ValidationException::class);

        UserData::from([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'emailVerified' => false
        ]);
    }

    public function test_password_is_hidden_in_response(): void
    {
        $data = UserData::from([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'emailVerified' => false,
            'password' => 'secret123'
        ]);
        
        $array = $data->toArray();
        
        $this->assertArrayNotHasKey('password', $array);
    }
}
```

### Testing Value Objects (Class-Based)
```php
<?php

namespace Tests\Unit\Domain\Shared\ValueObjects;

use Domain\Shared\ValueObjects\Email;
use InvalidArgumentException;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function test_creates_valid_email(): void
    {
        $email = Email::from('test@example.com');
        
        $this->assertEquals('test@example.com', $email->value);
    }

    public function test_rejects_invalid_email_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        Email::from('invalid-email');
    }

    public function test_emails_are_equal_when_values_match(): void
    {
        $email1 = Email::from('test@example.com');
        $email2 = Email::from('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }
}
```

### Listener Template
```php
<?php

declare(strict_types=1);

namespace Domain\{Domain}\Listeners;

use Domain\{Domain}\Events\{Something}Happened;

final class {ListenerName}
{
    public function handle({Something}Happened $event): void
    {
        // Access structured event data via DTO
        $eventData = $event->getEventData();
        
        // Handle domain-specific side effects
        // e.g., send notifications, update related entities, etc.
    }
}
```

**Note:** Domain-specific listeners should be in `Domain/{DomainName}/Listeners/`. Only use `app/Listeners` for cross-cutting infrastructure concerns (external API integrations, system-wide logging, etc.).

## Common Validation Attributes

```php
use Spatie\LaravelData\Attributes\Validation\*;

#[Required]              // Field is required
#[Email]                 // Valid email format
#[Min(8)]                // Minimum length/value
#[Max(255)]              // Maximum length/value
#[Numeric]               // Must be numeric
#[StringType]            // Must be string
#[IntegerType]           // Must be integer
#[BooleanType]           // Must be boolean
#[Uuid]                  // Valid UUID format
#[In(['active', 'inactive'])]  // Must be one of values
#[Regex('/^[a-zA-Z]+$/')]      // Must match regex
```

## Command Cheat Sheet

```bash
# Run all tests
vendor/bin/pest

# Run architectural tests only
vendor/bin/pest tests/Feature/DomainArchitectureTest.php

# Run specific test class
vendor/bin/pest tests/Unit/Domain/Users/Actions/CreateUserActionTest.php

# Run quality checks
composer quality

# Fix code style
vendor/bin/pint

# Apply Rector suggestions
vendor/bin/rector --dry-run
vendor/bin/rector

# Check spelling
vendor/bin/peck
```

## Test Organization

```
tests/
├── Feature/                    # End-to-end tests
│   ├── DomainArchitectureTest.php
│   └── Api/
├── Unit/                       # Isolated unit tests
│   └── Domain/
│       ├── Health/
│       │   └── Actions/
│       ├── Shared/
│       │   ├── ValueObjects/
│       │   └── DataTransferObjects/
│       └── Users/
│           ├── Actions/
│           ├── DataTransferObjects/
│           ├── Events/
│           ├── Exceptions/
│           ├── Listeners/
│           └── ValueObjects/
└── TestCase.php               # Base test case
```

## Best Practices Checklist

### Actions
- [ ] Verb-based naming with "Action" suffix
- [ ] Single responsibility
- [ ] Commands are void (CQRS)
- [ ] Dispatches domain events
- [ ] Static methods for stateless operations
- [ ] Proper type hints
- [ ] Unit tests with class-based structure

### DTOs
- [ ] Extends base `DataTransferObject`
- [ ] Uses validation attributes
- [ ] Readonly properties
- [ ] Immutable
- [ ] Validation tests for edge cases

### Events
- [ ] Past tense naming
- [ ] Extends base `DomainEvent`
- [ ] Contains minimal necessary data
- [ ] Immutable
- [ ] Has corresponding event data DTO
- [ ] Event handler tests

### Listeners
- [ ] Located in domain's `Listeners/` directory for domain logic
- [ ] Uses `app/Listeners` only for infrastructure concerns
- [ ] Accesses event data via DTO
- [ ] Handles one specific side effect
- [ ] Listener tests with event dispatch

### Value Objects
- [ ] Self-validating
- [ ] Immutable
- [ ] Meaningful domain concept
- [ ] Extends base `ValueObject`
- [ ] Equality and validation tests

### Exceptions
- [ ] Domain-specific
- [ ] Meaningful error messages
- [ ] Static factory methods
- [ ] Extends base `DomainException`
- [ ] Tests for exception scenarios

### Models
- [ ] Rich with business methods
- [ ] Domain logic not just data
- [ ] Proper relationships
- [ ] Business rule enforcement
- [ ] Tests for business methods

## Testing Anti-Patterns to Avoid

❌ **Don't test framework code**
```php
// Bad - testing Laravel's validation
public function test_laravel_validates_email(): void
{
    $this->expectException(ValidationException::class);
    UserData::from(['id' => 1, 'name' => 'Test', 'email' => 'invalid', 'emailVerified' => false]);
}
```

❌ **Don't test implementation details**
```php
// Bad - testing how something is done
public function test_uses_bcrypt_for_password(): void
{
    $this->assertTrue(Hash::check('password', $hashedPassword));
}
```

✅ **Do test business behavior**
```php
// Good - testing what the system does
public function test_creating_user_sends_welcome_email(): void
{
    Mail::fake();
    Event::fake();
    
    CreateUserAction::execute($userData);
    
    Event::assertDispatched(UserCreated::class);
    Mail::assertSent(WelcomeEmail::class);
}
```
