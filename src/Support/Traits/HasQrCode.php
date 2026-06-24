<?php

namespace Support\Traits;

use Support\Actions\GenerateQrCodeAction;

trait HasQrCode
{
    public function generateQrCode(): void
    {
        $action = app(GenerateQrCodeAction::class);
        $action->execute(
            $this,
            $this->qrCodeSourceField(),
            $this->qrCodePathField(),
            $this->qrCodeDirectory()
        );
    }

    abstract public function qrCodeSourceField(): string;
    abstract public function qrCodePathField(): string;
    abstract public function qrCodeDirectory(): string;
}
