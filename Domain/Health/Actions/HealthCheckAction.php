<?php

declare(strict_types=1);

namespace Domain\Health\Actions;

use Carbon\Carbon;
use Domain\Health\DataTransferObjects\HealthCheckData;
use Domain\Shared\Actions\Action;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Health Check Action
 *
 * Encapsulates all health check logic following the Actions pattern.
 * Returns a structured HealthCheckData DTO.
 */
class HealthCheckAction extends Action
{
    public function execute(): HealthCheckData
    {
        $checks = $this->performHealthChecks();
        $warnings = $this->checkProductionWarnings();
        $overallStatus = $this->determineOverallStatus($checks);

        return HealthCheckData::from([
            'status' => $overallStatus,
            'timestamp' => Carbon::now()->toISOString(),
            'version' => $this->getVersion(),
            'warnings' => $warnings,
            'checks' => $checks,
        ]);
    }

    /**
     * Perform essential health checks that affect service availability.
     *
     * @return array<string, bool>
     */
    private function performHealthChecks(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk_space' => $this->checkDiskSpace(),
            'queue' => $this->checkQueue(),
        ];
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Check cache system.
     */
    private function checkCache(): bool
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 1); // 1 second TTL
            $result = Cache::get($key) === 'test';
            Cache::forget($key);

            return $result;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Check disk space availability.
     */
    private function checkDiskSpace(): bool
    {
        try {
            // Check if we can write to storage directory
            $testFile = 'health_check_' . time() . '.tmp';
            Storage::disk('local')->put($testFile, 'test');
            $result = Storage::disk('local')->exists($testFile);
            Storage::disk('local')->delete($testFile);

            // Also check basic disk space (at least 100MB free)
            $freeBytes = disk_free_space(storage_path());
            $hasSpace = $freeBytes > (100 * 1024 * 1024); // 100MB

            return $result && $hasSpace;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Check queue system (if configured).
     */
    private function checkQueue(): bool
    {
        try {
            // Only check if queues are actually configured (not sync driver)
            $queueDriver = config('queue.default');
            if ($queueDriver === 'sync') {
                return true; // Sync driver always "works"
            }

            // For real queue drivers, try to get queue size
            $size = Queue::size();

            return $size >= 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Check for production configuration warnings (non-failing).
     *
     * @return array<string>
     */
    private function checkProductionWarnings(): array
    {
        // Only check warnings in production environment
        if (config('app.env') !== 'production') {
            return [];
        }

        $warnings = [];

        // Debug mode should be disabled in production
        $debug = config('app.debug');
        if ($debug === true) {
            $warnings[] = 'Debug mode is enabled in production';
        }

        // App should be optimized in production
        if (! $this->isAppOptimized()) {
            $warnings[] = 'Application is not optimized for production';
        }

        return $warnings;
    }

    /**
     * Check if the application is optimized.
     */
    private function isAppOptimized(): bool
    {
        // Check if config is cached
        $configCached = file_exists(app()->bootstrapPath('cache/config.php'));

        // Check if routes are cached
        $routesCached = file_exists(app()->bootstrapPath('cache/routes-v7.php'));

        return $configCached && $routesCached;
    }

    /**
     * Get application version.
     */
    private function getVersion(): ?string
    {
        $version = config('app.version');

        return $version !== null && $version !== '' ? $version : null;
    }

    /**
     * Determine overall system status from individual checks.
     *
     * @param  array<string, bool>  $checks
     */
    private function determineOverallStatus(array $checks): string
    {
        $allPassing = collect($checks)->every(fn ($status): bool => $status);
        $anyPassing = collect($checks)->contains(fn ($status): bool => $status);

        return match (true) {
            $allPassing => 'UP',
            $anyPassing => 'DEGRADED', // Some services working
            default => 'DOWN',
        };
    }
}
