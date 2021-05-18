<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{trans('mail.headers.new_callback', ['type' => $callback->type])}}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div>
    <p>
        <span>{{trans('mail.columns.customer_name')}}</span>
        <span>{{$callback->name}}</span>
    </p>
</div>
@if($callback->text)
    <div>
        <p>
            <span>{{trans('mail.columns.customer_email')}}</span>
            <span>{{$callback->email}}</span>
        </p>
    </div>
    <div>
        <p>
            <span>{{trans('mail.callback.customer_message')}}</span>
            <span>{{$callback->text}}</span>
        </p>
    </div>
@else
    <div>
        <p>
            <span>{{trans('mail.columns.customer_telephone')}}</span>
            <span>{{$callback->telephone}}</span>
        </p>
    </div>

    @if (null) { ?>
    <div>
        <p>
            <span>{{trans('mail.callback.prefer_callback_time')}}</span>
        </p>
    </div>
    @endif
@endif
</body>
</html>