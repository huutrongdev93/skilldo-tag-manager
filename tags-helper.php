<?php
Class Tag extends \SkillDo\Model\Model {

    static string $table = 'tags';

    static array $column = [
    ];

    static array $rules = [
        'created' => true,
        'updated' => true,
        'add' => [
            'require' => [
                'name' => 'Không thể cập nhật trang khi tiêu đề trống'
            ]
        ]
    ];

    static function getsByObjectID($id, $type = 'product') {

        $temp = model('tags_relationships')::where('object_id', $id)
                ->where('object_type', $type)
                ->select('object_id', 'tag_id')
                ->fetch();

        $listID = [];

        foreach ($temp as $item) {
            $listID[] = $item->tag_id;
        }

        return static::whereIn('id', $listID)->fetch();
    }

    static function getsObjectID($id, $type = 'product') {

        $temp = model('tags_relationships')::where('tag_id', $id)
            ->where('object_type', $type)
            ->select('object_id', 'tag_id')
            ->fetch();

        $listID = [];

        foreach ($temp as $item) {
            $listID[] = $item->object_id;
        }

        return $listID;
    }

    static function deleteById($id = 0): array|bool
    {
        $id = (int)Str::clear($id);

        if($id == 0) return false;

        $model = model(static::$table);

        $tag   = static::get($id);

        if(have_posts($tag)) {

            do_action('delete_tags', $tag );

            if($model->delete(Qr::set($tag))) {

                do_action('delete_tags_success', $id );

                //delete menu
                $model->table('tags_relationships')::delete(Qr::set('tag_id', $id));

                CacheHandler::delete( 'tags_'.md5($tag->id), true );

                CacheHandler::delete( 'tags_'.md5($tag->slug), true );

                return [$id];
            }
        }

        return false;
    }

    static function deleteList($listID = []) {

        if(have_posts($listID)) {

            $model  = model(static::$table);

            if($model->delete(Qr::set()->whereIn('id', $listID))) {

                do_action('delete_tags_list_trash_success', $listID );

                $model->table('tags_relationships')::delete(Qr::set()->whereIn('tag_id', $listID));

                CacheHandler::delete( 'tags_', true );

                return $listID;
            }
        }

        return false;
    }

    static function empty($id, $type = 'product') {

        return model('tags_relationships')::where('object_id', $id)->where('object_type', $type)->remove();
    }

    static function insertRelationship($id, $listID, $type = 'product'): void
    {
        $model = model('tags_relationships');

        if( have_posts($listID) ) {

            $temp = $model::gets(Qr::set('object_id', $id)->where('object_type', $type)->select('tag_id'));

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
                    $model::delete(Qr::set('object_id', $id)->where('object_type', $type)->where('tag_id', $tag_id));
                }
            }
        }
    }
}