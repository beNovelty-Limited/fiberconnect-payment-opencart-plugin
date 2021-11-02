<?php
/*
 * FiberConnect controller in admin
 */

include_once(dirname(__FILE__, 5) . '/catalog/controller/extension/fiberconnect/fiberconnectapi.php');

class ControllerExtensionPaymentFiberConnect extends Controller {

	/**
	 * this variable is the error
	 *
	 * @var array $error
	 */
	private $error = array();

	/**
	 * this variable is the keys
	 *
	 * @var array $keys
	 */
	private $keys = array(
		'api_key',

		'title',
		'status',
		'sort_order',
	);

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	private $code = 'fiberconnect';


	/**
	 * this function is the constructor of ControllerExtensionPaymentFiberConnect class
	 *
	 * @return  void
	 */
	public function index() {
		$this->load->language('extension/payment/' . $this->code);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/payment/fiberconnect');

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$lang_id = $this->model_extension_payment_fiberconnect->getLangId();

			foreach ($data['order_statuses'] as $key => $value) {
				if (isset($lang_id['en'])) {
					if ($this->config->get('config_language_id') == $lang_id['en']) {
						if ($value['name'] == "Processing") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Failed") {
							$order_statuses['payment_' . $this->code . '_failed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Pending") {
							$order_statuses['payment_' . $this->code . '_pending_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['zh'])) {
					if ($this->config->get('config_language_id') == $lang_id['zh']) {
						if ($value['name'] == "正在处理") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "失败") {
							$order_statuses['payment_' . $this->code . '_failed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "已退款") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
			}

			$config_post = array_merge($this->request->post, $order_statuses);
			$this->model_setting_setting->editSetting('payment_' . $this->code, $config_post);

			$this->session->data['success'] = $this->language->get('modify_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		// $data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['custom_fields'] = $this->model_extension_payment_fiberconnect->getCustomFields();
		$data['custom_field_values'] = $this->model_extension_payment_fiberconnect->getCustomFieldValues();

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$error_checklist = array(
			'warning',
			'api_key',
			'title'
		);

		foreach ($error_checklist as $item) {
			if (isset($this->error[$item])) {
				$data['error_' . $item] = $this->error[$item];
			} else {
				$data['error_' . $item] = '';
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/' . $this->code, 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/payment/' . $this->code, 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		foreach ($this->keys as $value) {
			$key = 'payment_' . $this->code . '_' . $value;
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/' . $this->code, $data));
	}

	/**
	 * to install the payment method
	 *
	 * @return  void
	 */
	public function install() {
		$this->load->language('extension/payment/' . $this->code);

		$this->load->model('setting/setting');
		$this->load->model('extension/payment/fiberconnect');
		$this->model_extension_payment_fiberconnect->addCustomOrderStatuses();
		$this->model_extension_payment_fiberconnect->install();
	}

	/**
	 * to validate another field in the payment method configuration
	 *
	 * @return  boolean
	 */
	public function validate() {
		//	First check if we have permission.
		//	Return false if we don't have enough permission anyway.
		if (!$this->user->hasPermission('modify', 'extension/payment/fiberconnect')) {
			$this->error['warning'] = $this->language->get('error_permission_required');
			return false;
		}

		//	Next check if fields are empty.
		//	Return false if either of them is empty.
		if (empty($this->request->post['payment_fiberconnect_api_key'])) {
			$this->error['api_key'] = $this->language->get('error_field_required');
		}
		if (empty($this->request->post['payment_fiberconnect_title'])) {
			$this->error['title'] = $this->language->get('error_field_required');
		}
		if ($this->error) {
			return false;
		}

		//	Afterwards, check if API key is working.
		//	Return false if it is invalid.
		$api = new FiberConnectAPI();
		if (!$api->verifyAPIKey($this->request->post['payment_fiberconnect_api_key'])) {
			$this->error['api_key'] = $this->language->get('error_api_key_invalid');
			return false;
		}

		//	By this point we have checked all possible errors.
		//	Return true as it should be error-free.
		return true;
	}
}
