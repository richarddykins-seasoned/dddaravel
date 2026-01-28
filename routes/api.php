<?php

declare(strict_types=1);

use App\Http\Controllers\Health\HealthController;
use Illuminate\Support\Facades\Route;

// Public API routes
// Standardized REST health check endpoint
Route::get('/health', HealthController::class)->name('health-check');
