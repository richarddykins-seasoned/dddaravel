<?php

declare(strict_types=1);

namespace Domain\Shared\DataTransferObjects;

use Spatie\LaravelData\Data;

/**
 * Base Data Transfer Object class extending Spatie Laravel Data.
 *
 * Data Transfer Objects are used to transfer data between different layers of the application
 * (controllers, actions, services) while providing type safety, validation,
 * and automatic serialization/deserialization.
 *
 * Following Martin Joo's approach, Data Transfer Objects help maintain clean boundaries
 * between layers and provide a contract for data structure.
 */
abstract class DataTransferObject extends Data {}
