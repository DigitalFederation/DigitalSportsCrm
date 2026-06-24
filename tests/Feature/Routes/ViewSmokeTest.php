<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Route Exclusions
|--------------------------------------------------------------------------
|
| Routes excluded from smoke testing because they require specific model
| instances or query parameters to work properly.
|
| Dead code routes were removed from the route files on 2026-01-09.
|
*/

/**
 * NEEDS REAL DATA - Routes that require specific query parameters or model instances.
 * These routes work but need proper setup beyond simple ID replacement.
 */
const NEEDS_REAL_DATA_ROUTES = [
    // Entity routes needing query parameters
    'entity.certification-attributed.create',        // Needs filter.committee query param
    'entity.certification-attributed.wizard.create', // Needs filter.committee query param
    'entity.evt-events.events.athlete-enrollment.create', // Needs valid event
    'entity.evt-events.events.disciplines.athlete-enrollment.create', // Needs valid event + discipline
    'entity.diving_licenses.invitations',            // Needs entity with invitations
    'entity.diving_license_directors.create',        // Needs valid licenseAttributed ID

    // Federation routes needing specific data
    'federation.profile.edit',                       // Needs federation profile setup
    'federation.license-attributed.create',          // Needs license_type_name + committee
    'federation.local-membership-plan.index',        // Needs main federation (ensureIsMainFederation middleware)
    'federation.local-membership-plan.create',       // Needs main federation
    'federation.local-membership-plan.edit',         // Needs main federation + valid plan
    'federation.committee.attachments.index',        // Needs committee setup
    'federation.attachments.index',                  // Needs attachment data
    'federation.committee.attachments-sent.index',   // Needs committee setup
    'federation.attachments-sent.index',             // Needs attachment data

    // Admin routes needing specific model instances
    'admin.official-documents.index',           // Needs valid document type
    'admin.role-mappings.federations.index',    // Needs role mappings setup
    'admin.route-permissions.preview',          // Needs permission preview data
    'admin.individual.show',                    // Needs valid individual ID
    'admin.membership-plan.edit',               // Needs valid membership plan
    'admin.certification.edit',                 // Needs valid certification
    'admin.license.edit',                       // Needs valid license
    'admin.document.create',                    // Needs document setup
    'admin.diving_technical_director_invitations.index', // Needs invitations data
    'admin.diving_technical_director_invitations.show',  // Needs valid invitation
    'admin.evt-events.sport-age-groups.index',  // Needs sport age groups
    'admin.evt-events.sport-age-groups.show',   // Needs valid sport age group
    'admin.evt-events.sport.index',             // Needs sports data
    'admin.evt-events.sport.create',            // Needs sports setup
    'admin.shipping.zones.create',              // Needs shipping setup
    'admin.shipping.zones.show',                // Needs valid zone
    'admin.shipping.sub-zones.create',          // Needs shipping setup
    'admin.shipping.sub-zones.show',            // Needs valid sub-zone
    'admin.shipping.prices.create',             // Needs shipping setup
    'admin.shipping.prices.show',               // Needs valid price
    'admin.event-applications.show',            // Needs valid event application
];

/**
 * INTENTIONAL REDIRECTS - Routes that redirect to other routes by design.
 */
const INTENTIONAL_REDIRECT_ROUTES = [
    'entity.diving_licenses.create', // Redirects to entity.diving_licenses.request
    'entity.event-applications.create', // Uses create-from-template or create-direct instead
];

/**
 * Get all excluded route names.
 */
function getExcludedRouteNames(): array
{
    return array_merge(
        NEEDS_REAL_DATA_ROUTES,
        INTENTIONAL_REDIRECT_ROUTES
    );
}

/**
 * Create a user with proper relationships for their group.
 * This ensures the user can access routes that check for entity/federation/individual relationships.
 */
function createUserForGroup(string $groupCode): User
{
    $user = User::factory()->forGroup($groupCode)->create();

    switch ($groupCode) {
        case 'INDIVIDUAL':
            Individual::factory()->create(['user_id' => $user->id]);
            $user->assignRole('individual-approved');
            break;

        case 'ENTITY':
            $entity = Entity::factory()->create();
            $user->entities()->attach($entity->id);
            $user->assignRole('entity-admin');
            break;

        case 'FEDERATION':
            $federation = Federation::factory()->create();
            Membership::factory()->create([
                'federation_id' => $federation->id,
                'status_class' => ActiveMembershipState::class,
            ]);
            $user->federations()->attach($federation->id);
            $user->assignRole('federation-admin');
            break;

        case 'ADMIN':
            $user->assignRole('admin');
            break;
    }

    return $user;
}

