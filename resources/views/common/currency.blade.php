@if (count($currencies) > 1)
    <div class="dropdownMenu">
        @foreach($currencies as $currency)
            @if ($currency['code'] == $user_currency)
                <span class="dropdownMenu__link js-dropdown-toggle">
                        {{$currency['name'], $currency['symbol']}}
                    </span>
            @endif
        @endforeach
        <div class="dropdownMenu__drop js-dropdown-content">
            @foreach ($currencies as $currency)
                <a href="{{route('change.currency', ['currency' => $currency['code']])}}"
                   class="dropdownMenu__link js-change-select {{ ($currency['code'] == $user_currency) ? 'dropdownMenu__link--active':''}}">
                    {{$currency['name'], $currency['symbol']}}
                </a>
            @endforeach
        </div>
    </div>
@endif

