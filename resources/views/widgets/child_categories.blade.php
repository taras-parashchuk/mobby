@if($child_categories->count())
    <div class="moduleList">
        <div class="moduleList__header">
            {{trans('common.text.category')}}
        </div>
        <ul class="moduleList__content list">
            @foreach ($child_categories as $category)
                <li class="list__item">
                    <div class="list__cell list__cell--line"></div>
                    <div class="list__cell">
                        <a href="{{$category->href}}"
                           class="list__link">{{$category->translate->name}}</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif