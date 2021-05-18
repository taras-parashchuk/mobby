<div class="l-d-none">
    <div id="callbackForm{{$postfix}}">
        <form action="{{route('message.send')}}" method="POST"
              class="form form--callback popup js-form js-popup-block js-form-validate">
            <div class="form__title form__title--medium">
                {{$title}}
            </div>
            <div class="form__set">
                <label class="form__label form__label--bold">
                    {{trans('form.entry.customer-name')}}
                </label>
                <input type="text" name="firstname"
                       value="{{auth()->user()->firstname ?? ''}}"
                       class="form__control js-control form__control--maxw js-field-validate"
                       data-field="name">
                <div class="form__validation js-validation">
                    {{trans('validation.between.string', ['attribute' => trans('validation.attributes.firstname'), 'min' => 2, 'max' => 32])}}
                </div>
            </div>
            @if ($show_all_form)
                <div class="form__set">
                    <label class="form__label form__label--bold">
                        {{trans('form.entry.customer-email')}}
                    </label>
                    <input type="text" name="email"
                           value="{{auth()->user()->email ?? ''}}"
                           class="form__control js-control form__control--maxw js-field-validate"
                           data-field="email">
                    <div class="form__validation js-validation">
                        {{trans('validation.email')}}
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold">
                        {{trans('form.entry.message')}}
                    </label>
                    <textarea name="text"
                              class="form__control js-control form__control--textarea form__control--textarea--message form__control--maxw js-field-validate"
                              data-field="text" data-min="10" data-max="1000"></textarea>
                    <div class="form__validation js-validation">
                        {{trans('validation.between.string', ['attribute' => trans('validation.attributes.message'), 'min' => 10, 'max' => 1000])}}
                    </div>
                </div>
            @else
                <div class="form__set">
                    <label class="form__label form__label--bold">
                        {{trans('form.entry.customer-telephone')}}
                    </label>
                    <input type="text" name="tel"
                           value="{{auth()->user()->telephone ?? ''}}"
                           class="form__control js-control form__control--maxw js-telMask js-field-validate"
                           data-field="tel">
                    <div class="form__validation js-validation">
                        {{trans('validation.custom.telephone.format')}}
                    </div>
                </div>

                @if ($call_time)
                    <div class="form__set">
                        <label class="form__label form__label--bold"><?php echo $text_time_call; ?></label>
                        <div class="form__choice choice">
                            <input type="radio" name="time" class="js-control choice__value" id="time_call1"
                                   value="1">
                            <label for="time_call1"
                                   class="choice__label choice__label--square"><?php echo $entry_time_now; ?></label>
                        </div>
                        <div class="form__choice choice">
                            <input type="radio" name="time" class="js-control choice__value" id="time_call2"
                                   value="2">
                            <label for="time_call2"
                                   class="choice__label choice__label--square"><?php echo $entry_time_5; ?></label>
                        </div>
                        <div class="form__choice choice">
                            <input type="radio" name="time" class="js-control choice__value" id="time_call3"
                                   value="3">
                            <label for="time_call3"
                                   class="choice__label choice__label--square"><?php echo $entry_time_15; ?></label>
                        </div>
                        <div class="form__choice choice">
                            <input type="radio" name="time" class="js-control choice__value" id="time_call4"
                                   value="4">
                            <label for="time_call4"
                                   class="choice__label choice__label--square"><?php echo $entry_time_30; ?></label>
                        </div>
                    </div>
                @endif

            @endif
            <div class="form__set form__set--left">
                <input type="hidden" name="type" value="{{$title}}">
                <input type="submit" class="form__btn form__btn--second btn btn--primary"
                       value="{{trans('form.button.send')}}"/>
            </div>

            <div class="loading js-loading">
                <div class="loading__wrap">
                <span class="icon icon--spin sb-icon-loading">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                            class="path4"></span><span class="path5"></span><span class="path6"></span><span
                            class="path7"></span><span class="path8"></span><span class="path9"></span><span
                            class="path10"></span><span class="path11"></span><span class="path12"></span>
                </span>
                </div>
            </div>

            <div class="form__result js-result"></div>
        </form>
    </div>
</div>