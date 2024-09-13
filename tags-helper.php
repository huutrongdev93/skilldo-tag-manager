<?php

use SkillDo\DB;

Class Tag extends \SkillDo\Model\Model {

    protected string $table = 'tags';

    protected array $rules = [
        'add' => [
            'require' => [
                'name' => 'Không thể cập nhật trang khi tiêu đề trống'
            ]
        ]
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (Tag $tag, $listRemove, $objects) {
            //delete menu
            DB::table('tags_relationships')->whereIn('tag_id', $listRemove)->delete();

            foreach ($objects as  $object) {
                \SkillDo\Cache::delete( 'tags_'.md5($object->id), true );
                \SkillDo\Cache::delete( 'tags_'.md5($object->slug), true );
            }
        });
    }

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