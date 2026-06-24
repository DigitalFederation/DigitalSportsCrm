<?php

namespace App\Http\Middleware;

use Closure;

class RemoveParametersWithEmptyValueFromQueryString
{
    /**
     * Remove parameters with empty value from query string
     *
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $query = $request->query->all();
        $request->query->replace($this->remove_empty_parameters($query));

        return $next($request);
    }

    /**
     * Remove empty parameters
     *
     *
     * @return mixed
     */
    public function remove_empty_parameters($array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->remove_empty_parameters($value);

                if (! count($value)) {
                    unset($array[$key]);
                }
            } elseif (! strlen($value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
