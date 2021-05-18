<form action="{{ route('account.forgotten') }}" novalidate method="POST"
      class="form form--login form--cLogin js-form-forgotten js-form js-form-validate"
      style="display: none;">
    <div class="form__title">
        {{ trans('account.text.heading.login')}}
    </div>
    <p class="form__description">{{ trans('account.text.forgotten_desc')}}</p>
    <div class="form__set">
        <label class="form__label form__label--bold">{{ trans('account.entry.email')}}</label>
        <input type="email" name="email"
               class="form__control js-control form__control--maxw js-field-validate"
               data-field="email">
        <div class="form__validation js-validation">{{ $error_login}}</div>
    </div>
    <div class="form__set form__set--buttons">
        <input type="submit" class="btn btn--login btn--primary"
               value="{{ trans('account.button.forgotten') }}"/>
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