<?php

namespace Tests\Feature\Insurance;

use Domain\Insurance\Models\Insurance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsuranceTableLazyLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_renders_without_lazy_loading_member(): void
    {
        // Reproduces the individual dashboard crash: insurances were loaded with
        // their plan but NOT their polymorphic `member` (as DashboardController did
        // before the fix). With preventLazyLoading on, reading $insurance->member
        // in the table threw LazyLoadingViolationException.
        Model::preventLazyLoading(true);

        Insurance::factory()->count(2)->create();

        // `member` intentionally NOT eager-loaded; mirror the base collection the
        // profile-tabbed view builds by flat-mapping member subscriptions.
        $insurances = collect(Insurance::with('insurancePlan')->get()->all());
        $this->assertFalse($insurances->first()->relationLoaded('member'));

        $view = $this->blade(
            '<x-individual.insurance-table :insurances="$insurances" context="individual" />',
            ['insurances' => $insurances],
        );

        $view->assertSee($insurances->first()->insurancePlan->name);
    }
}
