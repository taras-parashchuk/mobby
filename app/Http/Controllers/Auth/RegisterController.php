<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\HelperFunction;
use App\Models\Setting;
use App\Rules\Login;
use App\User;
use App\Http\Controllers\Controller;
use Facebook\Facebook;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectTo = route('home');

        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('pages.register', [
            'error_firstname' => trans('validation.between.string', ['attribute' => trans('validation.attributes.firstname'), 'min' => 2, 'max' => 32]),
            'error_lastname' => trans('validation.between.string', ['attribute' => trans('validation.attributes.lastname'), 'min' => 2, 'max' => 64]),
            'error_login' => trans('validation.custom.login.format'),
            'error_password' => trans('validation.min.string', ['attribute' => trans('validation.attributes.password'), 'min' => 4]),
            'error_confirmed' => trans('validation.confirmed', ['attribute' => trans('validation.attributes.password')]),

        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => ['bail','required', 'string', 'between:2,32'],
            'lastname' => ['bail','required', 'string', 'between:2,64'],
            'login' =>    ['bail','required', 'string', 'unique:users,email', 'unique:users,telephone', new Login()],
            'password' => ['bail','required', 'string', 'min:4', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $email = '';
        $telephone = '';

        if(filter_var($data['login'], FILTER_VALIDATE_EMAIL)){
            $email = $data['login'];
        }else{
            $telephone = $data['login'];
        }

        return User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $email,
            'telephone' => preg_replace('#\D#','', $telephone),
            'password' => Hash::make($data['password']),
            'group_id' => Setting::get('user_group_after_register', null)
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);


        return $this->registered($request, $user)
            ?: response()->json([
                'redirect' => $this->redirectPath()
            ]);
    }

    public function redirectPath()
    {
        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
