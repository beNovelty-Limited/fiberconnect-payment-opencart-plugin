<?php
/**
 * Handles POS Link, Refunds and other API requests.
 */
class FiberConnectApi
{

	/**
	 * API Key
	 *
	 * @var string
	 */
	public static $api_key;

	/**
	 * Retrieve api url.
	 */
	public static function getApiUrl() {
		include 'env.php';
		return $api_url;
	}

	/**
	 * Send request to the API
	 *
	 * @param string $url Url.
	 * @param array  $body Body.
	 * @param string $method Method.
	 *
	 * @return array
	 */
	public static function sendRequest($url, $body = '', $method = 'GET', $key = '')
	{
		if ($key === '') {
			$key = self::$api_key;
		}

		$headers = array(
			"X-OpenAPIHub-Key: " . $key
		);
		if ('POST' === $method || 'PUT' === $method) {
			array_push($headers, "content-type: application/json");
		}

		$data = json_encode($body);
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_CUSTOMREQUEST => strtoupper($method),
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_TIMEOUT => 70,
		  CURLOPT_SSL_VERIFYPEER => false,
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		}

		return $response;
	}

	/**
	 * Verify if a key is valid with the API.
	 *
	 * @param string $key the api key to test.
	 *
	 * @return boolean
	 */
	public static function verifyAPIKey($key)
	{
		$url = self::getApiUrl() . '/v1/payment-requests/a';
		$result = self::sendRequest($url, '', 'GET', $key);

		$decoded_result = json_decode($result, true);
		return !isset( $decoded_result['message'] );
	}

	/**
	 * Get pos link url with the API.
	 *
	 * @param array $body Body.
	 *
	 * @return array
	 */
	public static function generatePosLink($body)
	{
		$url = self::getApiUrl() . '/v1/payment-requests';
		return self::sendRequest($url, $body, 'POST');
	}

	/**
	 * Get available payment methods with the API.
	 *
	 * @return array
	 */
	public static function getPaymentMethods()
	{
		$url = self::getApiUrl() . '/v1/payment-methods';
		return self::sendRequest($url, '', 'GET');
	}

	/**
	 * Do refund with the API.
	 *
	 * @param string $payment_id Payment ID.
	 * @param array  $body Body.
	 *
	 * @return array
	 */
	public static function doRefund($payment_id, $body)
	{
		$url = self::getApiUrl() . '/refunds/' . $payment_id;
		return self::sendRequest($url, $body, 'POST');
	}


	/**
	 * Get payment detail with the API.
	 *
	 * @param string $payment_id Payment ID.
	 *
	 * @return array
	 */
	public static function getPayment($payment_id)
	{
		$url = self::getApiUrl() . '/payments/' . $payment_id;
		return self::send_request($url);
	}
}
