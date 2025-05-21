<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class GenerateFilamentResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:filament-resource {--model= : Generate resource for specific model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Filament Resource classes for models in the app/Models directory';

    /**
     * Models to skip during generation.
     *
     * @var array
     */
    protected $skipModels = [
        'XPLog', // Skip XPLog as it requires Source model
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelPath = app_path('Models');
        $files = File::allFiles($modelPath);
        $specificModel = $this->option('model');

        foreach ($files as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Skip if specific model is requested and this isn't it
            if ($specificModel && $className !== $specificModel) {
                continue;
            }

            // Skip models in the skip list
            if (in_array($className, $this->skipModels)) {
                $this->line("Skipping {$className} (in skip list)");

                continue;
            }

            // Skip abstract classes and interfaces
            try {
                $reflection = new ReflectionClass("App\\Models\\{$className}");
                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    $this->line("Skipping abstract/interface: {$className}");

                    continue;
                }

                // Check if model exists and is instantiable
                if (! $reflection->isInstantiable()) {
                    $this->line("Skipping non-instantiable class: {$className}");

                    continue;
                }

                // Check for required related models
                $missingModels = $this->checkRequiredModels($reflection);
                if (! empty($missingModels)) {
                    $this->error("✗ Cannot generate resource for {$className}. Missing required models:");
                    foreach ($missingModels as $missingModel) {
                        $this->line("  - {$missingModel}");
                    }
                    $this->line('Please create these models first using: php artisan make:model ModelName -m');

                    continue;
                }

            } catch (ReflectionException $e) {
                $this->error("Error processing {$className}: {$e->getMessage()}");

                continue;
            }

            $this->line("Generating Filament Resource for model: {$className}");

            try {
                Artisan::call('make:filament-resource', [
                    'name' => $className,
                    '--generate' => true,
                    '--force' => true,
                ]);

                $this->info("✓ Successfully generated resource for {$className}");
            } catch (Exception $e) {
                $this->error("✗ Failed to generate resource for {$className}: {$e->getMessage()}");
            }
        }

        $this->info('Filament Resources generation completed!');
    }

    /**
     * Check for required related models in the given class.
     */
    protected function checkRequiredModels(ReflectionClass $reflection): array
    {
        $missingModels = [];
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Skip methods that don't return a relationship
            if ($method->getNumberOfParameters() > 0 || $method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }

            try {
                $model = $reflection->newInstance();
                $result = $method->invoke($model);

                if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $relatedModel = get_class($result->getRelated());
                    if (! class_exists($relatedModel)) {
                        $missingModels[] = class_basename($relatedModel);
                    }
                }
            } catch (Exception $e) {
                // Skip methods that can't be invoked
                continue;
            }
        }

        return array_unique($missingModels);
    }
}
