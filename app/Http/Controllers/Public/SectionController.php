<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SectionController extends Controller
{
    use ResolvesPublicContent;

    public function __invoke(string $section): View
    {
        $sectionConfig = config("institution.sections.{$section}");
        $page = $this->publishedPageBySlug($section);

        abort_unless(is_array($sectionConfig), Response::HTTP_NOT_FOUND);

        return view('public.section', [
            'sectionKey' => $section,
            'title' => $page?->title ?: $sectionConfig['title'],
            'description' => $page?->summary ?: ($sectionConfig['description'] ?? null),
            'banner' => $this->resolvePageBanner($page),
        ]);
    }
}
