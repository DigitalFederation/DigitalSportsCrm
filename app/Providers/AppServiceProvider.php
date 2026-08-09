<?php

namespace App\Providers;

use App\Domain\EvtEvents\Actions\CreateIndividualAthleteEnrollmentAction;
use App\Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use App\Domain\EvtEvents\Actions\ValidateAndSummarizeAthleteEnrollmentsAction;
use App\Models\RoutePermission;
use App\Observers\DocumentObserver;
use App\Observers\RoutePermissionObserver;
use App\Plugins\PluginManager;
use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registry of installed plugins (see App\Plugins\PluginServiceProvider).
        $this->app->singleton(PluginManager::class);

        $this->app->bind(CreateIndividualAthleteEnrollmentAction::class, function ($app) {
            return new CreateIndividualAthleteEnrollmentAction(
                $app->make(GetAttributesAndRulesFromDisciplineAction::class),
                $app->make(ValidateAndSummarizeAthleteEnrollmentsAction::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading();

        // Register model observers
        Document::observe(DocumentObserver::class);
        RoutePermission::observe(RoutePermissionObserver::class);

        Relation::morphMap([
            'entity' => \Domain\Entities\Models\Entity::class,
            'individual' => \Domain\Individuals\Models\Individual::class,
            'federation' => \Domain\Federations\Models\Federation::class,
        ]);

        $this->applySiteSettingsOverrides();
    }

    /**
     * Overlay database-managed homepage settings on top of the
     * env-based branding config. Silently skipped when the table
     * does not exist yet (fresh install, pre-migration console runs).
     */
    private function applySiteSettingsOverrides(): void
    {
        try {
            $settings = \App\Models\SiteSetting::allSettings();
        } catch (\Throwable) {
            return;
        }

        $map = [
            'app_name' => 'app.name',
            'federation_name' => 'branding.primary.name',
            'federation_about' => 'branding.primary.about',
            'federation_address' => 'branding.primary.address',
            'federation_support_email' => 'branding.primary.support_email',
            'logo_path' => 'branding.primary.logo_path',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = $settings[$settingKey] ?? null;
            if ($value !== null && $value !== '') {
                config([$configKey => $value]);
            }
        }

        if (! empty($settings['app_name'])) {
            config(['branding.primary.portal_name' => $settings['app_name']]);
        }
    }
}
