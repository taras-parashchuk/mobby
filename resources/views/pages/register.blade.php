@extends('layouts.standart')

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('content')
    <div class="register">
        <h1 class="pageTitle">{{trans('account.text.heading.register')}}</h1>
        <div class="l-flex register__info">
            <form action="{{route('account.register.action')}}" method="POST"
                  class="form form--account js-form js-form-validate">
                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.firstname')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="text" name="firstname"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               data-field="name">
                        <div class="form__validation js-validation">{{$error_firstname}}</div>
                    </div>
                </div>

                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.lastname')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="text" name="lastname"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               data-field="name">
                        <div class="form__validation js-validation">{{$error_lastname}}</div>
                    </div>
                </div>

                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.login')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="text" name="login"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               data-field="login">
                        <div class="form__validation js-validation">{{$error_login}}</div>
                    </div>
                </div>

                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.password')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="password" name="password"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               id="password" data-field="password">
                        <div class="form__validation js-validation">{{$error_password}}</div>
                    </div>
                </div>

                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.confirm')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="password" name="password_confirmation"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               data-sameCheck="#password" data-field="same">
                        <div class="form__validation js-validation">{{$error_confirmed}}</div>
                    </div>
                </div>

                <div class="form__set form__set--inline form__set--newsletter">
                    <div class="form__group">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.newsletter.title')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <div class="radioNew">
                            <input type="checkbox" name="newsletter" class="radioNew__value" id="newsletter"
                                   value="1">
                            <label for="newsletter"
                                   class="radioNew__label register__newsletter radioNew__label--small">{{trans('account.entry.newsletter.text')}}</label>
                        </div>
                    </div>
                </div>

                <div class="form__set form__set--left">
                    <input type="submit" class="form__btn form__btn--account btn btn--primary"
                           value="{{trans('common.button.save')}}"/>
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

                @if(null)
                    <?php if($link_facebook_auth || $link_google_auth){ ?>
                    <div class="form__set form__set--left">
                        <div class="share share--inRegister">
                        <span class="share__text share__text--inRegister">
                            <?php echo $entry_social_register; ?>
                        </span>
                            <?php if($link_facebook_auth){ ?>
                            <a href="<?php echo $link_facebook_auth; ?>"
                               class="icon sb-icon-facebook-round share__link share__link--large">
                                <span class="path1"></span><span class="path2"></span><span
                                        class="path3"></span><span
                                        class="path4"></span>
                            </a>
                            <?php } ?>
                            <?php if($link_google_auth){ ?>
                            <a href="<?php echo $link_google_auth; ?>"
                               class="icon sb-icon-google-plus-round share__link share__link--large">
                                <span class="path1"></span><span class="path2"></span><span
                                        class="path3"></span><span
                                        class="path4"></span><span class="path5"></span>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                @endif

            </form>
        </div>
    </div>
@endsection