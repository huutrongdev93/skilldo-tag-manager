<?php
include_once 'admin/tags-metabox-product.php';
include_once 'admin/tags-metabox-post.php';

function admin_ajax_tag_search( $ci, $modal ) {

    $result['results'] = [];

    $query = InputBuilder::Get('q');

    $args = ['limit' => 50, 'select' => 'id, name, name_format'];

    if(have_posts($query)) {

        $keyword = Arr::get($query, 'term');

        if(!empty($keyword)) {

            $keyword = trim(Str::lower($keyword));

            $args['where_like'] = [
                'name_format' 	=> [$keyword],
            ];
        }

        $tags = tag::gets($args);

        foreach ($tags as $tag) {
            $result['results'][] = $tag;
        }
    }

    echo json_encode($result);

    return true;
}
Ajax::admin('admin_ajax_tag_search');