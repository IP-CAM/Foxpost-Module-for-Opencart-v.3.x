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
                order_id = '" . (int)$oder_id . "', 
                foxpost_type = '" . $this->db->escape($szallitas->code) . "',
                date_added = NOW()");
            }
        }
    }
    
    public function validateTelephone(&$route, &$data, &$output) {
        file_put_contents('validatetelephone.txt', var_export($data, true) . PHP_EOL, FILE_APPEND);
        if (isset($this->request->post['telephone'])) {
            $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');
            $telephone = $this->request->post['telephone'];
            if ((utf8_strlen($telephone) < 10) || !preg_match('/^\+[1-9]{1}[0-9]{3,14}$/', $telephone)) {
                $json = [];
                $json['error']['telephone'] = $this->language->get('error_phone') ;
                $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
            }
        }
    }
    public function validateTelephoneCheckout(&$route, &$data, &$output) {
        file_put_contents('validatetelephonecheckout.txt', var_export($data, true) . PHP_EOL, FILE_APPEND);
        if (isset($this->request->post['telephone'])) {
            $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');
            $telephone = $this->request->post['telephone'];
            if ((utf8_strlen($telephone) < 10) || !preg_match('/^\+[1-9]{1}[0-9]{3,14}$/', $telephone)) {
                $json = [];
                $json['error']['telephone'] = $this->language->get('error_phone') ;
                $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
            }
        }
    }
}
