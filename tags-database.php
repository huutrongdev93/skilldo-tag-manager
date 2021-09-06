<?php
function tags_database_table_create() {

	$model = get_model('plugins', 'backend');

	$model->query("CREATE TABLE IF NOT EXISTS `".CLE_PREFIX."tags` (
		`id` INT NOT NULL AUTO_INCREMENT , 
        `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `name_format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `created` datetime NOT NULL,
        `updated` datetime NOT NULL,
        `order` int(11) NOT NULL DEFAULT '0',
	    PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;");

    $model->query("CREATE TABLE `".CLE_PREFIX."tags_relationships` ( 
        `id` INT NOT NULL AUTO_INCREMENT , 
        `object_id` INT NOT NULL DEFAULT '0' , 
        `object_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'product' , 
        `tag_id` INT NOT NULL DEFAULT '0' , 
        `created` DATETIME NOT NULL , 
        `updated` DATETIME NOT NULL , 
        `order` INT NOT NULL DEFAULT '0' , 
        PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;");
}

function tags_database_table_drop() {
	$model = get_model('plugins', 'backend');
	$model->query("DROP TABLE IF EXISTS `".CLE_PREFIX."tags`");
	$model->query("DROP TABLE IF EXISTS `".CLE_PREFIX."tags_relationships`");
}
