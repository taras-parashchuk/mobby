<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Rules\Telephone;

class MessageController extends Controller
{
    //
    public function save(Request $request)
    {

        if($request->input('tel')){
            $request->merge(['tel' => preg_replace("/[^0-9]/", '', $request->input('tel'))]);
        }

        $request->validate([
            'firstname' => ['bail', 'required', 'string', 'min:2', 'max:64'],
            'email' => ['nullable', 'email'],
            'text' => ['nullable', 'bail', 'string', 'min:10', 'max:1000'],
            'tel' => ['nullable', 'bail', 'string', new Telephone()],
            'type' => ['required']
        ]);

        $message = new Message;

        $message->user_id = \Auth::id();
        $message->name = $request->input('firstname', '');
        $message->email = $request->input('email', '');
        $message->text = $request->input('text', '');
        $message->telephone = $request->input('tel', '');
        $message->type = $request->input('type');

        $message->save();

        return response()->json([
            'success' => trans('form.result.success-sent'),
        ]);

    }
}
