@extends('layouts.standart')

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('content')

    <div class="reset">
        <h1 class="pageTitle">{{trans('account.text.heading.reset')}}</h1>
        <div class="l-flex register__info">
            <form action="{{route('password.update')}}" method="POST"
                  class="form form--account js-form js-form-validate" id="reset_form">

                <div class="form__set form__set--inline">
                    <div class="form__group form__group--left">
                        <label for=""
                               class="form__label form__label--inline form__label--bold">{{trans('account.entry.email')}}</label>
                    </div>
                    <div class="form__group form__group--inBig">
                        <input type="email" name="email"
                               class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                               data-field="email">
                        <div class="form__validation js-validation">{{ $error_email }}</div>
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
                        <div class="form__validation js-validation">{{ $error_password }}</div>
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
                        <div class="form__validation js-validation">{{ $error_confirmed }}</div>
                    </div>
                </div>

                <input type="hidden" name="token" value="{{$token}}">

                <div class="form__set form__set--left" id="reset_loading">
                    <input type="submit" class="form__btn form__btn--account btn btn--primary"
                           value="{{trans('common.button.save')}}"/>
                </div>

                <div class="form__result js-result"></div>
            </form>
        </div>
    </div>

@endsection