/**
 * Get all GET routes for a given prefix, excluding system routes and known problematic routes.
 */
function getRoutesForPrefix(string $prefix): array
{
    $excludedPatterns = [
        'debugbar',
        '_ignition',
        'clockwork',
        '_boost',
        'livewire',
        'sanctum',
    ];

    $excludedRouteNames = getExcludedRouteNames();

    return collect(Route::getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods()))
        ->filter(function ($route) use ($prefix) {
            $uri = $route->uri();

            return str_starts_with($uri, $prefix);
        })
        ->reject(function ($route) use ($excludedPatterns) {
            $uri = $route->uri();
            foreach ($excludedPatterns as $pattern) {
                if (str_contains($uri, $pattern)) {
                    return true;
                }
            }

            return false;
        })
        ->reject(function ($route) use ($excludedRouteNames) {
            $name = $route->getName();

            return $name && in_array($name, $excludedRouteNames);
        })
        ->map(fn ($route) => [
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ])
        ->values()
        ->toArray();
}

/**
 * Replace route parameters with 1 for basic testing.
 * More sophisticated tests should use real model IDs.
 */
function replaceRouteParams(string $uri): string
{
    return preg_replace('/\{[^}]+\}/', '1', $uri);
}

/**
 * Get the status code from a response, handling special response types.
 * Returns null for responses that should be skipped (like file downloads).
 */
function getResponseStatus($response): ?int
{
    $baseResponse = $response->baseResponse ?? $response;

    if ($baseResponse instanceof StreamedResponse || $baseResponse instanceof BinaryFileResponse) {
        return null;
    }

    return $response->status();
}

beforeEach(function () {
    $this->withoutVite();

    $this->artisan('db:seed', ['--class' => 'UserGroupSeeder']);
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
});

describe('Individual Routes', function () {
    it('can access the individual dashboard', function () {
        $user = createUserForGroup('INDIVIDUAL');

        $response = $this->actingAs($user)->get('/individual/dashboard');

        expect($response->status())->not->toBe(500);
        expect($response->status())->not->toBe(404);
    });

    it('does not return 500 on individual routes', function () {
        $user = createUserForGroup('INDIVIDUAL');
        $routes = getRoutesForPrefix('individual/');

        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = replaceRouteParams($route['uri']);
            $response = $this->actingAs($user)->get('/'.$uri);
            $status = getResponseStatus($response);

            if ($status === null) {
                continue;
            }

            if ($status === 500) {
                $failedRoutes[] = [
                    'name' => $route['name'] ?? 'unnamed',
                    'uri' => $route['uri'],
                    'status' => $status,
                    'exception' => $response->exception?->getMessage(),
                ];
            }
        }

        expect($failedRoutes)->toBeEmpty(
            'Routes with 500 errors: '.json_encode($failedRoutes, JSON_PRETTY_PRINT)
        );
    });
});

describe('Entity Routes', function () {
    it('can access the entity dashboard', function () {
        $user = createUserForGroup('ENTITY');

        $response = $this->actingAs($user)->get('/entity/dashboard');

        expect($response->status())->not->toBe(500);
        expect($response->status())->not->toBe(404);
    });

    it('does not return 500 on entity routes', function () {
        $user = createUserForGroup('ENTITY');
        $routes = getRoutesForPrefix('entity/');

        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = replaceRouteParams($route['uri']);
            $response = $this->actingAs($user)->get('/'.$uri);
            $status = getResponseStatus($response);

            if ($status === null) {
                continue;
            }

            if ($status === 500) {
                $failedRoutes[] = [
                    'name' => $route['name'] ?? 'unnamed',
                    'uri' => $route['uri'],
                    'status' => $status,
                    'exception' => $response->exception?->getMessage(),
                ];
            }
        }

        expect($failedRoutes)->toBeEmpty(
            'Routes with 500 errors: '.json_encode($failedRoutes, JSON_PRETTY_PRINT)
        );
    });
});

