<?php

declare(strict_types=1);

namespace Domain\Shared\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base Domain Event
 *
 * All domain events should extend this base class to ensure
 * consistency and proper integration with Laravel event system.
 *
 * Domain events represent something significant that happened
 * in the domain and help decouple business logic.
 */
abstract class DomainEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The time when the event occurred.
     */
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable;
    }

    /**
     * Get the event name for logging/debugging purposes.
     */
    public function getEventName(): string
    {
        return static::class;
    }

    /**
     * Get the time when the event occurred.
     */
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Get structured event data as a DTO for event listeners.
     *
     * This method should return a DTO containing all relevant data
     * that event listeners might need, ensuring type safety and
     * consistency across the domain.
     */
    abstract public function getEventData(): \Domain\Shared\DataTransferObjects\DataTransferObject;
}
