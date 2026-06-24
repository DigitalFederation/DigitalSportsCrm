<?php

namespace Tests\Unit\Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseValidityDatesTest extends TestCase
{
    #[Test]
    public function it_demonstrates_license_validity_calculation_scenarios()
    {
        $action = new CalculateLicenseValidityDatesAction;

        // Scenario 1: Valid for 1 year from start date
        $yearlyLicense = new License([
            'interval' => 1,
            'interval_unit' => 'years',
        ]);

        $startDate = Carbon::parse('2025-03-15');
        $result = $action->execute($yearlyLicense, $startDate);

        $this->assertEquals('2025-03-15', $result['start_date']->format('Y-m-d'));
        $this->assertEquals('2026-03-15', $result['end_date']->format('Y-m-d'));
        $this->assertTrue($result['end_date']->isEndOfDay());

        // Scenario 2: Valid for X years (multi-year)
        $multiYearLicense = new License([
            'interval' => 3,
            'interval_unit' => 'years',
        ]);

        $result = $action->execute($multiYearLicense, $startDate);

        $this->assertEquals('2025-03-15', $result['start_date']->format('Y-m-d'));
        $this->assertEquals('2028-03-15', $result['end_date']->format('Y-m-d'));

        // Scenario 3: Without validation date (no expiration)
        $noExpirationLicense = new License([
            'interval' => null,
            'interval_unit' => null,
        ]);

        $result = $action->execute($noExpirationLicense, $startDate);

        $this->assertEquals('2025-03-15', $result['start_date']->format('Y-m-d'));
        $this->assertNull($result['end_date']);
    }

    #[Test]
    public function it_shows_how_license_attributed_dates_are_set()
    {
        // This test shows how the dates would be set on a LicenseAttributed model
        $license = new License([
            'interval' => 6,
            'interval_unit' => 'months',
        ]);

        $action = new CalculateLicenseValidityDatesAction;
        $dates = $action->execute($license, Carbon::parse('2025-01-01'));

        // In practice, these would be set on the LicenseAttributed model:
        // $licenseAttributed->current_term_starts_at = $dates['start_date']->format('Y-m-d H:i:s');
        // $licenseAttributed->current_term_ends_at = $dates['end_date']?->format('Y-m-d H:i:s');

        $this->assertEquals('2025-01-01 00:00:00', $dates['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-07-01 23:59:59', $dates['end_date']->format('Y-m-d H:i:s'));
    }
}
