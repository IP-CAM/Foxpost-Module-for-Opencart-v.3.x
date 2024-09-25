<?php

namespace Opencart\Catalog\Controller\Extension\RauschFoxpost\Shipping;

class RauschFoxpost extends \Opencart\System\Engine\Controller {

    public function addOrder($route, &$data, &$output) {
        $oder_id = $data[0];
        $sdata = $this->session->data;
        $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');
        $van_mar = $this->model_extension_rausch_foxpost_shipping_rausch_foxpost->getFoxpostId($oder_id);
        if ($oder_id && ! $van_mar) {
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
