@isset($modules)
    @if(count($modules))
        <div class="columnLeft" id="columnLeft">
            @foreach($modules as $module)
                @include($module['view'], $module['data'])
            @endforeach
        </div>
    @endif
@endisset
