<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\EloquentHealthResultStore;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (new HealthCheckResultHistoryItem)->getConnectionName();
        $historyItem = EloquentHealthResultStore::getHistoryItemInstance();
        assert($historyItem instanceof HealthCheckResultHistoryItem);
        $tableName = $historyItem->getTable();

        Schema::connection($connection)->create($tableName, function (Blueprint $blueprint): void {
            $blueprint->id();

            $blueprint->string('check_name');
            $blueprint->string('check_label');
            $blueprint->string('status');
            $blueprint->text('notification_message')->nullable();
            $blueprint->string('short_summary')->nullable();
            $blueprint->json('meta');
            $blueprint->timestamp('ended_at');
            $blueprint->uuid('batch');

            $blueprint->timestamps();
        });

        Schema::connection($connection)->table($tableName, function (Blueprint $blueprint): void {
            $blueprint->index('created_at');
            $blueprint->index('batch');
        });
    }
};
