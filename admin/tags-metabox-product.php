<?php
class AdminTagProduct {

	static function addMetaBox(): void
    {
        Metabox::add('product_tag', 'Tags', 'AdminTagProduct::renderMetaBox', [
            'module' => 'products',
            'content_box' => 'price',
            'content' => 'right',
            'position' => 10
        ]);
	}

    static function renderMetaBox($object): void
    {
        $tags = [];

        if(have_posts($object)) {
            $tags = Tag::getsByObjectID($object->id);
        }

		Plugin::view('tags-manager', 'admin/metabox', [
			'tags' => $tags
		]);
    }

    static function save($id, $module): void
    {
        if($module == 'products') {

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
                Tag::insertRelationship($id, $listID, 'product');
            }
            else {
                Tag::empty($id, 'product');
            }
        }
    }
}

add_action('init', 'AdminTagProduct::addMetaBox');
add_action('save_object', 'AdminTagProduct::save', 10, 2);