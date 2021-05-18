@if (count($languages) > 1)
    <div class="dropdownMenu">
        @foreach ($languages as $language)
            @if ($language['locale'] == $current_locale)
                <span class="dropdownMenu__link js-dropdown-toggle">
                        {{$language['name']}}
                    </span>
            @endif
        @endforeach
        <div class="dropdownMenu__drop js-dropdown-content">
            @foreach ($languages as $language)
                <a href="{{route('setlocale', ['locale' => $language['locale']])}}"
                   class="js-change-language dropdownMenu__link {{ ($language['locale'] == $current_locale) ?
                   'dropdownMenu__link--active' : '' }}">
                    {{$language['name']}}
                </a>
            @endforeach
        </div>
    </div>
@endif
