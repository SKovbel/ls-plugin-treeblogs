ALTER TABLE `prefix_blog` ADD `parent_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
ADD INDEX ( `parent_id` );

ALTER TABLE `prefix_blog` ADD FOREIGN KEY ( `parent_id` ) REFERENCES `prefix_blog` (
`blog_id`
) ON DELETE SET NULL ON UPDATE CASCADE ;