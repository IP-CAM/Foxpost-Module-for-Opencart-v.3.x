<?php
namespace Opencart\Admin\Model\Extension\RauschFoxpost\Shipping;
class RauschFoxpost extends \Opencart\System\Engine\Model {
	public function install(): void	{
		$this->db->query("ALTER TABLE " . DB_PREFIX . "session CHANGE data data LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rausch_foxpost_order` (
			rausch_foxpost_order_id int(11) NOT NULL,
			order_id int(11) NOT NULL,
			foxpost_type varchar(32) NOT NULL,
			foxpost_barcode varchar(40) NOT NULL,
			foxpost_size varchar(2) NOT NULL,
			foxpost_status varchar(16) NOT NULL,
			foxpost_shortname varchar(64) NOT NULL,
			date_added datetime NOT NULL,
			date_modified datetime DEFAULT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8");

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "rausch_foxpost_order` ADD PRIMARY KEY (rausch_foxpost_order_id)");

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "rausch_foxpost_order` MODIFY rausch_foxpost_order_id int(11) NOT NULL AUTO_INCREMENT");
	}

	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "rausch_foxpost_order`");
	}
}
