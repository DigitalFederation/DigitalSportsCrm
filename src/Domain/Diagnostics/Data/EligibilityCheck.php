<?php

namespace Domain\Diagnostics\Data;

class EligibilityCheck
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $passed,
        public string $message,
        public ?string $suggestion = null,
        public array $details = []
    ) {}

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'passed' => $this->passed,
            'message' => $this->message,
            'suggestion' => $this->suggestion,
            'details' => $this->details,
        ];
    }
}
