<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>
        {{trans('mail.headers.received_fast_order', ['telephone' => $telephone])}}
    </title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div style="width: 680px;">
    <a href="{{$route_home}}" title="{{$store_name}}">
        <img src="{{$logo}}"
             alt="{{$store_name}}"
             style="margin-bottom: 20px; border: none;"/>
    </a>
    <p style="margin-top: 0px; margin-bottom: 20px;">{{trans('mail.fast_order.subtitle', ['telephone' => $telephone])}}</p>
    <table style="border-collapse: collapse; width: 100%; max-width: 600px; margin-top: 25px;">
        <thead>
        <tr>
            <td style="padding-bottom: 10px; font-weight: 700;" colspan="4">{{trans('mail.columns.products')}}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.name')}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.model')}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.quantity')}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.price')}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.price_total')}}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td style="border: 1px solid #000; padding: 5px;"><a href="{{$product->href}}">{{$product->name}}</a></td>
                <td style="border: 1px solid #000; padding: 5px;">{{$product->id}}</td>
                <td style="border: 1px solid #000; padding: 5px;">{{$product->quantity}}</td>
                <td style="border: 1px solid #000; padding: 5px;">{{$product->priceFormat}}</td>
                <td style="border: 1px solid #000; padding: 5px;">{{$product->totalFormat}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
