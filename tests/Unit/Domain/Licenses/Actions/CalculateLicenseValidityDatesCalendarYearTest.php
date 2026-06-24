<?php

namespace Tests\Unit\Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\License;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculateLicenseValidityDatesCalendarYearTest extends TestCase
{
    private CalculateLicenseValidityDatesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculateLicenseValidityDatesAction;
    }

    #[Test]
    public function it_calculates_single_year_calendar_license()
    {
        $license = new License([
            'interval' => 1,
            'interval_unit' => 'years',
            'validity_type' => 'calendar_year',
        ]);

        $startDate = Carbon::parse('2025-03-15');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-03-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-12-31 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_multi_year_calendar_license()
    {
        $license = new License([
            'interval' => 3,
            'interval_unit' => 'years',
            'validity_type' => 'calendar_year',
        ]);

        $startDate = Carbon::parse('2025-03-15');
        $result = $this->action->execute($license, $startDate);

        // 3 years: 2025 + 2 = 2027, end of year
        $this->assertEquals('2025-03-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2027-12-31 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_two_year_calendar_license()
    {
        $license = new License([
            'interval' => 2,
            'interval_unit' => 'years',
            'validity_type' => 'calendar_year',
        ]);

        $startDate = Carbon::parse('2025-08-01');
        $result = $this->action->execute($license, $startDate);

        // 2 years: 2025 + 1 = 2026, end of year
        $this->assertEquals('2025-08-01 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-12-31 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_calendar_year_license_activated_late_in_year()
    {
        $license = new License([
            'interval' => 1,
            'interval_unit' => 'years',
            'validity_type' => 'calendar_year',
        ]);

        // Activated in December
        $startDate = Carbon::parse('2025-12-15');
        $result = $this->action->execute($license, $startDate);

        // Should still expire on Dec 31 of the same year
        $this->assertEquals('2025-12-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-12-31 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_handles_fixed_duration_for_yearly_licenses_when_specified()
    {
        $license = new License([
            'interval' => 3,
            'interval_unit' => 'years',
            'validity_type' => 'fixed_duration',
        ]);

        $startDate = Carbon::parse('2025-03-15');
        $result = $this->action->execute($license, $startDate);

        // Fixed duration: exactly 3 years from start
        $this->assertEquals('2025-03-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2028-03-15 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }
}
