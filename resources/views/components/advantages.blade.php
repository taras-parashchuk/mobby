@if($shippings || $payments || $product->translate->warranty)
    <div class="productInfoRight__paymentdelivery">
        @if($shippings)
            <div class="infoPaymentDelivery">
                <div class="infoPaymentDelivery__title infoPaymentDelivery__title--delivery">
                    {{trans('pages.checkout.sections.shipping.heading')}}
                </div>
                <ul class="infoPaymentDelivery__list">
                    <?php foreach ($shippings as $shipping) { ?>
                    <li class="infoPaymentDelivery__item">
                        <span class="infoPaymentDelivery__text"><?php echo $shipping; ?></span>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        @endif
        @if($payments)
            <div class="infoPaymentDelivery">
                <div class="infoPaymentDelivery__title infoPaymentDelivery__title--payment">
                    {{trans('pages.checkout.sections.payment.heading')}}
                </div>
                <ul class="infoPaymentDelivery__list">
                    <?php foreach ($payments as $payment_name) { ?>
                    <li class="infoPaymentDelivery__item">
                        <span class="infoPaymentDelivery__text"><?php echo $payment_name; ?></span>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        @endif
        @if ($product->translate->warranty)
            <div class="infoPaymentDelivery">
                <div class="infoPaymentDelivery__title infoPaymentDelivery__title--warranty">
                    {{trans('common.text.warranty')}}
                </div>
                <span class="infoPaymentDelivery__text">{{$product->translate->warranty}}</span>
            </div>
        @endif
    </div>
@endif