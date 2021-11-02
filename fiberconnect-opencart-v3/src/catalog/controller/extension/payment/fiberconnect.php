<?php
/*
 * FiberConnect payment controller
 */
include_once(dirname(__FILE__) . '/../fiberconnect/fiberconnect.php');

class ControllerExtensionPaymentFiberConnect extends ControllerExtensionFiberConnect
{

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code = 'fiberconnect';

	/**
	 * this function is the constructor of ControllerFiberConnect class
	 *
	 * @return  void
	 */
	public function index()
	{

		return $this->confirmHtml();
	}
}
