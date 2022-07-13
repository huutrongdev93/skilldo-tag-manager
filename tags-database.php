<?php
function tags_database_table_create() {
    $model = model();
    if(!$model::schema()->hasTable('tags')) {
        $model::schema()->create('tags', function ($table) {
            $table->increments('id');
            $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
            $table->string('name_format', 200)->collate('utf8mb4_unicode_ci')->nullable();
            $table->string('slug', 255)->collate('utf8mb4_unicode_ci')->nullable();
            $table->integer('order')->default(0);
            $table->dateTime('created');
            $table->dateTime('updated')->nullable();
        });
    }
    if(!$model::schema()->hasTable('tags_relationships')) {
        $model::schema()->create('tags_relationships', function ($table) {
            $table->increments('id');
            $table->integer('object_id')->default(0);
            $table->string('object_type', 200)->collate('utf8mb4_unicode_ci')->default('product');
            $table->integer('tag_id')->default(0);
            $table->integer('order')->default(0);
            $table->dateTime('created');
            $table->dateTime('updated')->nullable();
        });
    }
}

function tags_database_table_drop() {
    $model = model();
    $model::schema()->drop('tags');
    $model::schema()->drop('tags_relationships');
}