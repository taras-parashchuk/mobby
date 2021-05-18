@section('appData')
    @parent
    <style>
        @media screen and (min-width: 971px) {
        {{'#banner_'.$config->module_id}} .moduleBanner__content {
                grid-template-columns: <?php for ($i = 0; $i < $config->cols; $i++) {
    echo '1fr ';
} ?>;
                grid-gap: <?php echo (int)$config->offset; ?>px
            }
        }

        @media screen and (max-width: 970px) {
            {{'#banner_'.$config->module_id}} .moduleBanner__content {
                <?php if($config->cols >= 2){ ?>
                    grid-template-columns: 1fr 1fr;
                    grid-gap: <?php echo (int)$config->offset < 10 ?: 5; ?>px
                <?php } ?>


            }

        }

        @media screen and (max-width: 730px) {
            {{'#banner_'.$config->module_id}} .moduleBanner__content {
                display: block;
            }
        }
    </style>
@endsection

<list-slider-banners :slides='@json($slides)' :config='@json($config)'></list-slider-banners>