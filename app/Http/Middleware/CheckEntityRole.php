<?php

namespace App\Http\Middleware;

use Closure;

class CheckEntityRole
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $committee = $request->query('filter')['committee'] ?? '';

        // entity-admin has access to everything
        if ($user->hasRole('entity-admin') || empty($committee)) {
            return $next($request);
        }

        // Map committee codes to specific roles
        $roleMapping = [
            'sport' => 'entity-sport',
            'diving' => 'entity-international',           // CMAS Diving (international)
            'divingservices' => 'entity-diving-services', // Diving Services (non-international)
            'scientific' => 'entity-international',       // CMAS Scientific (international)
        ];

        $committeeRole = $roleMapping[$committee] ?? null;

        if ($committeeRole && $user->hasRole($committeeRole)) {
            return $next($request);
        }

        // Handle unauthorized access
        abort(403, 'Unauthorized action.');
    }
}
