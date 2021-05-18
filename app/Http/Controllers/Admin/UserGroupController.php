<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Language;
use App\Models\Setting;
use App\Models\UserGroup;
use App\Models\UserGroupTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserGroupController extends Controller
{
    private $rules;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'between:3,100'],
            'translates.*.summary' => ['nullable', 'max:220', 'string'],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
        ];
    }

    //
    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {
            $user_groups = UserGroup::with('translates')->enabled()->get();

            $user_groups->each->append('translate');

            $user_groups = $user_groups->map(function ($user_group) {

                return [
                    'id' => $user_group->id,
                    'name' => $user_group->translate->name
                ];
            });
        } else {
            $user_groups = UserGroup::with('translates')
                ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));

            foreach ($user_groups as $user_group) {
                $this->repairTranslates($user_group);
            }
        }

        return compact('user_groups');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages());

        $user_group = new UserGroup();

        $this->save($user_group, $request);

        return response()->json([
            'id' => $user_group->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $user_group = UserGroup::findOrFail($id);

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $user_group) {
            if (!$request->input('status') && ($user_group->id === Setting::get('user_group_after_register') || $user_group->id === Setting::get('user_group_before_register'))) {
                $fail(trans('validation.custom.inactive_main_user_group_id'));
            }
        }];

        $request->validate($this->rules, $this->messages());

        $this->save($user_group, $request);

        return response()->json([
            'id' => $user_group->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user_group = UserGroup::findOrFail($id);

        if($user_group->id === Setting::get('user_group_after_register') || $user_group->id === Setting::get('user_group_before_register')){
            abort('403', trans('validation.custom.inactive_main_user_group_id'));
        }elseif ($user_group->locked){
            abort('403', trans('validation.custom.remove_system_user_group_id'));
        }else{
            $user_group->delete();

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }

    private function save(UserGroup $userGroup, Request $request)
    {
        $userGroup->sort_order = $request->input('sort_order', 0) ?? 0;

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new UserGroupTranslation(
                [
                    'name' => $translate['name'],
                    'summary' => $translate['summary'] ?? null,
                    'locale' => $translate['locale']
                ]
            );
        }

        $userGroup->status = $request->input('status');

        $userGroup->save();

        $userGroup->translates()->delete();

        $userGroup->translates()->saveMany($translations);
    }

    private function repairTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new UserGroupTranslation([
                        'name' => '',
                        'summary' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
