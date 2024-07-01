@php
    $tag = Cms::getData('tag');
@endphp
@if(have_posts($tag['objects']))
    @if($tag['type'] == 'post')
        <div class="post post-index">
            <div class="post-container post-container-{{ Theme::style('category', 'style') }}" style="--item-post-column:{{ Theme::style('category', 'numberShow') }};--item-post-column-xs:{{ Theme::style('category', 'numberShowTablet') }};--item-post-column-sm:{{ Theme::style('category', 'numberShowMobile') }}">
                @if(have_posts($tag['objects']))
                    @foreach ($tag['objects'] as $key => $val)
                        {!! Theme::partial('include/loop/item_post', ['val' => $val, 'layoutStyle' => Theme::style('category', 'style')]) !!}
                    @endforeach
                @endif
            </div>
        </div>
    @endif
    @if($tag['type'] == 'product')
        <form method="get" id="js_product_index_form__load">
            <div class="page-product-index">
                <div class="product-slider-horizontal" style="margin-top: 10px; position: relative; min-height: 200px">
                    {!! Admin::loading() !!}
                    {!! ThemeTag::renderProduct($tag['objects']) !!}
                </div>
            </div>
        </form>
    @endif
@endif