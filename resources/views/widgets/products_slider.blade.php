<owl-slider
        type="products"
        slider-name='slider-reccomends'
        slider-id="{!! $config->module_id !!}"
        :products='@json($products)'
        :config='@json($config)'>
</owl-slider>