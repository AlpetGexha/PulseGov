<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class JobProgressController extends Controller
{
    public function getProgress(string $key): JsonResponse
    {
        $progress = Cache::get($key, [
            'progress' => 0,
            'message' => 'Job not started',
            'status' => 'pending',
        ]);

        return response()->json($progress);
    }
}
