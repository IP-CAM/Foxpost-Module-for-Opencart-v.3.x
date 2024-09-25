<?php

namespace Opencart\Catalog\Controller\Extension\RauschFoxpost\Shipping;

class RauschFoxpost extends \Opencart\System\Engine\Controller {

    public function addOrder($route, &$data, &$output) {
        $oder_id = $data[0];
        $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');
        $oder = $this->model_extension_rausch_foxpost_shipping_rausch_foxpost->getOrder($oder_id);
        $van_mar = $this->model_extension_rausch_foxpost_shipping_rausch_foxpost->getFoxpostId($oder_id);
        if ($oder_id && ! $van_mar && isset($oder['shipping_method'])) {
            $szallitas = json_decode($oder['shipping_method']); 
            $shipping_code = explode(".", $szallitas->code);
            if ($shipping_code[0] == "rausch_foxpost") {
                $this->db->query("INSERT INTO " . DB_PREFIX . "rausch_foxpost_order SET
                order_id = '" . (int)$sdata['order_id'] . "', 
                foxpost_type = '" . $this->db->escape($szallitas->code) . "',
                date_added = NOW()");
            }
        }
    }
}
