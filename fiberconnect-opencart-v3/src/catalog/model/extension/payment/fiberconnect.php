<?php
/*
 * FiberConnect model
 */
include_once(dirname(__FILE__) . '/../fiberconnect/fiberconnect.php');

class ModelExtensionPaymentFiberConnect extends ModelExtensionFiberConnectFiberConnect
{
	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code = 'fiberconnect';

	/**
	 * this variable is title
	 *
	 * @var string $title
	 */
	protected $title = 'FRONTEND_PM_FIBERCONNECT';

	/**
	 * this variable is logo
	 *
	 * @var string $logo
	 */
	protected $logo = 'fiberconnect.png';
}
