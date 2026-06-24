# Third-Party Notices

This project includes third-party open-source assets and published runtime assets. Their license files are retained where the assets are stored when available.

## In-Tree Assets

| Component | Location | License |
| --- | --- | --- |
| Bootstrap Icons | `public/assets/icons/` | MIT, see `public/assets/icons/LICENSE` |
| Flag Icons | `public/img/flags/` | MIT, see `public/img/flags/LICENSE` |
| TinyMCE | `public/tinymce/`, `resources/vendor/tinymce/` | MIT, see `public/tinymce/license.txt` and `resources/vendor/tinymce/license.txt` |
| CKEditor 5 build assets | `resources/vendor/ckeditor5/` | MIT for included builder/sample code, with CKEditor trademark notice, see `resources/vendor/ckeditor5/LICENSE.md` |

## Published Runtime Assets

`public/css/filament/` and `public/js/filament/` contain assets published from Filament packages for application runtime use. Generated Vite output (`public/build/`) and generated Dompdf font cache files (`storage/fonts/`) are intentionally ignored and should be produced during build or runtime setup.

## Dependency Licenses

Composer and npm dependencies are listed in `composer.lock` and `package.json`. Run dependency license tooling before publishing a release artifact if you need a complete bill of materials.
