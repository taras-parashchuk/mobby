@isset($modules)
    @if(count($modules))
        @foreach($modules as $module)
            @include($module['view'], $module['data'])
        @endforeach
    @endif
@endisset
