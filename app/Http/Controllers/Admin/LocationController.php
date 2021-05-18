<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Models\Setting;
use App\Rules\Telephone;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    //
    private $rules;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'telephones.*' => ['nullable', new Telephone()],
            'emails.*' => ['nullable', 'email'],
            'translates.*.locale' => ['exists:languages'],
            'translates.*.name' => ['required', 'string', 'max:250'],
            'translates.*.address' => ['string', 'max:250'],
        ];
    }

    public function index(Request $request)
    {
        if($request->input('autocomplete')){
            $locations = Location::get()->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->translate->name
                ];
            });
        }else{
            $locations = Location::with('translates')->get();
        }

        return compact('locations');
    }

    public function edit($id)
    {
        $location = Location::with('translates')->findOrFail($id);

        $this->repairTranslates($location);

        return $location->toArray();
    }

    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages());

        $location = new Location();

        $translates = [];

        foreach ($request->input('translates') as $translate) {
            $translates[] = new LocationTranslation([
                'locale' => $translate['locale'],
                'name' => $translate['name'],
                'address' => $translate['address'],
            ]);
        }

        $location->save();

        $location->translates()->saveMany($translates);

        return response()->json([
            'id' => $location->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $location) {
            if (!$request->input('status') && $location->id === Setting::get('location')) {
                $fail(trans('validation.custom.inactive_main_location'));
            }
        }];

        $request->validate($this->rules, $this->messages());

        $translates = [];

        foreach ($request->input('translates') as $translate) {
            $translates[] = new LocationTranslation([
                'locale' => $translate['locale'],
                'name' => $translate['name'],
                'address' => $translate['address'],
                'schedule' => $translate['schedule'],
            ]);
        }

        $location->telephones = json_encode($request->input('telephones'));

        $location->emails = json_encode($request->input('emails'));

        $location->latitude = $request->input('latitude');

        $location->longitude = $request->input('longitude');

        $location->status = $request->input('status');

        $location->save();

        $location->translates()->delete();

        $location->translates()->saveMany($translates);

        return response()->json([
            'id' => $location->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        if($id == Setting::get('location')){
            abort(422, trans('validation.custom.inactive_main_location'));
        }else{
            Location::find($id)->delete();

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }

    private function repairTranslates(&$model)
    {
        foreach (config('settings.languages') as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new LocationTranslation([
                        'name' => '',
                        'address' => '',
                        'schedule' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
