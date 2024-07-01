<?php
/**
Plugin name     : Tags Manager
Plugin class    : tags_manager
Plugin uri      : http://sikido.vn
Description     : Ứng dụng quản lý tag, bạn có thể thêm xóa một hoặc nhiều tag cho sản phẩm và bài viết của bạn dễ dàng và thuận tiện nhất.
Author          : Nguyễn Hữu Trọng
Version         : 2.2.0
*/
const TAG_NAME = 'tags-manager';

define('TAG_PATH', Path::plugin(TAG_NAME));

class tags_manager {

    private string $name = 'tags_manager';
    
    public function active(): void
    {
        Pages::insert(['title' => 'Tag', 'content' => '']);

        $store = Storage::disk('views');

        $templateViews  = [
            'page-tag.blade.php'  => 'plugins/'.TAG_NAME.'/template/page-tag.blade.php',
        ];

        foreach ($templateViews as $file_name => $file_path) {
            if($store->has(Theme::name().'/theme-child/'.$file_name)) {
                continue;
            }
            $store->copy($file_path, Theme::name().'/theme-child/'.$file_name);
        }

        $database = include 'database/database.php';

        $database->up();
    }

    public function uninstall(): void
    {

        $database = include 'database/database.php';

        $database->down();
    }
}

include 'tags-helper.php';

if(Admin::is()) {
    include 'tags-admin.php';
}
else {
    include 'tags-template.php';
}




