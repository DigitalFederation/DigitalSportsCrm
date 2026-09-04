<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use League\CommonMark\CommonMarkConverter;

class VersionController extends Controller
{
    public function index()
    {
        $version = config('app.version');
        $changelog = file_get_contents(base_path('CHANGELOG.md'));
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $htmlChangelog = $converter->convert($changelog);

        return view('web.common.versions.index', compact('version', 'htmlChangelog'));
    }

}
