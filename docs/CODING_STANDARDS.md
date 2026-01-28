# 🎯 PHPStan-First Coding Standards

This document outlines coding standards for maintaining 100% PHPStan compliance in the dddaravel API project.

## 📋 Table of Contents

- [Core Principles](#core-principles)
- [Type Safety Guidelines](#type-safety-guidelines)
- [Common Patterns](#common-patterns)
- [Laravel-Specific Guidelines](#laravel-specific-guidelines)
- [Testing Standards](#testing-standards)
- [Migration Guidelines](#migration-guidelines)

## 🎯 Core Principles

### 1. **Strict Types Always**
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;
```
- **Always** include `declare(strict_types=1);` in new PHP files
- Place after opening PHP tag and before namespace

### 2. **Complete Type Annotations**
```php
/**
 * Process user data and return validation results.
 *
 * @param array<string, mixed> $data Raw input data
 * @return array<string, bool> Validation results by field
 */
public function validateUserData(array $data): array
{
    // Implementation
}
```

### 3. **No Mixed Types in Public APIs**
```php
// ❌ Bad - Mixed return type
public function getConfig(string $key)
{
    return config($key);
}

// ✅ Good - Specific return type with null handling
public function getConfigString(string $key, string $default = ''): string
{
    $value = config($key);
    return is_string($value) ? $value : $default;
}
```

## 🛡️ Type Safety Guidelines

### **Null Safety Patterns**

```php
// ✅ Pattern 1: Early return with exception
public function findUser(int $id): User
{
    $user = User::find($id);
    if ($user === null) {
        throw new UserNotFoundException("User not found: {$id}");
    }
    return $user; // PHPStan knows $user is User here
}

// ✅ Pattern 2: Null coalescing with proper defaults
public function getUserName(?User $user): string
{
    return $user?->name ?? 'Guest';
}

// ✅ Pattern 3: Explicit null checks in conditions
public function processUser(?User $user): void
{
    if ($user !== null) {
        // PHPStan knows $user is User here
        $this->sendWelcomeEmail($user);
    }
}
```

### **Mixed Type Handling**

```php
// ✅ Type guards for API responses
public function handleApiResponse($response): array
{
    $data = $response->json();
    
    if (!is_array($data)) {
        throw new InvalidResponseException('Expected array response');
    }
    
    return $data; // PHPStan knows this is array
}

// ✅ Safe array access pattern
public function extractUserData(array $input): UserData
{
    if (!isset($input['name']) || !is_string($input['name'])) {
        throw new InvalidArgumentException('Name must be a string');
    }
    
    if (!isset($input['email']) || !is_string($input['email'])) {
        throw new InvalidArgumentException('Email must be a string');
    }
    
    return new UserData(
        name: $input['name'],
        email: $input['email']
    );
}
```

### **Collection Type Annotations**

```php
// ✅ Specific collection types
/**
 * @return Collection<int, User>
 */
public function getActiveUsers(): Collection
{
    return User::where('active', true)->get();
}

// ✅ Array shape documentation
/**
 * @param array{
 *     name: string,
 *     email: string,
 *     age?: int,
 *     preferences: array<string, mixed>
 * } $userData
 */
public function createUser(array $userData): User
{
    // Implementation
}
```

## 🔄 Common Patterns

### **Configuration Access**

```php
// ✅ Helper methods for type-safe config access
private function getRequiredConfigString(string $key): string
{
    $value = config($key);
    if (!is_string($value)) {
        throw new ConfigurationException("Config key '{$key}' must be a string");
    }
    return $value;
}

private function getConfigStringWithDefault(string $key, string $default): string
{
    $value = config($key);
    return is_string($value) ? $value : $default;
}

private function getConfigBool(string $key, bool $default = false): bool
{
    $value = config($key);
    return is_bool($value) ? $value : $default;
}
```

### **Request Validation**

```php
// ✅ Form Request with proper typing
class CreateUserRequest extends FormRequest
{
    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
        ];
    }

    /**
     * Get validated data with proper typing.
     *
     * @return array{
     *     name: string,
     *     email: string,
     *     age?: int
     * }
     */
    public function validated($key = null, $default = null): array
    {
        return parent::validated($key, $default);
    }
}
```

### **Exception Handling**

```php
// ✅ Specific exception handling
public function processPayment(int $userId, int $amount): PaymentResult
{
    try {
        $user = User::findOrFail($userId);
        return $this->paymentService->charge($user, $amount);
    } catch (ModelNotFoundException $e) {
        throw new UserNotFoundException("User {$userId} not found", 0, $e);
    } catch (PaymentException $e) {
        throw new PaymentProcessingException(
            "Failed to process payment for user {$userId}",
            $e->getCode(),
            $e
        );
    }
}
```

## 🏗️ Laravel-Specific Guidelines

### **Controller Methods**

```php
class UserController extends Controller
{
    /**
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'age' => $validated['age'] ?? null,
        ]);

        return response()->json($user, 201);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }
}
```

### **Model Relationships**

```php
class User extends Model
{
    /**
     * @return HasMany<Post>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return BelongsTo<Profile, User>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
```

### **Service Classes**

```php
class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService,
    ) {}

    /**
     * @param array{
     *     name: string,
     *     email: string,
     *     age?: int
     * } $userData
     */
    public function createUser(array $userData): User
    {
        $user = $this->userRepository->create($userData);
        
        $this->emailService->sendWelcomeEmail($user);
        
        return $user;
    }

    /**
     * @return Collection<int, User>
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActive();
    }
}
```

## 🧪 Testing Standards

### **Pest Test Structure**

```php
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a user with valid data', function (): void {
    $userData = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'age' => fake()->numberBetween(18, 80),
    ];

    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'age',
            'created_at',
            'updated_at',
        ]);

    $user = User::first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe($userData['name']);
    expect($user->email)->toBe($userData['email']);
    expect($user->age)->toBe($userData['age']);
});
```

### **Test Assertions with Type Safety**

```php
// ✅ Proper null checking in tests
it('handles missing user gracefully', function (): void {
    $response = $this->getJson('/api/users/999');
    
    $response->assertStatus(404);
    
    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toHaveKey('message');
});

// ✅ Collection testing
it('returns paginated users', function (): void {
    User::factory()->count(15)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200);
    
    $data = $response->json();
    expect($data)->toBeArray();
    expect($data)->toHaveKeys(['data', 'links', 'meta']);
    expect($data['data'])->toHaveCount(10); // Default pagination
});
```

## 📊 Migration Guidelines

### **Migration Structure**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('age')->nullable();
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

## 🚨 Common Pitfalls to Avoid

### **❌ Don't Do This**

```php
// ❌ Missing return type
public function getUser($id) {
    return User::find($id);
}

// ❌ Using mixed without type guards
public function processData($data) {
    return $data['key']; // Could throw if $data is not array
}

// ❌ Ignoring null possibilities
public function getName(): string {
    return auth()->user()->name; // Could be null
}

// ❌ Generic array types
public function getUsers(): array {
    return User::all()->toArray();
}
```

### **✅ Do This Instead**

```php
// ✅ Specific return types
public function getUser(int $id): ?User {
    return User::find($id);
}

// ✅ Type guards for mixed data
public function processData(mixed $data): string {
    if (!is_array($data) || !isset($data['key']) || !is_string($data['key'])) {
        throw new InvalidArgumentException('Invalid data structure');
    }
    return $data['key'];
}

// ✅ Null-safe operations
public function getName(): string {
    $user = auth()->user();
    return $user?->name ?? 'Guest';
}

// ✅ Specific collection types
/**
 * @return array<int, array<string, mixed>>
 */
public function getUsersArray(): array {
    return User::all()->toArray();
}
```

## 🔧 Tools Integration

- **PHPStan**: Level max analysis
- **Rector**: Automatic code modernization  
- **Laravel Pint**: Code formatting
- **Pest**: Testing framework
- **Peck**: Spell checking

## 📚 Additional Resources

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Laravel Types for PHPStan](https://github.com/larastan/larastan)
- [Pest Testing Framework](https://pestphp.com/)
- [PHP Type System Best Practices](https://php.watch/articles/php-type-system)

---

*This document should be updated as new patterns emerge and best practices evolve.*
