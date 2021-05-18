@if ($articles)
    <div class="module module--inContent">
        <div class="moduleNews js-module-news js-container-slider">
            <div class="moduleTitle moduleTitle--inContent">
                <span class="js-change-ln-text"
                      data-xs-text="{{$config['title-xs']}}">
                        {{$config['title-lg']}}
                </span>
                <div class="moduleNews__nav">
                    @if($config['has_more_articles'])
                        <a href=""
                           class="sliderTo sliderTo--inContent sliderTo--all js-change-ln-text"
                           data-xs-text="{{trans('common.button.more')}}">
                            {{trans('common.button.see_more')}}
                        </a>
                    @endif
                    <a class="sliderTo sliderTo--inContent sliderTo--left js-slideshow-nav" data-direction="prev">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                    </a>
                    <a class="sliderTo sliderTo--inContent sliderTo--right js-slideshow-nav" data-direction="next">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                    </a>
                </div>
            </div>
            <div class="moduleNews__content">
                <owl-slider type="articles" slider-name='slider-articles' slider-id="{!! $config['module_id'] !!}" :articles='@json($articles)' :config='@json($config)'></owl-slider>
            </div>
        </div>
    </div>
@endif
