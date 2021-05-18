<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>
        {{trans('mail.headers.new_order')}}
    </title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<table style="border-collapse: collapse; width: 100%; max-width: 600px;">
    <thead>
    <tr>
        <td style="font-weight: 700; padding-bottom: 10px;" colspan="4">
            {{trans('mail.headers.new_order')}}
        </td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.order_date_added')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{$date_added}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.payment_method')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{$payment_method}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.shipping_method')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{ $shipping_method }}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.address')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{$address}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.user_contacts')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{$user_info}}</td>
    </tr>
    @if($comment)
        <tr>
            <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.comment')}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{$comment}}</td>
        </tr>
    @endif
    </tbody>
</table>
<table style="border-collapse: collapse; width: 100%; max-width: 600px; margin-top: 25px;">
    <thead>
    <tr>
        <td style="padding-bottom: 10px; font-weight: 700;" colspan="4">{{trans('mail.columns.products')}}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.name')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.model')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.quantity')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.specification')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.price')}}</td>
        <td style="border: 1px solid #000; padding: 5px;">{{trans('mail.columns.price_total')}}</td>
    </tr>
    </thead>
    <tbody>
    @foreach ($products as $product)
        <tr>
            <td style="border: 1px solid #000; padding: 5px;">
                {{$product->name}}
            </td>
            <td style="border: 1px solid #000; padding: 5px;">{{$product->id}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{$product->quantity}}</td>
            <td style="border: 1px solid #000; padding: 5px;">
                @foreach($product->specification as $specification_name => $specification_value)
                    <div>
                        {{$specification_name}} - {{$specification_value}}
                    </div>
                @endforeach
            </td>
            <td style="border: 1px solid #000; padding: 5px;">{{$product->price}}</td>
            <td style="border: 1px solid #000; padding: 5px;">{{$product->price_total}}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>

    @foreach ($totals as $total)
        <tr style="text-align: right; font-weight: 700;">
            <td style="padding-top: 8px;" colspan="5">
                {{$total->name}}
                {{$total->value}}
            </td>
        </tr>
    @endforeach
    </tfoot>
</table>
</body>
</html>
