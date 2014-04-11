<?php
$installer = $this;
$installer->startSetup();

$installer->run("-- DROP TABLE IF EXISTS {$this->getTable('devils_colors_attribute')};
CREATE TABLE {$this->getTable('devils_colors_attribute')} (
	`entity_id` int(11) unsigned NOT NULL auto_increment,
	`attribute_id` int(11) unsigned NOT NULL default 0,
	`option_id` int(11) unsigned NOT NULL default 0,
	`color_id` int(11) unsigned NOT NULL default 0,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('devils_colors_color')};
CREATE TABLE {$this->getTable('devils_colors_color')} (
	`entity_id` int(11) unsigned NOT NULL auto_increment,
	`name` varchar(255) NOT NULL default '',
	`value` varchar(255) NOT NULL default '',
	`file` varchar(255) NOT NULL default '',
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
?>