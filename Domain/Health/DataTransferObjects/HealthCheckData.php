<?php

declare(strict_types=1);

namespace Domain\Health\DataTransferObjects;

use Domain\Shared\DataTransferObjects\DataTransferObject;

/**
 * Health Check Data DTO
 *
 * Represents the data of a health check operation.
 * Contains status, timestamp, version info, warnings, and individual check results.
 */
class HealthCheckData extends DataTransferObject
{
    public function __construct(
        public readonly string $status,
        public readonly string $timestamp,
        public readonly ?string $version,
        /** @var array<string> */
        public readonly array $warnings,
        /** @var array<string, bool> */
        public readonly array $checks,
    ) {}

    /**
     * Get the HTTP status code based on the health status.
     */
    public function getHttpStatusCode(): int
    {
        return match ($this->status) {
            'UP' => 200,
            'DEGRADED' => 503, // Should not receive traffic
            'DOWN' => 503,
            default => 503,
        };
    }

    /**
     * Get the response array for the API.
     *
     * @return array<string, mixed>
     */
    public function toApiResponse(): array
    {
        $response = [
            'status' => $this->status,
            'timestamp' => $this->timestamp,
        ];

        // Only add version info if available and not sensitive
        if ($this->version !== null) {
            $response['version'] = $this->version;
        }

        // Add warnings if any exist (production only, non-failing)
        if ($this->warnings !== []) {
            $response['warnings'] = $this->warnings;
        }

        return $response;
    }
}
