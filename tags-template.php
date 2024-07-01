<?php
class ThemeTag {

    static function product($object): void
    {
        $tags = Tag::getsByObjectID($object->id, 'product');

        if(have_posts($tags)) {

            $tagStr = '';

            foreach ($tags as $tag) {
                $tagStr .= '<a href="'.Url::permalink('tag/san-pham/'.$tag->slug).'">'.$tag->name.'</a>';
            }

            Plugin::view('tags-manager', 'product', [
                'tagStr' => $tagStr
            ]);
        }
    }

    static function post($content) {

        if(Theme::isPost()) {

            $object = get_object_current();

            if(have_posts($object) && $object->post_type == 'post') {

                $tags   = Tag::getsByObjectID($object->id, 'post');

                if(have_posts($tags)) {

                    $tagStr = '';

                    foreach ($tags as $tag) {
                        $tagStr .= '<a href="'.Url::permalink('tag/bai-viet/'.$tag->slug).'">'.$tag->name.'</a>';
                    }

                    $content .= Plugin::partial('tags-manager', 'post', [
                        'tagStr' => $tagStr
                    ]);
                }
            }
        }

        return $content;
    }

    static function tagData(\SkillDo\Http\Request $request): void
    {
        $tag = $request->segment(3);

        $type = $request->segment(2);

        if(in_array($type, ['san-pham', 'bai-viet'])  === false) {

            $tag = $request->segment(4);

            $type = $request->segment(3);
        }

        if(!empty($tag)) {

            $langKey = Language::listKey();

            if(in_array($type, $langKey)) {

                $tag = $request->segment(4);

                $type = $request->segment(3);
            }
        }

        if(!empty($tag) && !empty($type)) {

            if($type == 'san-pham') {
                $type = 'product';
            }

            if($type == 'bai-viet') {
                $type = 'post';
            }

            $tag = Tag::where('slug', $tag)->first();

            $objects = [];

            if(have_posts($tag)) {

                $listID = Tag::getsObjectID($tag->id, $type);

                if($type == 'post') {
                    $objects = Posts::gets(Qr::set()->whereIn('id', $listID));
                }

                if($type == 'product') {
                    $objects = Product::gets(Qr::set()->whereIn('id', $listID));
                }
            }

            $page = Cms::getData('object');

            $page->title = 'Tag '.$tag->name ?? '';

            Cms::setData('tag', [
                'type'    => $type,
                'objects' => $objects,
            ]);
        }
    }

    static function renderProduct($objects): string
    {
        $column       = Option::get('category_row_count');
        $column       = ( $column != 5) ? 12/ $column : 15;

        $columnTablet = Option::get('category_row_count_tablet');
        $columnTablet = ( $columnTablet != 5) ? 12/$columnTablet : 15;

        $columnMobile = Option::get('category_row_count_mobile');
        $columnMobile = ( $columnMobile != 5) ? 12/$columnMobile : 15;

        $col = 'col-xs-'.$columnMobile.' col-sm-'.$columnTablet.' col-md-'.$column.' col-lg-'.$column;

        return Prd::partial( 'index/products', [
            'col'       => $col,
            'category'  => [],
            'objects'   => $objects
        ]);
    }
}

add_action('product_detail_info', 'ThemeTag::product', 50 );
add_filter('the_content', 'ThemeTag::post', 50 );
add_action('page_controllers_detail', 'ThemeTag::tagData');