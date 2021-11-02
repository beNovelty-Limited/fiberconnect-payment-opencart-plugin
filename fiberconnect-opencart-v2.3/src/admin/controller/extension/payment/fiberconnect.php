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
							$order_statuses[$this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Failed") {
							$order_statuses[$this->code . '_failed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Pending") {
							$order_statuses[$this->code . '_pending_status_id'] = $value['order_status_id'];
						}
					}
				}
				// if (isset($lang_id['zh'])) {
				// 	if ($this->config->get('config_language_id') == $lang_id['zh']) {
				// 		if ($value['name'] == "正在处理") {
				// 			$order_statuses[$this->code . '_processing_status_id'] = $value['order_status_id'];
				// 		}
				// 		if ($value['name'] == "失败") {
				// 			$order_statuses[$this->code . '_failed_status_id'] = $value['order_status_id'];
				// 		}
				// 		if ($value['name'] == "已退款") {
				// 			$order_statuses[$this->code . '_refund_status_id'] = $value['order_status_id'];
				// 		}
				// 	}
				// }
			}

			$config_post = array_merge($this->request->post, $order_statuses);
			$this->model_setting_setting->editSetting($this->code, $config_post);
			// $this->model_setting_setting->editSetting('fps_transfer', $this->request->post);

			// echo 'we saved it';
			// $this->model_setting_setting->editSetting('fiberconnect', $config_post);
			// $this->model_setting_setting->editSetting('payment_fiberconnect', $config_post);

			$this->session->data['success'] = $this->language->get('modify_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', 'SSL'));
		}

		$data['custom_fields'] = $this->model_extension_payment_fiberconnect->getCustomFields();
		$data['custom_field_values'] = $this->model_extension_payment_fiberconnect->getCustomFieldValues();

		//	Set language translation. (Only needed for OpenCart 2.3)
		//	======
		$fetch_list = array(
			//	Some other items not defined by us
			'text_enabled',
			'text_disabled',
			'button_save',
			'button_cancel',

			//	Heading
			'heading_title',
			'edit_title',
			'text_extension',
			'text_fiberconnect',
			'tips_fiberconnect',
			'text_success',
			'modify_success',

			//  Title / Fields for each sections
			//  -   1.  Connection Settings
			'settings_connection',
			'settings_connection_key',

			//	-	2.	Basic Settings
			'settings_basic',
			'settings_basic_name',
			'settings_basic_name_tips',
			'settings_basic_status',
			'settings_basic_status_tips',
			'settings_basic_order',
			'settings_basic_order_tips',

			//	-	3.	Payment Method
			'settings_payment',
			'settings_payment_message',

			//	Error handling',
			'error_permission_required',
			'error_field_required',
			'error_api_key_required'
		);

		foreach ($fetch_list as $index) {
			$data[$index] = $this->language->get($index);
		}
		//	======
		//	End of set language translation part.

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
			'href'      => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', 'SSL'),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/' . $this->code, 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/payment/' . $this->code, 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		foreach ($this->keys as $value) {
			$key = $this->code . '_' . $value;
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
			echo $key;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/' . $this->code . '.tpl', $data));
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
		if (empty($this->request->post['fiberconnect_api_key'])) {
			$this->error['api_key'] = $this->language->get('error_field_required');
		}
		if (empty($this->request->post['fiberconnect_title'])) {
			$this->error['title'] = $this->language->get('error_field_required');
		}
		if ($this->error) {
			return false;
		}

		//	Afterwards, check if API key is working.
		//	Return false if it is invalid.
		$api = new FiberConnectAPI();
		if (!$api->verifyAPIKey($this->request->post['fiberconnect_api_key'])) {
			$this->error['api_key'] = $this->language->get('error_api_key_invalid');
			return false;
		}

		//	By this point we have checked all possible errors.
		//	Return true as it should be error-free.
		return true;
	}
}
