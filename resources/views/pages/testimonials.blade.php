@extends('layouts.standart')

@section('appData')
    @parent

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $('.js-review .form__ratingLabel').on('mouseover click', 'i.icon', function () {
                updateRating($(this));
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            $('#testimonial').delegate('.pagination a', 'click', function (e) {
                e.preventDefault();

                $('#testimonial').fadeOut('slow');

                $('#testimonial').load(this.href);

                $('#testimonial').fadeIn('slow');
            });
        });

    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('left-column') @endsection

@section('content')
    <div class="userTestimonial">
        <h1 class="pageTitle">
            {{trans('pages.testimonials.heading')}}
        </h1>
        <div class="userTestimonial__content">
            <div id="testimonial" class="userTestimonial__list">
                @foreach ($testimonials as $testimonial)
                    <div class="userTestimonial__single userTestimonialSingle">
                        <div class="userTestimonialSingle__top l-flex">
                            @if ($testimonial->date_added)
                                <span class="userTestimonialSingle__name">{{$testimonial->name}}</span>
                            @endif
                            @if($testimonial->name)
                                <span class="userTestimonialSingle__date">{{$testimonial->date_added}}</span>
                            @endif
                        </div>

                        <div class="l-flex">
                            <div class="userTestimonialSingle__rating rating">
                                <?php $i = 0;
                                while ($i < 5) {
                                if ($i < $testimonial->rating) { ?>
                                <i class="icon sb-icon-star rating--fill"></i>
                                <?php } else { ?>
                                <i class="icon sb-icon-star rating--null"></i>
                                <?php }
                                $i++;
                                } ?>
                            </div>
                        </div>

                        @if ($testimonial->text)
                            <div class="l-flex userTestimonialSingle__text">
                                <p>{{ $testimonial->text }}</p>
                            </div>
                        @endif

                    </div>

                @endforeach
            </div>
            <form action="{{route('testimonial.send')}}" method="POST"
                  class="form js-review form--message testimonialInfo__form js-form js-form-validate" id="testimonialForm">
                <div class="form__title">
                    {{trans('pages.testimonials.form-heading')}}
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold" for="contact__name">
                        {{trans('form.entry.rating')}}
                    </label>
                    <div class="rating">
                        <input class="form__rating" type="radio" name="rating" id="rating1"
                               value="1"/>
                        <label for="rating1" class="form__ratingLabel">
                            <i class="icon sb-icon-star rating--null"></i>
                        </label>

                        <input class="form__rating" type="radio" name="rating" id="rating2"
                               value="2"/>
                        <label for="rating2" class="form__ratingLabel">
                            <i class="icon sb-icon-star rating--null"></i>
                        </label>

                        <input class="form__rating" type="radio" name="rating" id="rating3"
                               value="3"/>
                        <label for="rating3" class="form__ratingLabel">
                            <i class="icon sb-icon-star rating--null"></i>
                        </label>

                        <input class="form__rating" type="radio" name="rating" id="rating4"
                               value="4"/>
                        <label for="rating4" class="form__ratingLabel">
                            <i class="icon sb-icon-star rating--null"></i>
                        </label>

                        <input class="form__rating" type="radio" name="rating" id="rating5"
                               value="5"/>
                        <label for="rating5" class="form__ratingLabel">
                            <i class="icon sb-icon-star rating--null"></i>
                        </label>
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold" for="contact__name">
                        {{trans('form.entry.customer-name')}}
                    </label>
                    <input type="text" name="firstname"
                           class="form__control js-control form__control--maxw js-field-validate js-input-name"
                           id="contact__name" value="{{auth()->user()->firstname ?? ''}}" data-field="name">
                    <div class="form__validation js-validation">
                        {{trans('validation.between.string', ['attribute' => trans('validation.attributes.firstname'), 'min' => 2, 'max' => 32])}}
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold"
                           for="contact__email">
                        {{trans('form.entry.customer-email')}}
                    </label>
                    <input type="text" name="email" value="{{auth()->user()->email ?? ''}}"
                           class="form__control js-control form__control--maxw js-field-validate js-input-email"
                           id="contact__email" data-field="email">
                    <div class="form__validation js-validation">
                        {{trans('validation.email')}}
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold"
                           for="contact__message">
                        {{trans('form.entry.message')}}
                    </label>
                    <textarea
                            class="form__control js-control form__control--maxw form__control--textarea
     form__control--textarea--message js-field-validate js-input-text"
                            name="review" id="contact__message" data-field="text" data-min="10"></textarea>
                    <div class="form__validation js-validation">
                        {{trans('validation.between.string', ['attribute' => trans('validation.attributes.message'), 'min' => 10, 'max' => 1000])}}
                    </div>
                </div>
                <div class="form__set form__set--left">
                    <input type="submit" class="form__btn form__btn--second btn btn--primary"
                           value="{{trans('form.button.send')}}"/>
                </div>
                <div class="form__result js-result"></div>
            </form>
        </div>
    </div>
@section('content-bottom')@endsection

@endsection