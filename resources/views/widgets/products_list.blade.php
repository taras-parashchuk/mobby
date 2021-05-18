@if ($products)
    <div class="module module--inSideBar">
        <div class="moduleProducts">
            <div class="moduleTitle">
                {{$config['title']}}
            </div>
            <div class="moduleProducts__content">
                @foreach ($products as $product)
                    <div class="moduleProducts__item moduleProducts__item--row">
                        <div class="moduleProducts__img">
                            <a href="{{$product->href}}">
                                <img src="{{$product->thumb}}" alt="{{$product->translate->name}}">
                            </a>
                        </div>
                        <div class="moduleProducts__info moduleProducts__info--row">
                            <a href="{{$product->href}}"
                               class="moduleProducts__title">
                                {{$product->translate->name}}
                            </a>

                            <div class="moduleProducts__available available--{{(bool)$product->available ? 'true':'false'}}">
                                {{$product->stockTitle}}
                            </div> 

                            <div class="moduleProducts__price price">
                                @if($product['type'] === 1)
                                    @if ($product['special'])
                                        <span class="price__share">
                                        {{$product->specialFormat}}
                                    </span>
                                    @else
                                        <span class="price__default">
                                        {{ $product->priceFormat}}
                                    </span>
                                    @endif
                                @else
                                    <span class="price__default">
                                        {{ $product->pricesFormat}}
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
