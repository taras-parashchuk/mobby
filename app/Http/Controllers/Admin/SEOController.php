<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Language;
use App\Models\RedirectTarget;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SEOController extends Controller
{
    //
    use CustomValidation;

    public function index()
    {
        $home_page_info = json_decode(Setting::get('home_page'));

        if (!$home_page_info) {

            $home_page_info = collect();

            $home_page_info->translates = collect();

            foreach (Language::where('status', 1)->pluck('locale') as $locale) {

                $home_page_info->translates->push([
                    'description' => '',
                    'meta_title' => '',
                    'meta_description' => '',
                    'meta_keywords' => '',
                    'locale' => $locale
                ]);
            }

            $home_page_info = $home_page_info->translates;
        }

        $seo_templates = json_decode(Setting::get('seo_templates', '', 'seo'));

        if (!$seo_templates) {

            $seo_templates = [
                'status' => false,
                'templates' => [
                    'category' => [],
                    'subcategory' => [],
                    'product' => [],
                    'filter_in_category' => [],
                    'filter_in_subcategory' => [],
                    'mix_filter_in_category' => [],
                    'mix_filter_in_subcategory' => [],
                ]
            ];


            foreach ($seo_templates['templates'] as $template_name => $template) {

                if(!isset($template['translates'])) $template['translates'] = [];

                foreach (Language::getOnlyActive()->pluck('locale') as $locale) {

                    if ($template_name === 'product') {
                        $template['translates'][] = [
                            'meta_title' => '',
                            'meta_description' => '',
                            'h1' => '',
                            'locale' => $locale
                        ];
                    } else {
                        $template['translates'][] = [
                            'meta_title' => '',
                            'meta_description' => '',
                            'locale' => $locale
                        ];
                    }

                    $seo_templates['templates'][$template_name] = $template;

                }
            }

        }


        return [
            'pages' => [
                'home' =>
                    [
                        'translates' => $home_page_info
                    ]
            ],
            'script_tags' => [
                'head' => htmlspecialchars_decode(Setting::get('script_tags.head', '', 'html')),
                'start_body' => htmlspecialchars_decode(Setting::get('script_tags.start_body', '', 'html')),
                'end_body' => htmlspecialchars_decode(Setting::get('script_tags.end_body', '', 'html')),
            ],
            'seo_templates' => $seo_templates
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'pages.home.translates.*.locale' => ['exists:languages,locale'],
            'pages.home.translates.*.name' => ['nullable', 'string', 'max:200'],
            'pages.home.translates.*.meta_title' => ['nullable', 'string', 'max:200'],
            'pages.home.translates.*.meta_description' => ['nullable', 'string', 'max:200'],
            'pages.home.translates.*.meta_keywords' => ['nullable', 'string', 'max:250'],
        ], $this->messages());

        Setting::set('home_page', json_encode($request->input('pages.home.translates')));

        Setting::set('seo_templates', json_encode($request->input('seo_templates')), 'seo');

        Setting::set('script_tags.head', htmlspecialchars($request->input('script_tags.head')), 'html');
        Setting::set('script_tags.start_body', htmlspecialchars($request->input('script_tags.start_body')), 'html');
        Setting::set('script_tags.end_body', htmlspecialchars($request->input('script_tags.end_body')), 'html');

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }
}
