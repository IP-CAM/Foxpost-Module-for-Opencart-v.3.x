<?php

namespace Opencart\Admin\Controller\Extension\RauschFoxpost\Shipping;



class RauschFoxpost extends \Opencart\System\Engine\Controller {
    
    private $error   = array();

    private $version = "1.0.21";

    public function index(): void {

        $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['save'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping');

        $data['shipping_rausch_foxpost_apm_cost'] = $this->config->get('shipping_rausch_foxpost_apm_cost');
        $data['shipping_rausch_foxpost_apmcod_cost'] = $this->config->get('shipping_rausch_foxpost_apmcod_cost');
        $data['shipping_rausch_foxpost_transfer_cost'] = $this->config->get('shipping_rausch_foxpost_transfer_cost');
        $data['shipping_rausch_foxpost_transfercod_cost'] = $this->config->get('shipping_rausch_foxpost_transfercod_cost');
        $data['shipping_rausch_foxpost_freelimit'] = $this->config->get('shipping_rausch_foxpost_freelimit');
        $data['shipping_rausch_foxpost_service'] = $this->config->get('shipping_rausch_foxpost_service');
        $data['shipping_rausch_foxpost_order_status_id'] = $this->config->get('shipping_rausch_foxpost_order_status_id');
        $data['shipping_rausch_foxpost_order_status_id'] = $this->config->get('shipping_rausch_foxpost_order_status_id');

        $data['shipping_rausch_foxpost_status'] = $this->config->get('shipping_rausch_foxpost_status');

        $data['shipping_rausch_foxpost_test'] = $this->config->get('shipping_rausch_foxpost_test');
        $data['shipping_rausch_foxpost_username'] = $this->config->get('shipping_rausch_foxpost_username');
        $data['shipping_rausch_foxpost_password'] = $this->config->get('shipping_rausch_foxpost_password');
        $data['shipping_rausch_foxpost_key'] = $this->config->get('shipping_rausch_foxpost_key');
        
        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        $data['shipping_rausch_foxpost_tax_class_id'] = $this->config->get('shipping_rausch_foxpost_tax_class_id');        

        foreach (['CREATE', 'OPEROUT', 'OPERIN', 'C2CIN', 'C2OUT', 'SORTIN'] as $item) {
            $data['shipping_rausch_foxpost_order_foxpost_' . $item . '_status_id'] = $this->config->get('shipping_rausch_foxpost_order_foxpost_' . $item . '_status_id');
        }

        $data['apms'] = json_decode(file_get_contents("https://cdn.foxpost.hu/apms.json"), true);

        usort($data['apms'], function ($a, $b) {
            return $a['group'] <=> $b['group'];
        });

        $ret = $this->apicall("/api/address");

        $data['api_status'] = false;
        if ($ret['code'] == 200) {
            $data['api_status'] = true;
        } else {
            if (isset($ret['data']['error'])) {
                $data['api_error'] = $ret['data']['error'];
            } else {
                $data['api_error'] = "CONNECTION ERROR";
            }
        }


        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['shipping_rausch_foxpost_geo_zone_id'] = $this->config->get('shipping_rausch_foxpost_geo_zone_id');

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['shipping_rausch_foxpost_status'] = $this->config->get('shipping_rausch_foxpost_status');
        $data['shipping_rausch_foxpost_sort_order'] = ($this->config->get('shipping_rausch_foxpost_sort_order')) ? $this->config->get('shipping_rausch_foxpost_sort_order') : 0;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/rausch_foxpost/shipping/rausch_foxpost_settings', $data));
    }

    public function save(): void {
        $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/rausch_foxpost/shipping/rausch_foxpost')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['shipping_rausch_foxpost_service'])) {
            $json['error']['service'] = $this->language->get('error_service');
        }

        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_apm_cost']) < 1)) {
            $json['error']['apm-cost'] = $this->language->get('error_apm_cost');
        }
        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_apmcod_cost']) < 1)) {
            $json['error']['apmcod-cost'] = $this->language->get('error_apmcod_cost');
        }
        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_transfer_cost']) < 1)) {
            $json['error']['transfer-cost'] = $this->language->get('error_transfer_cost');
        }
        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_transfercod_cost']) < 1)) {
            $json['error']['transfercod-cost'] = $this->language->get('error_transfercod_cost');
        }

        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_username']) < 1)) {
            $json['error']['username'] = $this->language->get('error_username');
        }
        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_password']) < 1)) {
            $json['error']['password'] = $this->language->get('error_password');
        }
        if ((oc_strlen($this->request->post['shipping_rausch_foxpost_key']) < 1)) {
            $json['error']['key'] = $this->language->get('error_key');
        }

        if (isset($json['error']) && !isset($json['error']['warning'])) {
            $json['error']['warning'] = $this->language->get('error_warning');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('shipping_rausch_foxpost', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // ORDERS

    public function orders() {
        if (!$this->user->hasPermission('access', 'sale/order')) {
            $this->load->language('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('error/permission', $data));

            return;
        }

        $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');
        $this->document->setTitle($this->language->get('heading_title'));

        $get = $this->request->get;

        // TEST

        if (isset($get['test'])) {

            exit;
        }

        // MIGRATE

        if (isset($get['migrate'])) {
            $query = $this->db->query("INSERT INTO " . DB_PREFIX . "rausch_foxpost_order (order_id, foxpost_type, foxpost_barcode, date_added) SELECT order_id, shipping_code, tracking, date_added FROM " . DB_PREFIX . "order WHERE order_id NOT IN ( SELECT order_id FROM " . DB_PREFIX . "rausch_foxpost_order )");
            $foxpost_datas = $query->rows;

            print "<pre>";
            print_r($foxpost_datas);
            exit;
        }

        // EXPORT
        elseif (isset($get['export'])) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE rausch_foxpost_order_id IN(" . $get['export'] . ')');
            $temp = $query->rows;

            $ids = [];
            foreach ($temp as $item) {
                $ids[] = $item['order_id'];
                $foxpost_data[$item['order_id']] = $item;
            }

            if ($ids) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order WHERE order_id IN(" . implode(',', $ids) . ')');
                $temp = $query->rows;

                foreach ($temp as $item) {
                    $order_data[$item['order_id']] = $item;
                }
            }

            $headers = array(
                "Címzett neve",
                "Címzett telefonszáma",
                "Címzett email címe",
                "Átvételi automata",
                "Település",
                "Irányítószám",
                "Utca, házszám",
                "Utánvételi összeg",
                "Csomag méret",
                "Futár információk",
                "Saját adatok",
                "Címkenyomtatás",
                "Törékeny",
                "Egyedi vonalkód",
                "Referencia kód"
            );

            $out = '"' . implode('","', $headers) . '"' . "\n";

            foreach ($foxpost_data as $foxpost_row) {
                $row_data = array();
                $order_row = $order_data[$foxpost_row['order_id']];

                $delivery = explode(".", $foxpost_row['foxpost_type']);

                $total = round($order_row['total']);

                $row_data[] = $order_row['lastname'] . " " . $order_row['firstname'];
                $row_data[] = $order_row['telephone'];
                $row_data[] = $order_row['email'];

                if (isset($delivery[1]) && $delivery[1] != 'transfer' && $delivery[1] != 'transfercod') {
                    $apm = explode("_", $delivery[1]);

                    $row_data[] = $apm[0]; // Átvételi automata
                    $row_data[] = ''; // Település
                    $row_data[] = ''; // Irányítószám
                    $row_data[] = ''; // Utca, házszám

                    if (isset($apm[1]) && $apm[1] == "cod") {
                        $row_data[] = $total; // Utánvételi összeg
                    } else {
                        $row_data[] = ''; // Utánvételi összeg
                    }
                } else {
                    $row_data[] = ''; // Átvételi automata
                    $row_data[] = $order_row['shipping_city']; // Település
                    $row_data[] = $order_row['shipping_postcode']; // Irányítószám
                    $row_data[] = $order_row['shipping_address_1'] . ' ' . $order_row['shipping_address_2']; // Utca, házszám

                    if ($delivery[1] == "transfercod") {
                        $row_data[] = $total; // Utánvételi összeg
                    } else {
                        $row_data[] = ''; // Utánvételi összeg
                    }
                }

                $row_data[] = $foxpost_row['foxpost_size'] ? $foxpost_row['foxpost_size'] : strtoupper($get['size']); // Csomag méret
                $row_data[] = str_replace("\n", " ", $order_row['comment']); // Futár információk
                $row_data[] = ''; // Saját adatok
                $row_data[] = ''; // Címkenyomtatás
                $row_data[] = ''; // Törékeny
                $row_data[] = $foxpost_row['foxpost_barcode']; // Egyedi vonalkód
                $row_data[] = ''; // Referencia kód

                $out .= '"' . implode('","', $row_data) . '"' . "\n";
            }

            $this->response->addHeader('Content-Type: text/csv');
            $this->response->addHeader('Content-Disposition: attachment; filename=foxpost_export.csv');
            $this->response->setOutput($out);
        }

        // BARCODE
        elseif (isset($get['barcode']) && isset($get['pagesize'])) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE foxpost_barcode != '' AND rausch_foxpost_order_id IN(" . $get['barcode'] . ')');

            $foxpost_datas = $query->rows;

            $data = [];
            $error = '';

            foreach ($foxpost_datas as $foxpost_data) {
                $data[] = $foxpost_data['foxpost_barcode'];
            }

            $resp = $this->apicall('/api/label/' . $get['pagesize'], 'POST', $data);

            $this->response->addHeader('Content-Type: application/pdf');
            $this->response->setOutput($resp['data']);
        }

        // SYNC
        elseif (isset($get['sync'])) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE foxpost_barcode != '' AND rausch_foxpost_order_id IN(" . $get['sync'] . ')');
            $foxpost_datas = $query->rows;

            $data = [];
            $error = '';

            foreach ($foxpost_datas as $foxpost_data) {
                $resp = $this->apicall('/api/tracking/' . $foxpost_data['foxpost_barcode']);
                
                if (isset($resp['code']) && $resp['code'] == 200) {
                    // OK
                } elseif (isset($resp['code']) && $resp['code'] == 401) {
                    $error = "FOXPOST API: " . $this->language->get('error_api_401') . " (401)";
                } elseif (isset($resp['code']) && $resp['code'] == 400) {
                    $error = "FOXPOST API: " . (isset($resp['data']['error']) ? $resp['data']['error'] : $this->language->get('error_api_unknow') . " (400)");
                } else {
                    $error = $this->language->get('error_api_unknow');
                }

                if ($error) {
                    break;
                } else {
                    if ($sid = $this->config->get('shipping_rausch_foxpost_order_foxpost_' . $resp['data']['traces'][0]['status'] . '_status_id')) {
                        $query = $this->db->query("
							UPDATE " . DB_PREFIX . "order
							SET
								order_status_id = '" . $sid . "'	
							WHERE
								order_id = " . round($foxpost_data['order_id']) . " AND
								order_status_id != " . $sid
                        );

                        if ($this->db->countAffected()) {
                            $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');
                            $this->model_extension_shipping_foxpost->addOrderHistory(
                                    round($foxpost_data['order_id']),
                                    $sid,
                                    'Foxpost'
                            );
                        }
                    }

                    $query = $this->db->query("
						UPDATE " . DB_PREFIX . "rausch_foxpost_order
						SET
							foxpost_shortname = '" . $resp['data']['traces'][0]['shortName'] . "',
							foxpost_status = '" . $resp['data']['traces'][0]['status'] . "',
							date_modified = NOW()
						WHERE
							rausch_foxpost_order_id = " . $foxpost_data['rausch_foxpost_order_id']
                    );
                }
            }
            exit;

            if ($error) {
                $data = array(
                    'message' => $error
                );
                $this->response->addHeader('HTTP/1.1 400 Bad Request');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($data));
        }

        // CREATE
        elseif (isset($get['create'])) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE rausch_foxpost_order_id = " . round($get['create']));
            $foxpost_data = $query->row;

            $this->load->model('sale/order');
            $order_data = $this->model_sale_order->getOrder($foxpost_data['order_id']);

            list($temp, $foxpost_type) = explode(".", $foxpost_data['foxpost_type']);

            if ($foxpost_type == "transfer") {
                $data['recipientCity'] = (!empty($order_data['shipping_city'])) ? $order_data['shipping_city'] : $order_data['payment_city'];
                $data['recipientZip'] = (!empty($order_data['shipping_postcode'])) ? $order_data['shipping_postcode'] : $order_data['payment_postcode'];
                $data['recipientAddress'] = (!empty($order_data['shipping_address_1'] . " " . $order_data['shipping_address_2'])) ? $order_data['shipping_address_1'] . " " . $order_data['shipping_address_2'] : $order_data['payment_address_1'] . " " . $order_data['payment_address_2'];
            } elseif ($foxpost_type == "transfercod") {
                $data['cod'] = round($order_data['total']);

                $data['recipientCity'] = (!empty($order_data['shipping_city'])) ? $order_data['shipping_city'] : $order_data['payment_city'];
                $data['recipientZip'] = (!empty($order_data['shipping_postcode'])) ? $order_data['shipping_postcode'] : $order_data['payment_postcode'];
                $data['recipientAddress'] = (!empty($order_data['shipping_address_1'] . " " . $order_data['shipping_address_2'])) ? $order_data['shipping_address_1'] . " " . $order_data['shipping_address_2'] : $order_data['payment_address_1'] . " " . $order_data['payment_address_2'];
            } else {
                $foxpost_type = explode("_", $foxpost_type);
                $foxpost_iscod = isset($foxpost_type[1]) ? $foxpost_type[1] : '';
                $foxpost_type = $foxpost_type[0];

                if ($foxpost_iscod == "cod") {
                    $data['cod'] = round($order_data['total']);
                }

                $data['destination'] = $foxpost_type;
            }

            $data['recipientEmail'] = $order_data['email'];
            $data['recipientPhone'] = $order_data['telephone'];
            $data['recipientName']  = $order_data['shipping_lastname'] . " " . $order_data['shipping_firstname'];

            $data['size'] = $get['size'];
            $data['source'] = "opencart4_rausch_" . $this->version;

            $error = '';
            $resp = $this->apicall('/api/parcel', 'POST', [$data]);

            $data = array();

            if (isset($resp['code']) && $resp['code'] == 201) {
                // OK
            } elseif (isset($resp['code']) && $resp['code'] == 401) {
                $error = "FOXPOST API: " . $this->language->get('error_api_401') . " (401)";
            } elseif (isset($resp['code']) && $resp['code'] == 400) {
                $error = "FOXPOST API: " . (isset($resp['data']['error']) ? $resp['data']['error'] : $this->language->get('error_api_unknow') . " (400)");
            } else {
                $error = $this->language->get('error_api_unknow');
            }

            if (!(isset($resp['data']['valid']) && $resp['data']['valid'])) {
                if (isset($resp['data']['parcels'][0]['errors'][0]['message']) && $resp['data']['parcels'][0]['errors'][0]['message']) {
                    $error = "FOXPOST API: " . $resp['data']['parcels'][0]['errors'][0]['message'];
                } else {
                    $error = "FOXPOST API: " . $this->language->get('error_api_validation');
                }
            }

            if ($error) {
                $data = array(
                    'message' => $error
                );
                $this->response->addHeader('HTTP/1.1 400 Bad Request');
            } else {
                $data_temp = $data;
                $data = [];

                $query = $this->db->query("UPDATE " . DB_PREFIX . "rausch_foxpost_order SET foxpost_barcode = '" . $resp['data']['parcels'][0]['clFoxId'] . "', foxpost_size = '" . $resp['data']['parcels'][0]['size'] . "', date_modified = NOW() WHERE rausch_foxpost_order_id = " . round($get['create']));
                $query = $this->db->query("UPDATE " . DB_PREFIX . "order SET tracking = '" . $resp['data']['parcels'][0]['clFoxId'] . "' WHERE order_id = " . round($foxpost_data['order_id']));

                if ($this->config->get('shipping_rausch_foxpost_order_foxpost_status_id')) {
                    $comment = $this->language->get('text_foxpost_tracking') . ": https://foxpost.hu/csomagkovetes/?code=" . $resp['data']['parcels'][0]['clFoxId'];

                    $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');
                    $this->model_extension_shipping_foxpost->addOrderHistory(
                            round($foxpost_data['order_id']),
                            $this->config->get('shipping_rausch_foxpost_order_foxpost_status_id'),
                            $comment
                    );
                }

                $order_info = $order_data;

                $language = new Language($order_info['language_code']);
                $language->load($order_info['language_code']);
                $language->load('extension/rausch_foxpost/shipping/rausch_foxpost');

                $data['order_info'] = $order_info;

                $order_info['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));

                $data['logo'] = $order_info['store_url'] . 'image/' . $this->config->get('config_logo');
                $data['store_name'] = $order_info['store_name'];
                $data['store_url'] = $order_info['store_url'];
                $data['customer_id'] = $order_info['customer_id'];
                $data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];

                $data['title'] = sprintf($language->get('mail_foxpost_subject'), $order_info['store_name'], $order_info['order_id']);
                $data['mail_foxpost_greeting'] = sprintf($language->get('mail_foxpost_greeting'), $order_info['store_name']);

                $data['foxpost_data'] = $foxpost_data;

                $data['foxpost_data']['foxpost_barcode'] = $resp['data']['parcels'][0]['clFoxId'];

                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['payment_firstname'],
                    'lastname' => $order_info['payment_lastname'],
                    'company' => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city' => $order_info['payment_city'],
                    'postcode' => $order_info['payment_postcode'],
                    'zone' => $order_info['payment_zone'],
                    'zone_code' => $order_info['payment_zone_code'],
                    'country' => $order_info['payment_country']
                );

                $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname' => $order_info['shipping_lastname'],
                    'company' => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city' => $order_info['shipping_city'],
                    'postcode' => $order_info['shipping_postcode'],
                    'zone' => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country' => $order_info['shipping_country']
                );

                $data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($order_data['email']);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(sprintf($this->language->get('mail_foxpost_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $foxpost_data['order_id']));
                $mail->setHtml($this->load->view('extension/rausch_foxpost/shipping/rausch_foxpost_mail', $data));
                $mail->send();

                $data = $data_temp;

                $this->response->redirect($this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'] . '&sync=' . $foxpost_data['foxpost_order_id'], true));
            }

            // $data['data'] = $resp;

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($data));
        }

        // LIST
        else {

            $data['link_base'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'], true);
            $data['link_archive'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'] . '&filter=archive', true);

            $orderby = isset($get['orderby']) ? $get['orderby'] : 'date_added';
            $orderbysub = isset($get['orderbysub']) ? $get['orderbysub'] : 'DESC';

            $data['orderby'] = $orderby;
            $data['orderbysub'] = $orderbysub;

            if (isset($get['filter']) && $get['filter'] == 'archive') {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE foxpost_status IN ('RECIEVE', 'HDRECIEVE', 'HDRETURN', 'INWAREHOUSE', 'COLLECTSENT') ORDER BY " . $orderby . " " . $orderbysub);
                $data['archive'] = true;
            } else {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "rausch_foxpost_order WHERE foxpost_status NOT IN ('RECIEVE', 'HDRECIEVE', 'HDRETURN', 'INWAREHOUSE', 'COLLECTSENT') ORDER BY " . $orderby . " " . $orderbysub);
            }

            $data['data'] = $query->rows;

            foreach ($data['data'] as &$item) {
                $item['link_sync'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'] . '&sync=' . $item['rausch_foxpost_order_id'], true);
                $item['link_barcode'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'] . '&barcode=' . $item['rausch_foxpost_order_id'], true);
                $item['link_create'] = $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'] . '&create=' . $item['rausch_foxpost_order_id'], true);
                $item['link_order'] = $this->url->link('sale/order.info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $item['order_id'], true);

                list($temp, $foxpost_type) = explode(".", $item['foxpost_type']);

                if ($foxpost_type == "transfer") {
                    $item['foxpost_type'] = 'transfer';
                    $item['foxpost_cod'] = false;
                } elseif ($foxpost_type == "transfercod") {
                    $item['foxpost_type'] = 'transfer';
                    $item['foxpost_cod'] = true;
                } else {
                    $foxpost_type = explode("_", $foxpost_type);
                    $foxpost_iscod = isset($foxpost_type[1]) ? $foxpost_type[1] : '';

                    $item['foxpost_type'] = $foxpost_type[0];

                    if ($foxpost_iscod == "cod") {
                        $item['foxpost_cod'] = true;
                    } else {
                        $item['foxpost_cod'] = false;
                    }
                }
            }

            $ret = $this->apicall("/api/address");

            $data['api_status'] = false;
            if ($ret['code'] == 200) {
                $data['api_status'] = true;
            }

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_orders'),
                'href' => $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'], true)
            );

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $data['apms'] = [];
            $temp = json_decode(file_get_contents("https://cdn.foxpost.hu/apms.json"), true);
            foreach ($temp as $apm) {
                $data['apms'][$apm['operator_id']] = $apm['name'];
            }
            unset($temp);

            $this->response->setOutput($this->load->view('extension/rausch_foxpost/shipping/rausch_foxpost_orders', $data));
        }
    }

    // API CALL

    protected function apicall($path, $method = 'GET', $request_data = array(), $query = array()) {
        $base_url = ($this->config->get('shipping_rausch_foxpost_test')) ? "https://webapi-test.foxpost.hu" : "https://webapi.foxpost.hu";
        $query = ($this->config->get('shipping_rausch_foxpost_test')) ? array_merge($query, array('isWeb' => false)) : $query;
        $url = $base_url . $path . ($query ? '?' . http_build_query($query) : '');
        
        $code = '';
        $headers = [];

        $username = $this->config->get('shipping_rausch_foxpost_username');
        $password = $this->config->get('shipping_rausch_foxpost_password');
        $key = $this->config->get('shipping_rausch_foxpost_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER,
                array(
                    "Content-Type: application/json",
                    'authorization: Basic ' . base64_encode($username . ":" . $password),
                    'api-key: ' . $key
                )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $data = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $header = explode("\n", $header);

        foreach ($header as &$header_item) {
            $header_item = trim($header_item);
        }

        if (in_array("Content-Type: application/json", $header) || in_array("Content-Type: application/json;charset=ISO-8859-1", $header)) {
            $data = json_decode($data, true);
        }

        curl_close($ch);
        file_put_contents('apicall.txt', var_export($data, true) . PHP_EOL, FILE_APPEND);
        return array(
            'code' => $code,
            'header' => $header,
            'data' => $data,
            // FOR DEBUG
            'request' => array(
                'url' => $url,
                'method' => $method,
                'payload' => $request_data
            )
        );
    }

    public function install(): void {
        $this->uninstall();
        $this->load->model('setting/event');
        $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');
        $this->model_extension_rausch_foxpost_shipping_rausch_foxpost->install();
        
        // Remove Event
        $this->model_setting_event->deleteEventByCode('rausch_foxpost_adminmenu');
        $this->model_setting_event->deleteEventByCode('rausch_shipping_foxpost');
        $this->model_setting_event->deleteEventByCode('rausch_shipping_foxpost_telephone_mask');
        
        // Add Event
        if (VERSION >= '4.0.2.0') {
            $this->model_setting_event->addEvent([
                'code' => 'rausch_foxpost_adminmenu',
                'description' => 'Rausch Foxpost Admin Menu',
                'trigger' => 'admin/view/common/column_left/before',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost.AddtoAdminMenu',
                'status' => 1,
                'sort_order' => 0
            ]);
            $this->model_setting_event->addEvent([
                'code' => 'rausch_shipping_foxpost',
                'description' => 'Rausch Foxpost',
                'trigger' => 'catalog/model/checkout/order/addHistory/after',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost.addOrder',
                'status' => 1,
                'sort_order' => 0
            ]);
            $this->model_setting_event->addEvent([
                'code' => 'rausch_shipping_foxpost_telephone_mask',
                'description' => 'Rausch Foxpost Telefon Mask',
                'trigger' => 'catalog/controller/account/register/validate/before',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost.validateTelephone',
                'status' => 1,
                'sort_order' => 0
            ]);
        } elseif (VERSION >= '4.0.1.0') {
            $this->model_setting_event->addEvent([
                'code' => 'rausch_foxpost_adminmenu',
                'description' => 'Rausch Foxpost Admin Menu',
                'trigger' => 'admin/view/common/column_left/before',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost|AddtoAdminMenu',
                'status' => 1,
                'sort_order' => 0
            ]);
            $this->model_setting_event->addEvent([
                'code' => 'rausch_shipping_foxpost',
                'description' => 'Rausch Foxpost',
                'trigger' => 'catalog/model/checkout/order/addHistory/after',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost|addOrder',
                'status' => 1,
                'sort_order' => 0
            ]);
            $this->model_setting_event->addEvent([
                'code' => 'rausch_shipping_foxpost_telephone_mask',
                'description' => 'Rausch Foxpost Telefon Mask',
                'trigger' => 'catalog/controller/account/register/validate/before',
                'action' => 'extension/rausch_foxpost/shipping/rausch_foxpost|validateTelephone',
                'status' => 1,
                'sort_order' => 0
            ]);
        } else {
            $this->model_setting_event->addEvent('rausch_foxpost_adminmenu', 'Rausch Foxpost Admin Menu', 'admin/view/common/column_left/before', 'extension/rausch_foxpost/shipping/rausch_foxpost|AddtoAdminMenu', 1, 0);
            $this->model_setting_event->addEvent('rausch_shipping_foxpost', 'Rausch Foxpost', 'catalog/model/checkout/order/addHistory/after', 'extension/rausch_foxpost/shipping/rausch_foxpost|addOrder');
            $this->model_setting_event->addEvent('rausch_shipping_foxpost_telephone_mask', 'catalog/controller/account/register/validate/before', 'extension/rausch_foxpost/shipping/rausch_foxpost|validateTelephone');
        }
            
        
        // Permission
        $this->load->model('user/user_group');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/rausch_foxpost/shipping/rausch_foxpost');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/rausch_foxpost/shipping/rausch_foxpost');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/rausch_foxpost/shipping/rausch_foxpost');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/rausch_foxpost/shipping/rausch_foxpost');
    }

    public function uninstall(): void {
        if ($this->user->hasPermission('modify', 'extension/rausch_foxpost/shipping/rausch_foxpost')) {
            $this->load->model('extension/rausch_foxpost/shipping/rausch_foxpost');

            $this->model_extension_rausch_foxpost_shipping_rausch_foxpost->uninstall();
            // Permission
            $this->load->model('user/user_group');
            $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/rausch_foxpost/shipping/rausch_foxpost');
            $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/rausch_foxpost/shipping/rausch_foxpost');
            
            $this->load->model('setting/event');
            // Remove Event
            $this->model_setting_event->deleteEventByCode('rausch_foxpost_adminmenu');
            $this->model_setting_event->deleteEventByCode('rausch_shipping_foxpost');
            $this->model_setting_event->deleteEventByCode('rausch_shipping_foxpost_telephone_mask');
        }
    }
    
    /**
     * 
     * @param type $route
     * @param array $data
     */
    public function AddtoAdminMenu(&$route, &$data){
        
        if ($this->user->hasPermission('access', 'extension/rausch_foxpost/shipping/rausch_foxpost')) {  
            $uj_menu = array();
            $this->load->language('extension/rausch_foxpost/shipping/rausch_foxpost');
            $link = [];
            $link[] = [
                    'name'      => $this->language->get('text_list'),
                    'href'      => $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost', 'user_token=' . $this->session->data['user_token'], true),
                    'children'  => []
            ];
            $link[] = [
                    'name'      => $this->language->get('text_orderlist'),
                    'href'      => $this->url->link('extension/rausch_foxpost/shipping/rausch_foxpost.orders', 'user_token=' . $this->session->data['user_token'], true),
                    'children'  => []
            ];

            foreach ($data['menus'] as $key => $menu) {             
                $uj_menu[] = $menu;
                if ($key == 0) {
                    $uj_menu[] = [
                        'id' => 'menu-rausch-foxpost',
                        'icon' => 'fa fa-plug',
                        'name' => $this->language->get('text_admin_menu'),
                        'href' => '',
                        'children' => $link
                    ];
                }
            }
            $data['menus'] = $uj_menu;
        }
    }
}
