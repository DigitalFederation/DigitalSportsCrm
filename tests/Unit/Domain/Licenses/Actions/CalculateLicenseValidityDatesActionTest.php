<?php

namespace Tests\Unit\Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\License;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculateLicenseValidityDatesActionTest extends TestCase
{
    private CalculateLicenseValidityDatesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculateLicenseValidityDatesAction;
    }

    #[Test]
    public function it_calculates_weekly_license_validity()
    {
        $license = new License([
            'interval' => 2,
            'interval_unit' => 'weeks',
        ]);

        $startDate = Carbon::parse('2025-01-01 10:00:00');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-01-01 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-15 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_monthly_license_validity()
    {
        $license = new License([
            'interval' => 3,
            'interval_unit' => 'months',
        ]);

        $startDate = Carbon::parse('2025-01-15');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-01-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-04-15 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_single_year_license_validity()
    {
        $license = new License([
            'interval' => 1,
            'interval_unit' => 'years',
        ]);

        $startDate = Carbon::parse('2025-03-15');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-03-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-15 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_calculates_multi_year_license_validity()
    {
        $license = new License([
            'interval' => 3,
            'interval_unit' => 'years',
        ]);

        $startDate = Carbon::parse('2025-01-01');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-01-01 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2028-01-01 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_handles_licenses_without_expiration()
    {
        $license = new License([
            'interval' => null,
            'interval_unit' => null,
        ]);

        $startDate = Carbon::parse('2025-01-01');
        $result = $this->action->execute($license, $startDate);

        $this->assertEquals('2025-01-01 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertNull($result['end_date']);
    }

    #[Test]
    public function it_handles_licenses_with_missing_interval_unit()
    {
        $license = new License([
            'interval' => 5,
            'interval_unit' => null,
        ]);

        $result = $this->action->execute($license);

        $this->assertInstanceOf(Carbon::class, $result['start_date']);
        $this->assertNull($result['end_date']);
    }

    #[Test]
    public function it_uses_current_date_when_no_start_date_provided()
    {
        $license = new License([
            'interval' => 1,
            'interval_unit' => 'months',
        ]);

        Carbon::setTestNow('2025-06-15 14:30:00');

        $result = $this->action->execute($license);

        $this->assertEquals('2025-06-15 00:00:00', $result['start_date']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-07-15 23:59:59', $result['end_date']->format('Y-m-d H:i:s'));

        Carbon::setTestNow(); // Reset
    }
}
