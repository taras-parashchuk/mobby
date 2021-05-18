@isset($modules)
    @if(count($modules))
        @foreach($modules as $module)
            <div class="container">
                <div>
                    @include($module['view'], $module['data'])
                </div>
            </div>
        @endforeach
    @endif
@endisset