<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $services = [
            'database' => 'ok',
            'redis' => 'ok',
            'storage' => 'ok',
        ];

        $isHealthy = true;

        // Verify Database connection
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $services['database'] = 'failed: ' . $e->getMessage();
            $isHealthy = false;
        }

        // Verify Redis connection
        try {
            Redis::connection()->ping();
        } catch (\Throwable $e) {
            $services['redis'] = 'failed: ' . $e->getMessage();
            $isHealthy = false;
        }

        // Verify Storage disk access
        try {
            Storage::disk(config('filesystems.default'))->exists('.healthcheck');
        } catch (\Throwable $e) {
            $services['storage'] = 'failed: ' . $e->getMessage();
            $isHealthy = false;
        }

        $statusCode = $isHealthy ? 200 : 503;

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'error',
            'timestamp' => now()->toIso8601String(),
            'services' => $services,
        ], $statusCode);
    }
}
