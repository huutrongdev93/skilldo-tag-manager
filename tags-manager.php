<?php
/**
Plugin name     : Tags Manager
Plugin class    : tags_manager
Plugin uri      : http://sikido.vn
Description     : Ứng dụng quản lý tag, bạn có thể thêm xóa một hoặc nhiều tag cho sản phẩm và bài viết của bạn dễ dàng và thuận tiện nhất.
Author          : Nguyễn Hữu Trọng
Version         : 2.1.0
*/
const TAG_NAME = 'tags-manager';

define('TAG_PATH', Path::plugin(TAG_NAME));

class tags_manager {

    private $name = 'tags_manager';
    
    public function active() {
        $page_cart  = Pages::insert(['title' => 'Tag', 'content' => '']);
        $template  = [
            'page-tag.php'  => TAG_NAME.'/template/page-tag.php',
        ];
        foreach ($template as $file_name => $file_path) {
            $file_new  = Path::theme($file_name, true);
            $file_path = Path::plugin($file_path, true);
            if(file_exists($file_new)) continue;
            if(file_exists($file_path)) {
                $handle     = file_get_contents($file_path);
                $file_new   = fopen($file_new, "w");
                fwrite($file_new, $handle);
                fclose($file_new);
            }
        }
        tags_database_table_create();
    }

    public function uninstall() {
        tags_database_table_drop();
    }
}

include 'tags-helper.php';

if(Admin::is()) {
    include 'tags-database.php';
    include 'tags-admin.php';
}
else {
    if(Template::isPage('products_index')) {
        function product_filter_tag_input() {
            $tag = Request::get('tag');
            if (!empty($tag)) echo '<input type="text" name="tag" value="' . $tag . '">';
        }
        add_action('page_products_index_form_hidden', 'product_filter_tag_input');
    }
    include 'tags-template.php';
}

function product_filters_tag($args) {
    $tag = Request::get('tag');
    if(!empty($tag)) {
        $tag    = Tag::get(Qr::set('slug', $tag));
        $listID = [];
        if(have_posts($tag)) {
            $listID = Tag::getsObject($tag->id, 'product');
        }
        $args->whereIn('id', $listID);
    }
    return $args;
}
add_filter('controllers_product_index_args', 'product_filters_tag', 40);