describe('Federation Routes', function () {
    it('can access the federation dashboard', function () {
        $user = createUserForGroup('FEDERATION');

        $response = $this->actingAs($user)->get('/federation/dashboard');

        expect($response->status())->not->toBe(500);
        expect($response->status())->not->toBe(404);
    });

    it('does not return 500 on federation routes', function () {
        $user = createUserForGroup('FEDERATION');
        $routes = getRoutesForPrefix('federation/');

        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = replaceRouteParams($route['uri']);
            $response = $this->actingAs($user)->get('/'.$uri);
            $status = getResponseStatus($response);

            if ($status === null) {
                continue;
            }

            if ($status === 500) {
                $failedRoutes[] = [
                    'name' => $route['name'] ?? 'unnamed',
                    'uri' => $route['uri'],
                    'status' => $status,
                    'exception' => $response->exception?->getMessage(),
                ];
            }
        }

        expect($failedRoutes)->toBeEmpty(
            'Routes with 500 errors: '.json_encode($failedRoutes, JSON_PRETTY_PRINT)
        );
    });
});

describe('Admin & International Routes', function () {
    it('can access the admin dashboard', function () {
        $user = createUserForGroup('ADMIN');

        $response = $this->actingAs($user)->get('/admin/dashboard');

        expect($response->status())->not->toBe(500);
        expect($response->status())->not->toBe(404);
    });

    it('does not return 500 on admin routes', function () {
        $user = createUserForGroup('ADMIN');
        $routes = getRoutesForPrefix('admin/');

        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = replaceRouteParams($route['uri']);
            $response = $this->actingAs($user)->get('/'.$uri);
            $status = getResponseStatus($response);

            if ($status === null) {
                continue;
            }

            if ($status === 500) {
                $failedRoutes[] = [
                    'name' => $route['name'] ?? 'unnamed',
                    'uri' => $route['uri'],
                    'status' => $status,
                    'exception' => $response->exception?->getMessage(),
                ];
            }
        }

        expect($failedRoutes)->toBeEmpty(
            'Routes with 500 errors: '.json_encode($failedRoutes, JSON_PRETTY_PRINT)
        );
    });

    it('does not return 500 on international routes', function () {
        $user = createUserForGroup('ADMIN');
        $routes = getRoutesForPrefix('international/');

        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = replaceRouteParams($route['uri']);
            $response = $this->actingAs($user)->get('/'.$uri);
            $status = getResponseStatus($response);

            if ($status === null) {
                continue;
            }

            if ($status === 500) {
                $failedRoutes[] = [
                    'name' => $route['name'] ?? 'unnamed',
                    'uri' => $route['uri'],
                    'status' => $status,
                    'exception' => $response->exception?->getMessage(),
                ];
            }
        }

        expect($failedRoutes)->toBeEmpty(
            'Routes with 500 errors: '.json_encode($failedRoutes, JSON_PRETTY_PRINT)
        );
    });
});

describe('Public Routes', function () {
    it('can access the welcome page', function () {
        $response = $this->get('/');

        expect($response->status())->not->toBe(500);
    });

    it('can access the login page', function () {
        $response = $this->get('/login');

        expect($response->status())->toBe(200);
    });

    it('registration page does not return 500', function () {
        $response = $this->get('/register');

        // Registration may be disabled (404) or enabled (200)
        expect($response->status())->not->toBe(500);
    });
});

/*
|--------------------------------------------------------------------------
| Dead Code Routes - CLEANED UP on 2026-01-09
|--------------------------------------------------------------------------
|
| The following dead code routes were removed from the route files:
|
| Entity (routes/routes_entity.php):
| - Removed: subscriptions.create, subscriptions.edit
| - Removed: certification-attributed.edit
| - Added 'create' to attachments resource except list
|
| Federation (routes/routes_federation.php):
| - Removed: official-documents.edit
| - Added 'show' to local-membership-plan except list
| - Added 'create' to attachments and attachments-sent resource except lists
| - Added 'create' to event-applications resource except list
| - Removed: membership.create/show/edit/activate (stub controller routes)
| - Removed: export.download (broken GET route calling store())
|
| Admin (routes/routes_admin.php):
| - Removed 'show' from insurance-plans only list
| - Changed staff-roles to only(['index', 'store', 'destroy'])
| - Added 'show' to attribute-group and discipline-templates except lists
| - Added 'show' to shipping methods and weights except lists
|
| Total routes cleaned up: 25 dead code routes removed
| Remaining exclusions: Routes that need real model data to render
|
*/
