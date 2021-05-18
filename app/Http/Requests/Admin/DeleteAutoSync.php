<?php

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;


class DeleteAutoSync extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \App\Models\Sync::find($this->route('auto_sync'), ['id']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException(trans('validation.custom.have_not_access'));
    }
}
