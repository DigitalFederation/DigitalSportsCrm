<?php

namespace Domain\Imports\Actions;

use App\Jobs\ProcessImportChunkJob;
use Domain\Imports\Models\Import;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImportFileAction
{
    protected $chunkSize = 1000;

    /**
     * Process an import file in chunks.
     */
    public function execute(Import $import): void
    {
        $filePath = Storage::disk('local')->path($import->file_path);

        // Determine file type and read accordingly
        $extension = pathinfo($import->filename, PATHINFO_EXTENSION);

        if (strtolower($extension) === 'csv') {
            $this->processCSV($import, $filePath);
        } else {
            $this->processExcel($import, $filePath);
        }
    }

    /**
     * Process CSV file in chunks.
     */
    protected function processCSV(Import $import, string $filePath): void
    {
        // Read file content and remove BOM if present
        $content = file_get_contents($filePath);
        $bom = "\xEF\xBB\xBF";
        if (str_starts_with($content, $bom)) {
            $content = substr($content, 3);
        }

        $reader = Reader::createFromString($content);

        // Auto-detect delimiter (supports comma, semicolon, tab, pipe)
        $delimiters = [',', ';', "\t", '|'];
        $delimiter = $this->detectDelimiter($filePath, $delimiters);
        $reader->setDelimiter($delimiter);

        $reader->setHeaderOffset(0);

        // Get total row count
        $totalRows = iterator_count($reader) - 1; // Minus header row
        $import->update(['total_rows' => $totalRows]);

        // Reset reader - need to recreate from string
        $reader = Reader::createFromString($content);
        $reader->setDelimiter($delimiter);
        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();

        $chunk = [];
        $chunkIndex = 0;

        foreach ($records as $offset => $record) {
            // Trim all values
            $chunk[] = array_map('trim', $record);

            if (count($chunk) >= $this->chunkSize) {
                // Run chunk synchronously
                ProcessImportChunkJob::dispatchSync($import->id, $chunk, $chunkIndex);

                $chunk = [];
                $chunkIndex++;
            }
        }

        // Process remaining records
        if (! empty($chunk)) {
            ProcessImportChunkJob::dispatchSync($import->id, $chunk, $chunkIndex);
        }
    }

    /**
     * Process Excel file in chunks.
     */
    protected function processExcel(Import $import, string $filePath): void
    {
        // First, get the total row count
        $data = Excel::toArray(new class {}, $filePath)[0];
        $totalRows = count($data) - 1; // Minus header row
        $import->update(['total_rows' => $totalRows]);

        // Get headers
        $headers = array_shift($data);

        // Process in chunks
        $chunks = array_chunk($data, $this->chunkSize);

        foreach ($chunks as $chunkIndex => $chunkData) {
            // Convert arrays back to associative arrays with headers
            $processedChunk = [];
            foreach ($chunkData as $row) {
                if (count($row) === count($headers)) {
                    $processedChunk[] = array_combine($headers, $row);
                }
            }

            if (! empty($processedChunk)) {
                ProcessImportChunkJob::dispatchSync($import->id, $processedChunk, $chunkIndex);
            }
        }
    }

    /**
     * Set chunk size.
     */
    public function setChunkSize(int $size): self
    {
        $this->chunkSize = $size;

        return $this;
    }

    /**
     * Calculate optimal chunk size based on available memory.
     */
    public function calculateOptimalChunkSize(): int
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);

        // Use 25% of available memory for safety
        $availableMemory = $memoryLimitBytes * 0.25;

        // Estimate 1KB per row
        $estimatedRowSize = 1024;

        $optimalSize = (int) ($availableMemory / $estimatedRowSize);

        // Keep within reasonable bounds
        return max(100, min(5000, $optimalSize));
    }

    /**
     * Detect CSV delimiter by analyzing the first line.
     */
    protected function detectDelimiter(string $filePath, array $delimiters = [',', ';', "\t", '|']): string
    {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        // Return the delimiter with the highest count
        arsort($counts);
        $detectedDelimiter = array_key_first($counts);

        // If no delimiter found, default to comma
        return $counts[$detectedDelimiter] > 0 ? $detectedDelimiter : ',';
    }

    /**
     * Convert memory limit string to bytes.
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }
}
