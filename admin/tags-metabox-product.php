<?php
Metabox::add('product_tag', 'Tags', 'admin_metabox_product_tag', [
    'module' => 'products',
    'content_box' => 'price',
    'content' => 'right',
    'position' => 10
]);

function admin_metabox_product_tag( $object ) {
    $tags = [];
    if(have_posts($object)) {
        $tagsID = Tag::getsByObjectID($object->id, 'product');
        $tags   = Tag::gets(Qr::set()->whereIn('id', $tagsID));
    }
    ?>
    <div class="col-md-12 row m-1" id="box_tags">
        <div class="form-group group">
            <select name="tags[]" class="form-control select2" multiple="multiple">
                <?php
                if(have_posts($tags)) {
                    foreach ($tags as $tag) {
                        echo '<option value="'.$tag->name.'" selected="selected">'.$tag->name.'</option>';
                    }
                }
                ?>
            </select>
            <p style="color:#999;margin:5px 0 5px 0;">Dùng "," hoặc "tab" để tạo tag mới khi không có kết quả tìm kiếm</p>
        </div>
    </div>
    <script defer>
        $(function () {
            $(".select2").select2({
                tags: true,
                tokenSeparators: [',', '\n'],
                minimumInputLength: 1,
                ajax: {
                    url: ajax+'?action=admin_ajax_tag_search',
                    dataType: "json",
                    delay: 500,
                    data: function(term, page) {
                        return {
                            q: term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: $.map(data.results, function (item) {
                                return {
                                    text: item.name,
                                    id: item.name,
                                    data: item
                                };
                            })
                        };
                    }
                }
            })
        });
    </script>
    <?php
}

function admin_product_tag_save($id, $module) {

    if($module == 'products') {

        $tags = Request::Post('tags');

        $listID = [];

        if(have_posts($tags)) {

            foreach ($tags as $name) {

                $tagID = 0;

                $tag_insert = [
                    'name_format' => trim(Str::lower($name))
                ];

                $tag = tag::get(Qr::set('name_format', 'CONVERT(\''.trim(Str::lower($name)).'\', BINARY)')->select('id', 'name'));

                if(have_posts($tag)) {

                    $tagID = $tag->id;
                }
                else {

                    $tag_insert['name'] = trim($name);

                    $tagID = tag::insert($tag_insert);
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

add_action('save_object', 'admin_product_tag_save', 10, 2);