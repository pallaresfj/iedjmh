<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function __invoke(string $section): View
    {
        $sectionConfig = config("institution.sections.{$section}");

        abort_unless(is_array($sectionConfig), Response::HTTP_NOT_FOUND);

        return view('public.section', [
            'sectionKey' => $section,
            'title' => $sectionConfig['title'],
            'description' => $sectionConfig['description'] ?? null,
        ]);
    }
}
