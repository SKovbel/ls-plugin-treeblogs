
ALTER TABLE `prefix_blog` ADD `parent_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL , ADD INDEX ( `parent_id` );

ALTER TABLE `prefix_blog` ADD FOREIGN KEY ( `parent_id` ) REFERENCES `prefix_blog` (
`blog_id`
) ON DELETE SET NULL ON UPDATE CASCADE ;

CREATE TABLE `prefix_topic_blog` (
	`blog_id` int(11) unsigned NOT NULL,
	`topic_id` int(11) unsigned NOT NULL,
	PRIMARY KEY (`blog_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
