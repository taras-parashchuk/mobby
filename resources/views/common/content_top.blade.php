@isset($modules)
    @if(count($modules))
        @foreach($modules as $module)
            @isset($module['data']['has_container'])
                <div class="container">
                    <div>
                        @include($module['view'], $module['data'])
                    </div>
                </div>
            @else
                <div class="contentCol">
                    <div>
                        @include($module['view'], $module['data'])
                    </div>
                </div>
            @endisset
        @endforeach
    @endif
@endisset