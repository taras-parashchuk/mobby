<div id="formTopLogin">
    <form action="{{route('account.login')}}" method="POST"
          class="form form--login form--cLogin js-form js-form-validate">
        <div class="form__title">
            {{trans('account.text.heading.login')}}
        </div>
        <div class="form__set">
            <label class="form__label form__label--bold">{{ trans('account.entry.login')}}</label>
            <input type="text" name="login"
                   class="form__control js-control form__control--maxw js-field-validate js-input-validate"
                   data-field="login">
            <div class="form__validation js-validation">{{ $error_login }}</div>
        </div>
        <div class="form__set">
            <label class="form__label form__label--bold">{{ trans('account.entry.password')}}</label>
            <input type="password" name="password"
                   class="form__control js-control form__control--maxw js-field-validate"
                   data-field="password">
            <a href="javascript:void(0)"
               class="form__help text-link js-login-open-forgotten">{{ trans('account.text.forgotten')}}</a>
            <div class="form__validation js-validation">{{ $error_password}}</div>
        </div>
        <div class="form__set form__set--buttons">
            <input type="submit" class="btn btn--login btn--primary"
                   value="{{ trans('account.button.login')}}"/>

            @if($oauth)
                <div class="share share--inLogin">
                <span class="share__text share__text--inLogin">
                    {{ trans('account.text.login_as_user')}}:
                </span>

                    <a href="{{ url('auth/facebook') }}"
                       class="icon sb-icon-facebook-round share__link share__link--large">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                class="path4"></span>
                    </a>

                    <a href="{{ url('auth/google')}}"
                       class="icon sb-icon-google-plus-round share__link share__link--large">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                class="path4"></span><span class="path5"></span>
                    </a>

                </div>
            @endif

            <div>{{ trans('account.text.or')}}</div>
            <a href="{{ route('account.register')  }}"
               class="btn btn--login btn--second">{{ trans('account.text.heading.register')}}</a>
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
    @include('form.forgotten')
</div>
