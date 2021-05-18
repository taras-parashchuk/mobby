@if($slides)
    <owl-slider type="slider-default" slider-name='slider-default' slider-id="{!! $config->module_id !!}" :slides='@json($slides)' :config='@json($config)'></owl-slider>
@endif