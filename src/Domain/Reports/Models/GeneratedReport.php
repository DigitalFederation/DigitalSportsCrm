<?php

namespace Domain\Reports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GeneratedReport extends Model
{
    protected $fillable = [
        'name',
        'generated_by',
        'generated_on',
        'file_path',
        'status',
        'insurer_status',
        'filters',
        'file_size',
    ];

    protected $casts = [
        'generated_on' => 'datetime',
        'filters' => 'array', // Cast the filters column to array
    ];

    // belongsTo a User
    public function user()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Get human readable file size
    public function getHumanFileSizeAttribute()
    {
        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    // Calculate and set file size when file is stored
    public function setFilePath($path)
    {
        $this->file_path = $path;

        // Use consistent file path resolution
        $fullPath = storage_path('app/' . $path);

        if (file_exists($fullPath)) {
            $this->file_size = filesize($fullPath);
            $this->save();
        } else {
            \Log::warning('File not found when setting file path', [
                'path' => $path,
                'full_path' => $fullPath,
                'report_id' => $this->id,
            ]);
        }

        return $this;
    }

    // Get date range as a formatted string
    public function getDateRangeAttribute()
    {
        $filters = $this->filters ?? [];
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if (! $startDate && ! $endDate) {
            return 'All time';
        }

        $start = $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : 'All time';
        $end = $endDate ? \Carbon\Carbon::parse($endDate)->format('M d, Y') : 'Present';

        return "{$start} → {$end}";
    }

    // Status badge color helper
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'ready' => 'green',
            'processing' => 'yellow',
            'failed' => 'red',
            default => 'gray',
        };
    }

    // Check if report is downloadable
    public function getIsDownloadableAttribute()
    {
        return $this->status === 'ready' && Storage::exists($this->file_path);
    }
}
