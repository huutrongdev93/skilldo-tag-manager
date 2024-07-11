<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

return new class () extends Migration {

    public function up(): void
    {
        if(!schema()->hasTable('tags')) {
            schema()->create('tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('name_format', 200)->collate('utf8mb4_unicode_ci')->nullable();
                $table->string('slug', 255)->collate('utf8mb4_unicode_ci')->nullable();
                $table->integer('order')->default(0);
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated')->nullable();
            });
        }
        if(!schema()->hasTable('tags_relationships')) {
            schema()->create('tags_relationships', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('object_id')->default(0);
                $table->string('object_type', 200)->collate('utf8mb4_unicode_ci')->default('product');
                $table->integer('tag_id')->default(0);
            });
        }
    }

    public function down(): void
    {
        schema()->drop('tags');

        schema()->drop('tags_relationships');
    }
};