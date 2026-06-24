<?php

namespace Domain\Diagnostics\Data;

class DiagnosticResult
{
    /**
     * @param  array<EligibilityCheck>  $checks
     * @param  array<string>  $failedChecks
     * @param  array<string>  $suggestions
     */
    public function __construct(
        public bool $isEligible,
        public array $checks,
        public array $failedChecks = [],
        public array $suggestions = [],
        public array $debugData = []
    ) {}

    public function toArray(): array
    {
        return [
            'isEligible' => $this->isEligible,
            'checks' => array_map(fn (EligibilityCheck $check) => $check->toArray(), $this->checks),
            'failedChecks' => $this->failedChecks,
            'suggestions' => $this->suggestions,
            'debugData' => $this->debugData,
        ];
    }

    public function getFailedChecks(): array
    {
        return array_filter($this->checks, fn (EligibilityCheck $check) => ! $check->passed);
    }

    public function getPassedChecks(): array
    {
        return array_filter($this->checks, fn (EligibilityCheck $check) => $check->passed);
    }
}
