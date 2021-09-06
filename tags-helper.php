<?php
Class Tag {
    static public function get($args = []) {

        if(is_numeric($args)) {
            $cache_id = 'tags_'.md5($args);
            if(Language::default() != Language::current()) {
                $cache_id .= '_'.Language::current();
            }
            if(CacheHandler::has($cache_id) !== false) return apply_filters('get_tags', CacheHandler::get($cache_id));
            $args = ['where' => array('id' => (int)$args)];
        }

        if(!empty($args['where']['slug']) && count($args['where']) == 1 && count($args) <= 2) {
            $cache_slug     = 'tags_'.md5($args['where']['slug']);
            $params_temp    = [];
            if(!empty($args['params'])) $params_temp = $args['params'];
            $cache_slug .= '_'.md5(serialize($params_temp));
            if(Language::default() != Language::current()) {
                $cache_slug .= '_'.Language::current();
            }
            if(CacheHandler::has($cache_slug) !== false) return apply_filters('get_tags', CacheHandler::get($cache_slug));
        }

        $args = array_merge(['where' => [], 'params' => []], (is_array($args)) ? $args : [] );

        $model 	= get_model()->settable('tags')->settable_metabox('metabox');

        $tags = $model->get_data($args);

        if(have_posts($tags)) {
            $cache_id = 'tags_'.md5($tags->id);
            if(Language::default() != Language::current()) {
                $cache_id .= '_'.Language::current();
            }
            CacheHandler::save($cache_id, $tags);
            if(!empty($cache_slug)) CacheHandler::save($cache_slug, $tags);
        }

        return apply_filters('get_tags', $tags);
    }
    static public function gets($args = []) {
        if(!have_posts($args)) $args = [];
        if(is_numeric($args))  $args = ['where' => ['id' => (int)$args]];
        $args = array_merge(['where' => [], 'params' => []], $args );
        $model = get_model()->settable('tags')->settable_metabox('metabox');
        $tags = $model->gets_data($args);
        return apply_filters( 'gets_tags', $tags, $args );
    }
    static public function getsByObjectID($id, $type = 'product') {
        $model = get_model()->settable('tags_relationships');
        $temp  = $model->gets_where(['object_id' => $id, 'object_type' => $type], ['select' => 'object_id, tag_id']);
        $listID = [];
        foreach ($temp as $item) {
            $listID[] = $item->tag_id;
        }
        return $listID;
    }
    static public function getsObject($id, $type = 'product') {
        $model = get_model()->settable('tags_relationships');
        $temp  = $model->gets_where(['object_type' => $type, 'tag_id' => $id], ['select' => 'object_id']);
        $listID = [];
        foreach ($temp as $item) {
            $listID[] = $item->object_id;
        }
        return $listID;
    }
    static public function count($args = []) {
        if(is_numeric($args))  $args = ['where' => ['id' => (int)$args]];
        if(!have_posts($args)) $args = array();
        $args = array_merge( array('where' => array(), 'params' => array() ), $args );
        $model 	= get_model()->settable('tags')->settable_metabox('metabox');
        $tags = $model->count_data($args);
        $model->settable('tags');
        return apply_filters( 'count_tags', $tags, $args );
    }
    static public function insert($tags = []) {

        $model = get_model()->settable('tags');

        if(!empty($tags['id'])) {

            $id         = (int) $tags['id'];

            $update     = true;

            $old_tags   = static::get($id);

            if (!$old_tags) return new SKD_Error('invalid_tags_id', __('ID Tag không chính xác.'));

            if(empty($tags['name'])) $tags['name'] = $old_tags->name;
        }
        else {

            if(empty($tags['name'])) return new SKD_Error('empty_tags_title', __('Không thể cập nhật trang khi tiêu đề trống.', 'empty_tags_title') );

            $update = false;
        }

        $name            =  trim(Str::clear($tags['name']));

        $name_format     =  Str::lower($name);

        $slug            =  Str::slug($name);

        if ($update) {
            $args_count = ['where' => ['slug' => $slug, 'id <>' => $id]];
        }
        else {
            $args_count = ['where' => ['slug' => $slug]];
        }

        $count = static::count($args_count);
        $i = 1;
        while ($count > 0) {
            $slug = $slug.'-'.$i;
            $args_count['where']['slug'] = $slug;
            $count = static::count($args_count);
            $i++;
        }

        $data = compact( 'name', 'name_format', 'slug');

        $data = apply_filters( 'pre_insert_tags_data', $data, $tags, $update ? $old_tags : null);

        if ($update) {

            $model->settable('tags')->update_where( $data, compact( 'id' ) );

            $tags_id = (int) $id;
        }
        else {

            $model->settable('tags');

            $tags_id = $model->add( $data );
        }

        $model->settable('tags');

        $tags_id  = apply_filters( 'after_insert_tags', $tags_id, $tags, $data, $update ? $old_tags : null  );

        return $tags_id;
    }
    static public function delete($id = 0) {

        $id = (int)Str::clear($id);

        if($id == 0) return false;

        $model = get_model()->settable('tags');

        $tag   = static::get($id);

        if(have_posts($tag)) {

            do_action('delete_tags', $tag );

            if($model->delete_where(['id'=> $tag])) {

                do_action('delete_tags_success', $id );

                Metadata::deleteByMid('tags', $id);

                //delete menu
                $model->settable('tags_relationships')->delete_where(['tag_id'=> $id]);

                CacheHandler::delete( 'tags_'.md5($tag->id), true );

                CacheHandler::delete( 'tags_'.md5($tag->slug), true );

                return [$id];
            }
        }

        return false;
    }
    static public function deleteList($listID = []) {

        if(have_posts($listID)) {

            $model  = get_model()->settable('tags');

            if($model->delete_where_in(['field' => 'id', 'data' => $listID])) {

                do_action('delete_tags_list_trash_success', $listID );

                foreach ($listID as $key => $id) {
                    Metadata::deleteByMid('tags', $id);
                }

                $model->settable('tags_relationships')->delete_where_in(['field' => 'tag_id', 'data' => $listID]);

                CacheHandler::delete( 'tags_', true );

                return $listID;
            }
        }

        return false;
    }
    static public function getMeta($tags_id, $key, $single) {
        return Metadata::get('tags', $tags_id, $key, $single);
    }
    static public function updateMeta($tags_id, $meta_key, $meta_value) {
        return Metadata::update('tags', $tags_id, $meta_key, $meta_value);
    }
    static public function deleteMeta($tags_id, $meta_key, $meta_value) {
        return Metadata::delete('tags', $tags_id, $meta_key, $meta_value);
    }
    static public function empty($id, $type = 'product') {
        return get_model()->settable('tags_relationships')->delete_where(['object_id' => $id, 'object_type' => $type]);
    }
    static public function insertRelationship($id, $listID, $type = 'product') {

        $model = get_model();

        if( have_posts($listID) ) {

            $model->settable('tags_relationships');

            $temp = $model->gets_where(['object_id' => $id, 'object_type' => $type], ['select' => 'tag_id']);

            $relationships = [];

            foreach ($temp as $temp_key => $temp_value) {
                $relationships[$temp_value->tag_id] = $temp_value->tag_id;
            }

            $re['object_id']    = $id;

            $re['object_type'] 	= $type;

            foreach ($listID as $key => $tagID ) {
                if(isset($relationships[$tagID])) {
                    unset($relationships[$tagID]);
                    continue;
                }
                $re['tag_id'] 		= $tagID;
                $model->add($re);
            }

            if( have_posts($relationships) ) {
                foreach ($relationships as $tag_id) {
                    $model->delete_where(array('object_id' => $id, 'object_type' => $type, 'tag_id' => $tag_id ));
                }
            }
        }
    }
}