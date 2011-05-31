<?php
$config = array();
$config['blogs']['count'] = 5;

/**
 * Приоритет блока "дерево каталогов". 0-disabled
 */
$config['treemenu_block_priority'] = 110;

/**
 * Регистрация таблицы topic_blog
 */
Config::Set('db.table.topic_blog', '___db.table.prefix___topic_blog');


return $config;
