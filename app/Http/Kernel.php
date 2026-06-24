<?php

namespace App\Http;

use App\Http\Middleware\EnsureUserHasGroup;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ImpersonateMiddleware::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        'route.permission' => \App\Http\Middleware\CheckRoutePermission::class,
        'user_group' => EnsureUserHasGroup::class,
        'empty_query_string' => \App\Http\Middleware\RemoveParametersWithEmptyValueFromQueryString::class,
        'user.relations' => \App\Http\Middleware\UserCheckIfEmptyRelations::class,
        'check_entity_can_invite' => \App\Http\Middleware\CheckEntityCanInvite::class,
        'ensureIsMainFederation' => \App\Http\Middleware\EnsureIsMainFederation::class,
        'ensureIsDefaultFederation' => \App\Http\Middleware\EnsureIsDefaultFederation::class,
        'ensureAntiDopingPinIsValid' => \App\Http\Middleware\EnsureAntiDopingPinIsValidMiddleware::class,
        'check.entity.role' => \App\Http\Middleware\CheckEntityRole::class,
        'check.active.user' => \App\Http\Middleware\CheckActiveUser::class,
        'custom-signed' => \App\Http\Middleware\CustomValidateSignature::class,
        'check.federation.membership' => \App\Http\Middleware\CheckFederationMembershipStatus::class,
        'event.role' => \App\Http\Middleware\EventRoleMiddleware::class,
        'federation.can_issue_certifications' => \App\Http\Middleware\EnsureFederationCanIssueCertifications::class,
        'federation.can_manage_committee' => \App\Http\Middleware\EnsureFederationCanManageCommittee::class,
        'ensure.profile.photo' => \App\Http\Middleware\EnsureProfilePhotoExists::class,
    ];
}
