<?php

declare(strict_types=1);

namespace Domain\Certifications\Services;

use App\Exceptions\CertificationCardGenerationException;
use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use RuntimeException;

class CertificationCardGeneratorService
{
    private ImageManager $imageManager;
    private string $fontPath;
    private string $fontBoldPath;
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
        $this->fontPath = public_path('fonts/Inter-VariableFont_opsz,wght.ttf');
        $this->fontBoldPath = public_path('fonts/Inter_28pt-Bold.ttf');
    }

    public function generate(CertificationAttributed $certification): string
    {

        try {
            // Validate required fields first
            $this->validateRequiredFields($certification);

            $image = $this->createBaseImage();

            // Add grid for development purposes
            if (config('app.debug')) {
                // $this->addGrid($image);
            }

            $this->addProfileImage($image, $certification);
            $this->addLogo($image, $certification);
            $this->addCertificationDetails($image, $certification);
            $this->addDivider($image);
            $this->addQRCode($image, $certification);
            $this->addAdditionalInfo($image, $certification);

            return $this->saveImage($image, $certification);
        } catch (CertificationCardGenerationException $e) {
            // Re-throw CertificationCardGenerationException directly
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to generate certification card', [
                'certification_id' => $certification->id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Failed to generate certification card', 0, $e);
        }
    }

    private function validateRequiredFields(CertificationAttributed $certification): void
    {
        $certification->load('individual', 'federation.country', 'entity');

        // First validate the profile image using Storage facade for cloud compatibility
        $hasValidProfileImage = false;
        $profileMedia = $certification->individual?->getFirstMedia('profile');
        if ($profileMedia) {
            $disk = Storage::disk($profileMedia->disk);
            $hasValidProfileImage = $disk->exists($profileMedia->getPathRelativeToRoot());
        }

        $requiredFields = [
            'individual.full_name' => __('Diver Name'),
            'certification_name' => __('Certification Name'),
            'federation.country.name' => __('Country'),
            'current_term_starts_at' => __('Issue Date'),
            'federation.member_code' => __('Organization Code'),
            'national_code' => __('National Code'),
        ];

        $missingFields = [];

        foreach ($requiredFields as $field => $data) {
            if (is_array($data)) {
                // Handle special cases like profile image
                if (! $data['value']) {
                    $missingFields[] = $data['label'];
                }
            } else {
                // Handle regular fields
                $value = data_get($certification, $field);
                if (empty($value)) {
                    $missingFields[] = $data;
                }
            }
        }

        if (! empty($missingFields)) {
            Log::debug('Missing required fields for certification card', [
                'certification_id' => $certification->id,
                'missing_fields' => $missingFields,
            ]);
            throw new CertificationCardGenerationException(
                __('Cannot generate certification card. Missing required fields'),
                $missingFields

            );
        }
    }
    private function addGrid(ImageInterface $image, int $spacing = 50): void
    {
        $color = 'cccccc'; // Light grey color for the grid

        // Draw vertical lines and labels
        for ($x = 0; $x < $image->width(); $x += $spacing) {
            $image->drawLine(function (LineFactory $line) use ($x, $color, $image) {
                $line->from($x, 0);
                $line->to($x, $image->height());
                $line->color($color);
                $line->width(1);
            });
            $this->addText($image, (string) $x, $x + 2, 2, 10, 'ff0000', true); // Red labels at the top
        }

        // Draw horizontal lines and labels
        for ($y = 0; $y < $image->height(); $y += $spacing) {
            $image->drawLine(function (LineFactory $line) use ($y, $color, $image) {
                $line->from(0, $y);
                $line->to($image->width(), $y);
                $line->color($color);
                $line->width(1);
            });
            $this->addText($image, (string) $y, 2, $y + 2, 10, 'ff0000', true); // Red labels on the left
        }
    }

    private function createBaseImage(): ImageInterface
    {
        // Adjust the size to the credit card dimensions in pixels
        return $this->imageManager->create(1039, 661); // 86.6mm x 53.88mm in pixels at 300 DPI
    }

    private function addProfileImage(
        ImageInterface $image,
        CertificationAttributed $certification
    ): void {

        try {
            $profile = null;
            $profileMedia = $certification->individual?->getFirstMedia('profile');

            if ($profileMedia) {
                try {
                    $disk = Storage::disk($profileMedia->disk);
                    $path = $profileMedia->getPathRelativeToRoot();

                    Log::info("Profile image disk: {$profileMedia->disk}, path: {$path}");

                    if ($disk->exists($path)) {
                        // Get content from storage (works for both local and cloud)
                        $imageContent = $disk->get($path);
                        if ($imageContent) {
                            $tempPath = tempnam(sys_get_temp_dir(), 'profile_');
                            file_put_contents($tempPath, $imageContent);
                            $profile = $this->imageManager->read($tempPath);
                            @unlink($tempPath); // Clean up temp file
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to process profile image from storage', [
                        'error' => $e->getMessage(),
                        'certification_id' => $certification->id,
                    ]);
                }
            }

            // If we don't have a valid profile image, use default
            if (! $profile) {
                $defaultImagePath = public_path('img/user_placeholder.png');
                if (! file_exists($defaultImagePath)) {
                    throw new RuntimeException('Default profile image not found');
                }
                $profile = $this->imageManager->read($defaultImagePath);
                Log::info('Using default profile image', [
                    'certification_id' => $certification->id,
                ]);
            }

            $profile->cover(195, 195);
            // Create a new image for the profile with border
            $profileWithBorder = $this->imageManager->create(200, 200);
            // Draw a rounded rectangle as the border
            $profileWithBorder->drawRectangle(0, 0, function (RectangleFactory $rectangle) {
                $rectangle->size(200, 200);
                $rectangle->background('#64748B');
            });

            // Place profile image on the bordered background
            $profileWithBorder->place($profile, 'center');
            $image->place($profileWithBorder, 'top-left', 50, 50);
        } catch (\Throwable $e) {
            Log::error('Critical error in profile image processing', [
                'error' => $e->getMessage(),
                'certification_id' => $certification->id,
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Failed to process profile image', 0, $e);
        }
    }

    private function addLogo(ImageInterface $image, CertificationAttributed $certification): void
    {
        $committee = $certification->certification?->committee;
        $isInternational = $committee?->isInternational() ?? false;

        try {
            if (! $isInternational) {
                $logoPath = $committee
                    ? public_path($committee->getLogoPath())
                    : public_path(config('branding.primary.logo_path', 'img/project-logo.svg'));

                if (! file_exists($logoPath)) {
                    Log::warning('No logo file found, skipping logo', [
                        'certification_id' => $certification->id,
                    ]);
                } else {
                    $logo = $this->imageManager->read($logoPath);
                    $logo->scaleDown(160);
                    $image->place($logo, 'bottom-left', 50, 50);
                }
            }

            if ($isInternational) {
                $secondaryLogoPath = public_path(config('branding.international.secondary_logo_path', 'img/international-logo.svg'));
                if (file_exists($secondaryLogoPath)) {
                    $secondaryLogo = $this->imageManager->read($secondaryLogoPath);
                    $secondaryLogo->scaleDown(120);
                    $image->place($secondaryLogo, 'top-right', 50, 50);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to add logo', [
                'error' => $e->getMessage(),
                'certification_id' => $certification->id,
            ]);
        }
    }

    private function addCmasColorUrl(ImageInterface $image, array $textParts, int $x, int $y, int $size, string $align = 'left'): void
    {
        $totalWidth = 175;

        $currentX = $x;
        if ($align === 'center') {
            $currentX -= $totalWidth / 2;
        } elseif ($align === 'right') {
            $currentX -= $totalWidth;
        }

        foreach ($textParts as $part) {
            $image->text($part['text'], (int) $currentX, $y, function (FontFactory $font) use ($size, $part) {
                $font->filename($this->fontPath);
                $font->size($size);
                $font->color($part['color']);
                $font->align('left');
                $font->valign('top');
            });
            $currentX += $part['width'];
        }
    }

    private function addCertificationDetails(ImageInterface $image, CertificationAttributed $certification): void
    {
        // Calculate diver name height and position
        $diverNameFontSize = 36;
        $diverNameHeight = (int) ($diverNameFontSize * 1.2);
        $diverNameY = 265 - $diverNameHeight;

        // Add diver's name
        $this->addText(
            $image,
            $certification->individual->full_name,
            275,
            $diverNameY,
            $diverNameFontSize,
            '64748B'
        );

        // Calculate available space for certification name
        $maxTitleHeight = $diverNameY; // Space between Y=50 and diver's name
        $titleFontSize = 48;
        $text = strtoupper($certification->certification_name);

        do {
            $titleHeight = $this->calculateTextHeight($text, 475, $titleFontSize);
            $titleFontSize = max(24, $titleFontSize - 2);
        } while ($titleHeight > $maxTitleHeight && $titleFontSize > 24);

        // Add certification name
        $certificationTitleY = $diverNameY - $titleHeight - 10; // 10px gap
        $this->addText(
            $image,
            $text,
            275,
            $certificationTitleY,
            $titleFontSize,
            '1b6cb3',
            true,
            475
        );
    }
    private function calculateTextHeight(string $text, int $maxWidth, int $fontSize): int
    {
        $approximateCharWidth = (int) ($fontSize / 2);
        $charsPerLine = (int) ($maxWidth / $approximateCharWidth);
        $lines = explode("\n", wordwrap($text, $charsPerLine, "\n"));

        return count($lines) * (int) ($fontSize * 1.2);
    }
    private function addText(ImageInterface $image, string $text, int $x, int $y, int $size, string $color, bool $bold = false, int $maxWidth = 0): int
    {
        $lineHeight = (int) ($size * 1.2);

        if ($maxWidth > 0) {
            $approximateCharWidth = (int) ($size / 2);
            $charsPerLine = (int) ($maxWidth / $approximateCharWidth);
            $lines = explode("\n", wordwrap($text, $charsPerLine, "\n"));
        } else {
            $lines = [$text]; // Force single line for diver's name
        }
        try {
            foreach ($lines as $index => $line) {

                $image->text($line, $x, $y + ($index * $lineHeight), function (FontFactory $font) use ($size, $color, $bold) {
                    $font->filename($bold ? $this->fontBoldPath : $this->fontPath);
                    $font->size($size);
                    $font->color($color);
                    $font->align('left');
                    $font->valign('top');
                });
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to add text: $text", [
                'error' => $e->getMessage(),
            ]);
        }

        return count($lines) * $lineHeight;
    }
    private function addDivider(ImageInterface $image): void
    {
        $image->drawRectangle(50, 285, function ($rectangle) {
            $rectangle->background('1b6cb3');
            $rectangle->width(950);
            $rectangle->height(2);
        });
    }
    private function addQRCode(ImageInterface $image, CertificationAttributed $certification): void
    {
        $committee = $certification->certification?->committee;
        $isInternational = $committee?->isInternational() ?? false;

        if ($isInternational) {
            $this->addInternationalLogo($image, $certification);
        }
    }

    private function addInternationalLogo(ImageInterface $image, CertificationAttributed $certification): void
    {
        $entity = $certification->entity;
        $profileMedia = $entity?->getFirstMedia('profile');

        if (! $profileMedia) {
            return;
        }

        try {
            $disk = Storage::disk($profileMedia->disk);
            $path = $profileMedia->getPathRelativeToRoot();

            if (! $disk->exists($path)) {
                Log::warning('Entity profile image not found, skipping bottom-left logo', [
                    'certification_id' => $certification->id,
                    'entity_id' => $entity->id,
                ]);

                return;
            }

            $imageContent = $disk->get($path);
            if (! $imageContent) {
                return;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'entity_logo_');
            file_put_contents($tempPath, $imageContent);
            $logo = $this->imageManager->read($tempPath);
            @unlink($tempPath);

            $logo->scaleDown(width: 120);

            // Center logo in the 200px profile column with ~25px padding on each side
            // Vertical centering in text block area (y: 325-603) with min 25px padding
            $textAreaTop = 325;
            $textAreaBottom = 603;
            $textAreaCenter = $textAreaTop + ($textAreaBottom - $textAreaTop) / 2;
            $logoY = (int) max($textAreaTop + 25, $textAreaCenter - $logo->height() / 2);

            $image->place(
                $logo,
                'top-left',
                90,
                $logoY
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to add entity logo to bottom-left', [
                'error' => $e->getMessage(),
                'certification_id' => $certification->id,
            ]);
        }
    }

    private function addNationalQRCode(ImageInterface $image, CertificationAttributed $certification): void
    {
        $qrCodeUrl = $certification->individual->qrcode_path;

        // Convert URL to relative path if necessary
        $relativePath = str_replace(Storage::disk('public')->url('/'), '', $qrCodeUrl);
        $fullPath = Storage::disk('public')->path($relativePath);

        if (! file_exists($fullPath)) {
            Log::error("QR code file not found for certification {$certification->id}", [
                'url' => $qrCodeUrl,
                'relative_path' => $relativePath,
                'full_path' => $fullPath,
            ]);

            // Attempt to fetch the QR code content from the URL
            $qrContent = @file_get_contents($qrCodeUrl);
            if ($qrContent !== false && strlen($qrContent) > 0) {
                // Validate the fetched content is actually an image
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($qrContent);
                if (! str_starts_with($mimeType, 'image/')) {
                    Log::error("QR code URL returned non-image content for certification {$certification->id}", [
                        'mime_type' => $mimeType,
                        'url' => $qrCodeUrl,
                        'content_length' => strlen($qrContent),
                    ]);
                    throw new RuntimeException("QR code URL returned invalid content (mime: {$mimeType}) for certification {$certification->id}");
                }
                Storage::disk('public')->put($relativePath, $qrContent);
                Log::info('QR code fetched from URL and saved to disk');
            } else {
                throw new RuntimeException("QR code not found for certification {$certification->id}");
            }
        }

        try {
            $qr = $this->imageManager->read($fullPath);
            $qr->resize(195, 195);

            $qrWithBorder = $this->imageManager->create(200, 200);
            $qrWithBorder->drawRectangle(0, 0, function (RectangleFactory $rectangle) {
                $rectangle->size(200, 200);
                $rectangle->background('#64748B');
            });

            $qrWithBorder->place($qr, 'center');

            $image->text(
                $certification->individual->member_code,
                150,
                530,
                function (FontFactory $font) {
                    $font->filename($this->fontPath);
                    $font->size(24);
                    $font->color('64748B');
                    $font->align('center');
                    $font->valign('top');
                }
            );

            $image->place(
                $qrWithBorder,
                'bottom-left',
                50,
                135
            );
        } catch (\Throwable $e) {
            Log::error("Failed to process QR code for certification {$certification->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new RuntimeException("Failed to process QR code for certification {$certification->id}", 0, $e);
        }
    }

    private function addAdditionalInfo(
        ImageInterface $image,
        CertificationAttributed $certification
    ): void {

        $isInternational = $certification->certification?->committee?->isInternational() ?? false;

        $infoItems = [
            ...(! $isInternational ? [['label' => 'International N°', 'value' => $certification->international_code, 'labelWidth' => 130]] : []),
            ['label' => 'NATIONAL N°', 'value' => $certification->national_code, 'labelWidth' => 195],
            ['label' => 'COUNTRY', 'value' => $certification->federation->country->name, 'labelWidth' => 150],
            ['label' => 'ISSUE DATE', 'value' => Carbon::parse($certification->current_term_starts_at)->format('d/m/Y'), 'labelWidth' => 168],
            ...($certification->current_term_ends_at ? [['label' => 'EXPIRY DATE', 'value' => Carbon::parse($certification->current_term_ends_at)->format('d/m/Y'), 'labelWidth' => 195]] : []),
            ['label' => 'ORGANIZATION', 'value' => $certification->organizationDisplay(), 'labelWidth' => 220],
            ['label' => 'COURSE DIRECTOR', 'value' => $this->getCourseDirectorName($certification), 'labelWidth' => 270],
        ];

        if ($certification->entity) {
            $infoItems[] = ['label' => 'ESCOLA', 'value' => $certification->entity->name, 'labelWidth' => 130];
        }

        $labelX = 275;
        $fontSize = 28;
        $lineHeight = 50;
        $labelValueGap = 5;

        foreach ($infoItems as $index => $item) {
            $y = 325 + ($index * $lineHeight);

            // Add label
            $this->addText(
                $image,
                $item['label'] . ':',
                $labelX,
                $y,
                $fontSize,
                '000000',
                true // Use bold font for labels
            );

            $valueX = $labelX + $item['labelWidth'] + $labelValueGap;

            // Add value
            $this->addText(
                $image,
                $item['value'],
                $valueX,
                $y,
                $fontSize,
                '000000',
                false // Use regular font for values
            );
        }
    }

    private function getCourseDirectorName(CertificationAttributed $certification): string
    {
        $mainInstructor = $certification->mainInstructor->first();

        if (empty($mainInstructor)) {
            return __('National Technical Committee');
        }

        return trim($mainInstructor->name . ' ' . $mainInstructor->surname) ?: '-';
    }

    private function saveImage(ImageInterface $image, CertificationAttributed $certification): string
    {
        $fileName = "certification_card_{$certification->id}.png";
        $directory = 'certifications';
        $finalPath = "{$directory}/{$fileName}";

        try {
            // Skip temp directory and write directly to public storage
            if (! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Generate and save the image directly
            $encoder = new PngEncoder;
            $encodedImage = $image->encode($encoder);

            if (! Storage::disk('public')->put($finalPath, $encodedImage->toString())) {
                throw new RuntimeException('Failed to save certification card');
            }

            Log::info('Successfully saved certification card', [
                'certification_id' => $certification->id,
                'path' => $finalPath,
                'disk' => 'public',
            ]);

            return $finalPath;
        } catch (\Throwable $e) {
            Log::error("Failed to save certification card for certification {$certification->id}", [
                'error' => $e->getMessage(),
                'disk' => 'public',
                'path' => $finalPath,
            ]);
            throw new RuntimeException('Failed to save certification card: ' . $e->getMessage(), 0, $e);
        }
    }
}
