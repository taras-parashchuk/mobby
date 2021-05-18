<?php

namespace App\Http\Controllers\Admin;

use App\Events\UpdateUserEvent;
use App\Rules\Telephone;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    //

    private $rules;

    public function __construct()
    {
        $this->rules = [
            'firstname' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],
            'email' => ['required_without:telephone', 'nullable', 'email', Rule::unique('users')],
            'group_id' => ['required', 'exists:user_groups,id'],
            'newsletter' => ['boolean'],
            'telephone' => ['required_without:email', 'nullable', new Telephone(), Rule::unique('users')],
            'password' => ['nullable', 'string', 'min:4', 'confirmed'],
            'is_admin' => ['required','boolean']
        ];
    }

    public function index(Request $request)
    {
        $users = User::orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
            ->paginate($request->input('perPage'));

        return compact('users');
    }

    public function store(Request $request)
    {
        $this->rules['password'][0] = 'required';

        if ($request->input('telephone')) {

            $request->merge(['telephone' => preg_replace("/[^0-9]/", '', $request->input('telephone'))]);

        }

        $request->validate($this->rules);

        $user = new User();

        $this->save($user, $request);

        event(new Registered($user));

        return response()->json([
            'id' => $user->id,
            'text' =>  trans('form.result.success-created')
        ]);

    }

    public function update(Request $request, $id)
    {
        $this->rules['email'][3] = $this->rules['email'][3]->ignore($id);
        $this->rules['telephone'][3] = $this->rules['telephone'][3]->ignore($id);

        if ($request->input('telephone')) {

            $request->merge(['telephone' => preg_replace("/[^0-9]/", '', $request->input('telephone'))]);

        }

        $user = User::findOrFail($id);

        $request->validate($this->rules);

        $this->save($user, $request);

        event(new UpdateUserEvent($user));

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        Validator::make(['id' => $id], [
            'id' => [Rule::notIn(\Auth::id())]
        ])->validate();


        User::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);

    }

    private function save(User &$user, Request $request)
    {
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->telephone = $request->input('telephone', '') ?: '';
        $user->email = $request->input('email', null);
        $user->newsletter = $request->input('newsletter');
        $user->group_id = $request->input('group_id');
        $user->is_admin = $request->input('is_admin');

        if($request->input('password')){
            $user->password = \Hash::make($request->input('password'));
        }

        $user->save();
    }
}
