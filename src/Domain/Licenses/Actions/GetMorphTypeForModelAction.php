<?php

namespace Domain\Licenses\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Get the correct morph type for a model
 * This ensures consistency with Laravel's morph map
 */
class GetMorphTypeForModelAction
{
    /**
     * Execute the action
     *
     * @param  string|Model  $model  The model class name or instance
     * @return string The morph type to use in database
     */
    public function execute($model): string
    {
        // If it's a class string, instantiate it
        if (is_string($model)) {
            $instance = new $model;
        } else {
            $instance = $model;
        }

        // Use Laravel's getMorphClass which respects the morph map
        return $instance->getMorphClass();
    }

    /**
     * Get morph type from class name string
     *
     * @param  string  $className  Full class name
     * @return string The morph type
     */
    public function fromClassName(string $className): string
    {
        // Handle the case where the class might not exist
        if (! class_exists($className)) {
            // Fallback to extracting from known patterns
            return $this->extractMorphAlias($className);
        }

        return $this->execute($className);
    }

    /**
     * Extract morph alias from full class name
     * Used as fallback when class doesn't exist
     */
    private function extractMorphAlias(string $className): string
    {
        $morphMap = [
            'Domain\\Entities\\Models\\Entity' => 'entity',
            'Domain\\Individuals\\Models\\Individual' => 'individual',
            'Domain\\Federations\\Models\\Federation' => 'federation',
        ];

        return $morphMap[$className] ?? $className;
    }
}
