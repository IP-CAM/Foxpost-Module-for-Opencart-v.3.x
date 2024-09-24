<?php

namespace Opencart\Catalog\Controller\Extension\RauschFoxpost\Shipping;

class RauschFoxpost extends \Opencart\System\Engine\Controller {

    public function addOrder($route, &$data, &$output) {

        $sdata = $this->session->data;
        
        if (isset($sdata['order_id'])) {
            $shipping_code = explode(".", $sdata['shipping_method']['code']);
            if ($shipping_code[0] == "rausch_foxpost") {
                $this->db->query("INSERT INTO " . DB_PREFIX . "rausch_foxpost_order SET
                order_id = '" . (int)$sdata['order_id'] . "', 
                foxpost_type = '" . $this->db->escape($sdata['shipping_method']['code']) . "',
                date_added = NOW()");
            }
        }
    }
}
