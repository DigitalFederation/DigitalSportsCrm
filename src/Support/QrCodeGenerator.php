<?php

namespace Support;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeGenerator
{
    /**
     * Generate a QR code and return the file path.
     */
    public static function generate(string $code, string $path): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd
        );
        $writer = new Writer($renderer);

        // Generate QR code content
        $qrCodeContent = $writer->writeString($code);

        // Define the file path within the 'storage/app/public' directory
        $filePath = $path . '/' . $code . '.png';

        // Store the QR code content in the file
        Storage::disk('public')->put($filePath, $qrCodeContent);

        // Return the URL to access the QR code publicly
        return Storage::disk('public')->url($filePath);
    }
}
