<?php

/*
 * FiberConnect controller
 */

include_once(dirname(__FILE__) . '/fiberconnectapi.php');

class ControllerExtensionFiberConnect extends Controller
{
	/**
	 * this variable is Version
	 *
	 * @var string $version
	 */
	protected $version = '1.0.0';

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code='';

	/**
	 * this variable is payment type
	 *
	 * @var string $payment_type
	 */
	protected $payment_type = 'DB';

	/**
	 * this variable is logo
	 *
	 * @var string $logo
	 */
	protected $logo = '';

	/**
	 * this function is the constructor of ControllerFiberConnect class
	 *
	 * @return  void
	 */
	public function index()
	{
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirments.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}
			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->language->load('extension/payment/fiberconnect');

		$this->initApi();

		$payment_widget_url = $this->getPaymentUrl();
		$this->response->redirect($payment_widget_url);
	}

	/**
	 * To load the confirm view
	 *
	 * @return  void
	 */
	public function confirmHtml()
	{
		$this->language->load('extension/payment/fiberconnect');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = $this->url->link('extension/' . $this->code . '/checkout/' . $this->code, '', true);

		return $this->load->view('extension/payment/fiberconnect/confirm', $data);
	}

	/**
	 * Get a payment response then redirect to the payment success page or the payment error page.
	 *
	 * @return  void
	 */
	public function callback()
	{
		$this->load->model('extension/fiberconnect/fiberconnect');
		$this->load->model('checkout/order');

		$response_body	= file_get_contents('php://input');
		$response		= json_decode($response_body, true);
		$payment_id		= $response['event_body']['id'];
		$payment_status	= $response['event_type'];
		$order_id		= $this->model_extension_fiberconnect_fiberconnect->getOrderFromPaymentId( $payment_id );

		$this->model_extension_fiberconnect_fiberconnect->log('callback - response body : ' . print_r($response, true));

		//	Now map the actual status from $payment_status to $status_code.
		$status_id		= '';
		switch ($payment_status) {
			case 'payment_request.created':
				$status_id	= 'pending';
				break;

			case 'payment_request.paid':
				$status_id	= 'processing';
				break;

			case 'payment_request.charge_failed':
			case 'payment_request.cancelled':
				$status_id	= 'failed';
				break;

			default:
				break;
		}
		$status_code	= $this->config->get( 'payment_' . $this->code . '_' . $status_id . '_status_id' );

		//	Now write record to DB.
		$comment		= 'Payment status updated to: ' . $status_id;
		$this->model_extension_fiberconnect_fiberconnect->updateOrderStatus($order_id, $status_id);
		$this->model_checkout_order->addOrderHistory($order_id, $status_code, $comment, '', true);
	}

	/**
	 * Get languages code
	 *
	 * @return string
	 */
	function getLangCode()
	{
		switch (substr($this->session->data['language'], 0, 2)) {
			case 'zh':
				$lang_code = "zh";
				break;
			default:
				$lang_code = "en";
				break;
		}
		return $lang_code;
	}

	/**
	 * Get a payment type
	 *
	 * @return  string
	 */
	function getPaymentType()
	{
		return $this->payment_type;
	}

	/**
	 * Get a customer ip
	 *
	 * @return  string
	 */
	function getCustomerIp()
	{
		if ($_SERVER['REMOTE_ADDR'] == '::1') {
			return "127.0.0.1";
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get a template
	 *
	 * @return  string
	 */
	function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Get payment widget at checkout payment page
	 *
	 * @return  string
	 */
	public function getPaymentUrl()
	{
		$this->load->model('extension/fiberconnect/fiberconnect');
		$this->load->model('checkout/order');

		//	Fetch order related data, and update on DB.
		$order_id		= $this->session->data['order_id'];
		$order_info 	= $this->model_checkout_order->getOrder($order_id);
		$currency   	= $order_info['currency_code'];
		$amount     	= $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$transaction_id = 'oc-' . $order_id;
		$this->model_extension_fiberconnect_fiberconnect->updateFiberConnectData($order_id, 'transaction_id', $transaction_id);
		$this->model_extension_fiberconnect_fiberconnect->log('FiberConnect Order Info : ' . print_r($order_info, true));

		//	Define a helper function for converting line item.
		function item_to_line( $item ) {
			return array(
				"description"	=>	$item['name'],
				"name"			=>	$item['name'],
				"reference"		=>	(isset($item['sku']) && $item['sku'] !== '-') ? $item['sku'] : $item['name'],
				"quantity"		=>	$item['qty'],
				"price"			=>	$item['unit_price']
			);
		};

		//	Define a helper function for filtering not available payment methods.
		function filter_activated_methods( $item ) {
			return $item['status'] === 'activated';
		};

		//	Define a helper function for reducing an available payment method from object to simple form.
		function map_reduce_methods( $item ) {
			return $item['id'];
		};

		//	Get all payment methods allowed.
		//	Steps needed -
		//	1.	Use endpoint call to see all available methods.
		//	2.	Reduce array by `array_filter` to get all activated methods.
		//	3.	Map array by `array_map` to get an array of methods in key-value pair form.
		//	4.	Reduce array by removing all keys with `array_values`.
		$this->model_extension_fiberconnect_fiberconnect->log( print_r(FiberConnectApi::getPaymentMethods(), 1) );
		$list_payment_methods		= json_decode( FiberConnectApi::getPaymentMethods(), 1 );
		$activated_payment_methods	= array_filter( $list_payment_methods['payment_methods'], 'filter_activated_methods' );
		$reduced_payment_methods	= array_map( 'map_reduce_methods', $activated_payment_methods );
		$payment_methods			= array_values( $reduced_payment_methods );

		//	Now make actual payment request.
		$body = array(
			"reference"	=>	'#' . strval($order_id) . ' ' . $order_info['firstname'] . ' ' . $order_info['lastname'],
			"payment_method_types"	=>	$payment_methods,
			"amount"	=>	array(
				"currency"	=>	$currency,
				// "currency"	=>	'HKD',
				"value"		=>	$amount
				// "value"		=>	0.81
			),
			"line_items"	=>	array_map( 'item_to_line', array_values( $this->getCartItems() ) ),
			"payment_method_options"	=>	array(
				"gw_url"	=>	array(
					"success_callback_url"	=>	$this->url->link('checkout/success', '', true),
					"fail_callback_url"		=>	$this->url->link('checkout/checkout', '', true)
				)
			),
			"email"		=>	$order_info['email'],
			"metadata"	=>	array(
				"usage_type"	=>	"opencart"
			)
		);
		$post_link = json_decode( FiberConnectApi::generatePosLink($body), 1 );

		$this->model_extension_fiberconnect_fiberconnect->log('Response Link : ' . print_r($post_link, 1));

		//	At last, see if we need to redirect user.
		if (isset($post_link['gw_url'])) {
			//	Create a new record on Orders list.
			$fiberconnect_order_data = [
				'order_id'		=>	$order_id,
				'payment_id'	=>	$post_link['id'],
				'amount'		=>	$amount,
				'currency'		=>	$currency,
				'result'		=>	'pending'
			];
			$order_status_id	= $this->config->get( 'payment_fiberconnect_pending_status_id' );
			$comment			= 'Payment ID: ' . $post_link['id'];
			$this->model_extension_fiberconnect_fiberconnect->saveOrder($fiberconnect_order_data);
			$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, '', true);

			//	Ready to redirect user now.
			return $post_link['gw_url'];
		} else {
			//	Generate link failed, show error message.

			//	TODO - possibly needed to change error message format
			if (isset($post_link['error_details']) && isset($post_link['error_details']['body'][0]['message'])) {
				if ($post_link['error_details']['body'][0]['message'] == '"amount.currency" must be [HKD]') {
					$this->redirectError('Currency amount must be HKD.');
				} else {
					$this->redirectError($post_link['error_description']);
				}
			} else if (isset($post_link['message'])) {
				$this->redirectError($post_link['message']);
			} else {
				$this->redirectError($post_link['error_description']);
			}
		}
	}

	/**
     * redirect to the error message page or the failed message page
     *
     * @param string $error_identifier
     * @return  void
     */
    public function redirectError($error_identifer)
    {
        $this->language->load('extension/payment/fiberconnect');
        $this->session->data['error'] = $error_identifer;
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

	/**
	 * Init the API class and set the API key.
	 */
	protected function initApi() {
		FiberConnectApi::$api_key = $this->config->get('payment_fiberconnect_api_key');
	}

	/**
	 * Get cart items
	 *
	 * @return  array
	 */
	function getCartItems()
	{
		$this->load->model('account/order');
		$this->load->model('catalog/product');
		$this->load->model('extension/fiberconnect/fiberconnect');

		$cart = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
		$cart_items = array();
		$i = 0;
		foreach ($cart as $item) {
			$product = $this->model_catalog_product->getProduct($item['product_id']);
			$cart_items[$i]['qty'] = (int)$item['quantity'];
			$cart_items[$i]['name'] = $item['name'];

			$item_tax = (float)$item['tax'];
			$item_price = (float)$item['price'];

			if ($product['sku'] == '') {
				$sku = '-';
			} else {
				$sku = $product['sku'];
			}

			if (!isset($item_price)) {
				$unit_price = 0;
			} else {
				$unit_price = $item_price + $item_tax;
			}
			
			$cart_items[$i]['sku'] = $sku;
			$cart_items[$i]['unit_price'] = $unit_price;

			$discount_price = (float)$product['price'];
			$special_price = (float)$product['special'];
			$product_price = (float)$this->model_extension_fiberconnect_fiberconnect->getProductPrice($item['product_id']);
			$i = $i+1;
		}
		return $cart_items;
	}
}
