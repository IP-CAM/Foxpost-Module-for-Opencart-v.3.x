<?php

namespace Opencart\Catalog\Model\Extension\RauschFoxpost\Shipping;

class RauschFoxpost extends \Opencart\System\Engine\Model
{

	public function getQuote($address)
	{

		$this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');

		$currency = $this->model_localisation_currency->getCurrencyByCode($this->session->data['currency']);
		$lang     = strtolower($this->session->data['shipping_address']['iso_code_2']);
                
		$shipping_method = '';
		$shipping_method_code        = '';
		$shipping_method_code_type   = '';
		$shipping_method_code_apm    = '';
		$shipping_method_code_apmcod = '';
		$shipping_method_title       = '';
		$shipping_method_cost        = '';
		$shipping_method_text        = '';

		if (isset($this->session->data['shipping_method'])) {
			$shipping_method      = print_r($this->session->data['shipping_method'], true);
			$shipping_method_code = $this->session->data['shipping_method']['code'];
			$shipping_method_code = explode('.', $shipping_method_code);
			if ($shipping_method_code[0] == "foxpost" && (!($shipping_method_code[1] == "transfer" || $shipping_method_code[1] == "transfercod"))) {
				$shipping_method_title = $this->session->data['shipping_method']['title'];
				$shipping_method_cost  = $this->session->data['shipping_method']['cost'];
				$shipping_method_text  = $this->session->data['shipping_method']['text'];
				$shipping_method_code_type = explode('_', $shipping_method_code[1]);

				if (isset($shipping_method_code_type[1])) {
					$shipping_method_code_apm    = 'foxpost.' . $shipping_method_code_type[0];
					$shipping_method_code_apmcod = 'foxpost.' . $shipping_method_code[1];

					$shipping_method_code_type   = $shipping_method_code_type[1];
				} else {
					$shipping_method_code_apm    = 'foxpost.' . $shipping_method_code[1];
					$shipping_method_code_apmcod = 'foxpost.' . $shipping_method_code[1] . '_cod';

					$shipping_method_code_type = '';
				}

				$shipping_method_code  = $this->session->data['shipping_method']['code'];
			} else {
				$shipping_method_code  = '';
			}
		}

		$text_foxpost_apm         = $this->language->get('text_foxpost_apm');
		$text_foxpost_apmcod      = $this->language->get('text_foxpost_apmcod');
		$text_foxpost_transfer    = $this->language->get('text_foxpost_transfer');
		$text_foxpost_transfercod = $this->language->get('text_foxpost_transfercod');

		$apms = json_decode(file_get_contents("https://cdn.foxpost.hu/apms.json"), true);

		$error = false;

		$quote_data = array();

                
		$apm_cost         = $this->config->get('shipping_rausch_foxpost_apm_cost');
		$apmcod_cost      = $this->config->get('shipping_rausch_foxpost_apmcod_cost');
		$transfer_cost    = $this->config->get('shipping_rausch_foxpost_transfer_cost');
		$transfercod_cost = $this->config->get('shipping_rausch_foxpost_transfercod_cost');

		$freelimit        = $this->config->get('shipping_rausch_foxpost_freelimit');
		$total            = round($this->cart->getTotal());
                
		$_apm_cost         = $this->currency->format(floatval($this->tax->calculate($apm_cost, $this->config->get('shipping_rausch_foxpost_tax_class_id'), $this->config->get('config_tax') )), strtoupper($this->session->data['currency']));
		$_apmcod_cost      = $this->currency->format(floatval($this->tax->calculate($apmcod_cost, $this->config->get('shipping_rausch_foxpost_tax_class_id'), $this->config->get('config_tax'))), strtoupper($this->session->data['currency']));
		$_transfer_cost    = $this->currency->format(floatval($this->tax->calculate($transfer_cost, $this->config->get('shipping_rausch_foxpost_tax_class_id'), $this->config->get('config_tax'))), strtoupper($this->session->data['currency']));
		$_transfercod_cost = $this->currency->format(floatval($this->tax->calculate($transfercod_cost, $this->config->get('shipping_rausch_foxpost_tax_class_id'), $this->config->get('config_tax'))), strtoupper($this->session->data['currency']));
		
                if ($freelimit && $freelimit <= $total) {
			$apm_cost          = 0;
			$apmcod_cost       = 0;
			$transfer_cost     = 0;
			$transfercod_cost  = 0;

			$_apm_cost         = $this->language->get('text_free');
			$_apmcod_cost      = $this->language->get('text_free');
			$_transfer_cost    = $this->language->get('text_free');
			$_transfercod_cost = $this->language->get('text_free');
		}

		if (!$this->config->get('shipping_rausch_foxpost_service')) {
			$error = $this->language->get('error_service');
		} elseif ($currency['code'] != 'HUF') {
			$error = $this->language->get('error_currency');
		} elseif ($address['iso_code_2'] != 'HU') {
			$error = $this->language->get('error_country');
		} else {

			if (in_array('transfer', $this->config->get('shipping_rausch_foxpost_service'))) {
				$quote_data['transfer'] = array(
					'code'         => "foxpost.transfer",
					'title'        => $text_foxpost_transfer,
					'cost'         => $transfer_cost,
					'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
					'text'         => $_transfer_cost
				);
			}

			if (in_array('transfercod', $this->config->get('shipping_rausch_foxpost_service'))) {
				$quote_data['transfercod'] = array(
					'code'         => "foxpost.transfercod",
					'title'        => $text_foxpost_transfercod,
					'cost'         => $transfercod_cost,
					'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
					'text'         => $_transfercod_cost
				);
			}

			if (in_array('apm', $this->config->get('shipping_rausch_foxpost_service')) || in_array('apmcod', $this->config->get('shipping_rausch_foxpost_service'))) {
				$foxpost_widget = $this->load->view('extension/rausch_foxpost/shipping/foxpost_widget', array(
					'lang'						  => $lang,
					'shipping_method'             => $shipping_method,
					'shipping_method_code'        => $shipping_method_code,
					'shipping_method_code_type'   => $shipping_method_code_type,
					'shipping_method_code_apm'    => $shipping_method_code_apm,
					'shipping_method_code_apmcod' => $shipping_method_code_apmcod,
					'shipping_method_title'       => $shipping_method_title,
					'shipping_method_text'        => $shipping_method_text,
					'text_foxpost_apm'            => $text_foxpost_apm,
					'text_foxpost_apmcod'         => $text_foxpost_apmcod,
					'_apm_cost'                   => $_apm_cost,
					'_apmcod_cost'                => $_apmcod_cost,
				));

				if (in_array('apm', $this->config->get('shipping_rausch_foxpost_service'))) {
					$quote_data['apm'] = array(
						'code'         => "rausch_foxpost.apm",
						'name'        => $text_foxpost_apm,
						'cost'         => $apm_cost,
						'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
						'text'         => $_apm_cost . (!in_array('apmcod', $this->config->get('shipping_rausch_foxpost_service')) ? $foxpost_widget : '')
					);

					foreach ($apms as $item) {
						$quote_data[$item['operator_id']] = array(
							'code'         => 'rausch_foxpost.' . $item['operator_id'],
							'name'        => $text_foxpost_apm . " (" . $item['name'] . ")",
							'cost'         => $apm_cost,
							'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
							'text'         => $_apm_cost
						);
					}
				}

				if (in_array('apmcod', $this->config->get('shipping_rausch_foxpost_service'))) {
					$quote_data['apmcod'] = array(
						'code'         => "rausch_foxpost.apmcod",
						'name'        => $text_foxpost_apmcod,
						'cost'         => $apmcod_cost,
						'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
						'text'         => $_apmcod_cost . $foxpost_widget
					);

					foreach ($apms as $item) {
						$quote_data[$item['operator_id'] . '_cod'] = array(
							'code'         => 'rausch_foxpost.' . $item['operator_id'] . '_cod',
							'name'        => $text_foxpost_apmcod . " (" . $item['name'] . ")",
							'cost'         => $apmcod_cost,
							'tax_class_id' => $this->config->get('shipping_rausch_foxpost_tax_class_id'),
							'text'         => $_apmcod_cost
						);
					}
				}
			}
		}

		$method_data = array(
			'code'       => 'rausch_foxpost',
			'name'      => 'FoxPost',
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('shipping_rausch_foxpostsort_order'),
			'error'      => $error
		);
		return $method_data;
	}
}
