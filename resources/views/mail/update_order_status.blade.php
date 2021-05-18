<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>
        {{trans('mail.headers.update_order', ['order_id' => $order_id])}}
    </title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<p>{{trans('mail.update_order.your_order_status', ['status' => $status])}}</p>
@if($comment)
    <p>{{trans('mail.columns.comment')}}</p>
    <p>{{$comment}}</p>
@endif
</body>
</html>