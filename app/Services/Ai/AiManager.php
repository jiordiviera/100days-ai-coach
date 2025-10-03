<?php

namespace App\Services\Ai;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class AiManager
{
    public function __construct(
        protected Container $container,
    ) {
    }

    public function generateInsights(DailyLog $log, bool $force = false): DailyLogAiResult
    {
        $drivers = $this->providers();

        $errors = [];
        foreach ($drivers as $driverName) {
            try {
                $driver = $this->resolveDriver($driverName);

                return $driver->generateDailyLogInsights($log);
            } catch (Throwable $e) {
                $errors[$driverName] = $e;
                report($e);
                continue;
            }
        }

        throw new RuntimeException('Unable to generate AI insights: '.implode(', ', array_keys($errors)));
    }

    protected function providers(): array
    {
        $default = config('ai.default', 'fake');
        $fallbacks = config('ai.fallback', []);

        if (is_string($fallbacks)) {
            $fallbacks = array_map('trim', explode(',', $fallbacks));
        }

        $fallbacks = array_filter(Arr::wrap($fallbacks), static fn ($driver) => filled($driver));

        return array_values(array_unique(array_merge([$default], $fallbacks)));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function resolveDriver(string $driverName): AiDriver
    {
        $drivers = config('ai.drivers', []);
        $class = $drivers[$driverName] ?? null;

        if (! $class) {
            throw new RuntimeException(sprintf('AI driver [%s] is not defined.', $driverName));
        }

        $driver = $this->container->make($class);

        if (! $driver instanceof AiDriver) {
            throw new RuntimeException(sprintf('AI driver [%s] must implement %s.', $driverName, AiDriver::class));
        }

        return $driver;
    }
}
