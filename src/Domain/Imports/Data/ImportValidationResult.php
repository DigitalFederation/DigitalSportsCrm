<?php

namespace Domain\Imports\Data;

class ImportValidationResult
{
    public function __construct(
        public readonly int $totalRows,
        public readonly int $validRows,
        public readonly int $errorRows,
        public readonly int $warningRows,
        public readonly array $errors,
        public readonly array $warnings,
        public readonly array $validRecords,
        public readonly array $sampleValidRecords,
        public readonly array $sampleErrorRecords,
        public readonly bool $hasErrors,
        public readonly float $validationTime,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalRows: $data['total_rows'],
            validRows: $data['valid_rows'],
            errorRows: $data['error_rows'],
            warningRows: $data['warning_rows'],
            errors: $data['errors'],
            warnings: $data['warnings'],
            validRecords: $data['valid_records'],
            sampleValidRecords: $data['sample_valid_records'],
            sampleErrorRecords: $data['sample_error_records'],
            hasErrors: $data['has_errors'],
            validationTime: $data['validation_time'],
        );
    }

    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'valid_rows' => $this->validRows,
            'error_rows' => $this->errorRows,
            'warning_rows' => $this->warningRows,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'valid_records' => $this->validRecords,
            'sample_valid_records' => $this->sampleValidRecords,
            'sample_error_records' => $this->sampleErrorRecords,
            'has_errors' => $this->hasErrors,
            'validation_time' => $this->validationTime,
        ];
    }

    public function getValidationSummary(): string
    {
        return sprintf(
            '%d total rows: %d valid, %d errors, %d warnings (%.2fs)',
            $this->totalRows,
            $this->validRows,
            $this->errorRows,
            $this->warningRows,
            $this->validationTime
        );
    }
}
