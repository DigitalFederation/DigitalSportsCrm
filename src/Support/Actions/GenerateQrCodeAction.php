<?php

namespace Support\Actions;

use Illuminate\Database\Eloquent\Model;
use Support\QrCodeGenerator;

class GenerateQrCodeAction
{
    public function execute(Model $model, string $codeField, string $pathField, string $directory): void
    {
        $code = $model->$codeField;
        $relativePath = QrCodeGenerator::generate($code, "qrcodes/$directory");
        $model->$pathField = $relativePath;
        $model->save();
    }

}
