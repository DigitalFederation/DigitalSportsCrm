<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait MorphAliasHelper
{
    /**
     * Get the morph alias for a given class or return the alias if already a morph string
     *
     * @param  string|object  $class
     */
    public static function getMorphAlias($class): string
    {
        // If it's an object, get its class name
        if (is_object($class)) {
            $class = get_class($class);
        }

        // Get the morph map
        $morphMap = Relation::morphMap();

        // If it's already a morph alias, return it
        if (in_array($class, array_keys($morphMap))) {
            return $class;
        }

        // Search for the class in the morph map and return its alias
        $alias = array_search($class, $morphMap);

        // If found, return the alias; otherwise return the original class
        return $alias !== false ? $alias : $class;
    }

    /**
     * Check if a string is a morph alias
     */
    public static function isMorphAlias(string $value): bool
    {
        $morphMap = Relation::morphMap();

        return isset($morphMap[$value]);
    }

    /**
     * Get the class name from a morph alias
     */
    public static function getClassFromMorphAlias(string $alias): ?string
    {
        $morphMap = Relation::morphMap();

        return $morphMap[$alias] ?? null;
    }
}
