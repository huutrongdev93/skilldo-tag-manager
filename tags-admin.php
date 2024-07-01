<?php
include_once 'admin/tags-metabox-product.php';
include_once 'admin/tags-metabox-post.php';

class AdminTagAjax {

    static function search(\SkillDo\Http\Request $request): void
    {

        $result['results'] = [];

        $query = $request->input('q');

        $args = Qr::set()->select('id', 'name', 'name_format')->limit(50);

        if(have_posts($query)) {

            $keyword = Arr::get($query, 'term');

            if(!empty($keyword)) {
                $keyword = trim(Str::lower($keyword));
                $args->where('name_format', 'like', '%'.$keyword.'%');
            }

            $tags = Tag::gets($args);

            foreach ($tags as $tag) {
                $result['results'][] = $tag;
            }
        }

        echo json_encode($result);
    }
}

Ajax::admin('AdminTagAjax::search');