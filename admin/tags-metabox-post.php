<?php
class AdminTagPost {

    static function addMetaBox(): void
    {
        Metabox::add('post_tag', 'Tags', 'AdminTagPost::renderMetaBox', [
            'module' => 'post_post',
            'content_box' => 'media',
            'content' => 'right',
            'position' => 10
        ]);
    }

    static function renderMetaBox($object): void
    {
        $tags = [];

        if(have_posts($object)) {
            $tags = Tag::getsByObjectID($object->id, 'post');
        }

        Plugin::view('tags-manager', 'admin/metabox', [
            'tags' => $tags
        ]);
    }

    static function save($id, $module): void
    {
        if($module == 'post') {

            $tags = request()->input('tags');

            $listID = [];

            if(have_posts($tags)) {

                foreach ($tags as $name) {

                    $tagInsert = [
                        'name_format' => trim(Str::lower($name))
                    ];

                    $tag = Tag::get(Qr::set()->whereRaw('name_format = CONVERT(\''.trim(Str::lower($name)).'\', BINARY)')->select('id', 'name'));

                    if(have_posts($tag)) {

                        $tagID = $tag->id;
                    }
                    else {

                        $tagInsert['name'] = trim($name);

                        $slug = Str::slug($tagInsert['name']);

                        $count = Tag::where('slug', $slug)->amount();

                        $i = 1;

                        while ($count > 0) {

                            $slug = $slug.'-'.$i;

                            $count = Tag::where('slug', $slug)->amount();

                            $i++;
                        }

                        $tagInsert['slug'] = $slug;

                        $tagID = Tag::insert($tagInsert);
                    }

                    if(!empty($tagID)) $listID[$tagID] = $tagID;
                }
            }

            if(have_posts($listID)) {
                Tag::insertRelationship($id, $listID, 'post');
            }
            else {
                Tag::empty($id, 'post');
            }
        }
    }
}

add_action('init', 'AdminTagPost::addMetaBox');
add_action('save_object', 'AdminTagPost::save', 10, 2);