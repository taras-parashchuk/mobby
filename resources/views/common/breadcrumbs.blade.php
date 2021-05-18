<div class="breadcrumb breadcrumb--withMenu">

    <menu-vertical :is-home="{{(int)(Route::currentRouteName() === 'home')}}" ref="menu" categories='@json($menu_groups_categories)'></menu-vertical>

    <ul class="breadcrumbList">
        @if($breadcrumbs)
            @foreach ($breadcrumbs as $breadcrumb)
                @if($breadcrumb->url && !$loop->last)
                    <li>
                        <a href="{{$breadcrumb->url}}"
                           class="breadcrumbList__link">{{$breadcrumb->title}}</a>
                        <span class="breadcrumbList__delimiter">/</span>
                    </li>
                @else
                    <li>{{$breadcrumb->title}}</li>
                @endif
            @endforeach
        @endif
    </ul>
</div>