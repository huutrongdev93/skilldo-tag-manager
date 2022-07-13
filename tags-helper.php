<?php
Class Tag extends Model {
    static string $table = 'tags';
    static public function getsByObjectID($id, $type = 'product') {
        $temp   = model('tags_relationships')->gets(Qr::set('object_id', $id)->where('object_type', $type)->select('object_id', 'tag_id'));
        $listID = [];
        foreach ($temp as $item) {
            $listID[] = $item->tag_id;
        }
        return $listID;
    }
    static public function getsObject($id, $type = 'product') {
        $temp  = model('tags_relationships')->gets(Qr::set('object_type', $type)->where('tag_id', $id)->select('object_id'));
        $listID = [];
        foreach ($temp as $item) {
            $listID[] = $item->object_id;
        }
        return $listID;
    }
    static public function insert($tags = []) {

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
            $argsCount = Qr::set('slug', $slug)->where('id', '<>', $id);
        }
        else {
            $argsCount = Qr::set('slug', $slug);
        }

        $count = static::count($argsCount);

        $i = 1;

        while ($count > 0) {

            $slug = $slug.'-'.$i;

            if($argsCount->isWhere('slug')) {
                $argsCount->removeWhere('slug');
            }

            $argsCount->where('slug', $slug);

            $count = static::count($argsCount);

            $i++;
        }

        $data = compact( 'name', 'name_format', 'slug');

        $data = apply_filters( 'pre_insert_tags_data', $data, $tags, $update ? $old_tags : null);

        $model = model(static::$table);

        if ($update) {

            $model->update( $data, Qr::set($id));

            $tags_id = (int) $id;
        }
        else {

            $tags_id = $model->add( $data );
        }

        $model->settable('tags');

        $tags_id  = apply_filters( 'after_insert_tags', $tags_id, $tags, $data, $update ? $old_tags : null  );

        return $tags_id;
    }
    static public function delete($id = 0) {

        $id = (int)Str::clear($id);

        if($id == 0) return false;

        $model = model(static::$table);

        $tag   = static::get($id);

        if(have_posts($tag)) {

            do_action('delete_tags', $tag );

            if($model->delete(Qr::set($tag))) {

                do_action('delete_tags_success', $id );

                Metadata::deleteByMid('tags', $id);

                //delete menu
                $model->settable('tags_relationships')->delete(Qr::set('tag_id', $id));

                CacheHandler::delete( 'tags_'.md5($tag->id), true );

                CacheHandler::delete( 'tags_'.md5($tag->slug), true );

                return [$id];
            }
        }

        return false;
    }
    static public function deleteList($listID = []) {

        if(have_posts($listID)) {

            $model  = model(static::$table);

            if($model->delete(Qr::set()->whereIn('id', $listID))) {

                do_action('delete_tags_list_trash_success', $listID );

                foreach ($listID as $id) {
                    Metadata::deleteByMid('tags', $id);
                }

                $model->settable('tags_relationships')->delete(Qr::set()->whereIn('tag_id', $listID));

                CacheHandler::delete( 'tags_', true );

                return $listID;
            }
        }

        return false;
    }
    static public function empty($id, $type = 'product') {
        return model('tags_relationships')->delete(Qr::set('object_id', $id)->where('object_type', $type));
    }
    static public function insertRelationship($id, $listID, $type = 'product') {

        $model = get_model();

        if( have_posts($listID) ) {

            $model->settable('tags_relationships');

            $temp = $model->gets(Qr::set('object_id', $id)->where('object_type', $type)->select('tag_id'));

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
                $re['tag_id'] = $tagID;
                $model->add($re);
            }

            if( have_posts($relationships) ) {
                foreach ($relationships as $tag_id) {
                    $model->delete(Qr::set('object_id', $id)->where('object_type', $type)->where('tag_id', $tag_id));
                }
            }
        }
    }
}