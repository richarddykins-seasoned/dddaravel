<?php

declare(strict_types=1);

namespace Domain\Shared\Exceptions;

use Exception;

/**
 * Base Domain Exception
 *
 * All domain-specific exceptions should extend this base class
 * to maintain consistency across the domain layer.
 */
abstract class DomainException extends Exception {}
