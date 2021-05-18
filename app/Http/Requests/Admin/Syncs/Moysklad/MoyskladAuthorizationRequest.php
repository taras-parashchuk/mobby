<?php

namespace App\Http\Requests\Admin\Syncs\Moysklad;

use Illuminate\Foundation\Http\FormRequest;
use GuzzleHttp;
use Illuminate\Validation\ValidationException;

class MoyskladAuthorizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $client = new GuzzleHttp\Client([
            'auth' => [$this->input('login'), $this->input('password')]
        ]);

        try {
            $client->get('https://online.moysklad.ru/api/remap/1.1');
        } catch (\Exception $exception){
            $code = $exception->getCode();
        }
        if ($code == 401) {

            throw ValidationException::withMessages([
                trans('auth.failed')
            ]);

        }

        if ($code == 404) {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'login' => 'required|max:50',
            'password' => 'required|max:50',
        ];
    }
}
