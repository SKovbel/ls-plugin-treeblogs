<?php
$config = array();
$config['blogs']['count'] = 5;

/**
 * Приоритет блока "дерево блогов"
 */
$config['treemenu_block_priority'] = 175;

/**
 * Регистрация таблицы topic_blog
 */
Config::Set('db.table.topic_blog', '___db.table.prefix___topic_blog');


return $config;
