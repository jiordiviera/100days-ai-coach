<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiGenerationMetricResource;
use App\Models\AiGenerationMetric;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AiMetricsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $metrics = AiGenerationMetric::query()
            ->orderByDesc('date')
            ->orderBy('model')
            ->limit(90)
            ->get();

        return AiGenerationMetricResource::collection($metrics);
    }
}
