@if($config['type'] === 'vertical')
    <menu-vertical is-home='{{$is_home}}' ref="menu" categories='@json($menu_groups_categories)'></menu-vertical>
@else
    <menu-horizontal ref="menu" :props-categories='@json($menu_categories)' :props-groups-categories='@json($menu_groups_categories)'></menu-horizontal>
@endif