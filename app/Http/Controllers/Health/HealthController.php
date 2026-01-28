<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use Domain\Health\Actions\HealthCheckAction;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Return standardized health check response.
     *
     * Follows REST API standards with minimal information disclosure:
     * - Simple status: UP, DOWN, or DEGRADED
     * - ISO 8601 timestamp with timezone
     * - HTTP 200 for UP, 503 for DOWN/DEGRADED (removes from load balancer rotation)
     * - No internal system details exposed
     */
    public function __invoke(): JsonResponse
    {
        $healthResult = HealthCheckAction::make()->execute();

        return response()->json(
            $healthResult->toApiResponse(),
            $healthResult->getHttpStatusCode()
        );
    }
}
