<?php
//  Heading
$_['heading_title']                     = 'FiberConnect Payment';
$_['edit_title']                        = 'Edit ' . $_['heading_title'] . ' Plugin';
$_['text_extension']                    = 'Extension';
$_['text_fiberconnect']                 = '<img src="view/image/payment/fiberconnect.png" alt="' . $_['heading_title'] . '" title="' . $_['heading_title'] . '" style="border: 1px solid #EEEEEE;" />';
$_['tips_fiberconnect']                 = 'With FiberConnect Payment Plugin, you can collect payment in <b>HKD</b> via various payment methods and view all payment status auto updated into your FiberConnect account. <a href="https://fiberapi.com/en/contact-2/">Contact us</a> for more details!';
$_['text_success']                      = 'After installed the plugin, you need to go to plugin settings and input an API key that received from FiberConnect.';
$_['modify_success']                    = 'Success : You have modified ' . $_['heading_title'] . ' details.';


//  Title / Fields for each sections
//  -   1.  Connection Setting
$_['settings_connection']               = '1. Connection Setting - API Credentials';
$_['settings_connection_key']           = 'API Key';

//  -   2.  Basic Settings
$_['settings_basic']                    = '2. Basic Settings';
$_['settings_basic_name']               = 'Payment method name';
$_['settings_basic_name_tips']          = 'Checkout payment method name';
$_['settings_basic_status']             = 'Extension status';
$_['settings_basic_status_tips']        = 'Enable or disable this payment method';
$_['settings_basic_order']              = 'Sort Order';
$_['settings_basic_order_tips']         = 'Input a number to determine the order of this payment method in Checkout page';

//  -   3.  Order Settings (Not used until future design changed)
$_['settings_order']                    = '3. Order Settings';
$_['settings_order_progress']           = 'Transaction in-progress';
$_['settings_order_progress_tips']      = 'Payment transaction is created and not yet completed.';
$_['settings_order_successful']         = 'Payment successful';
$_['settings_order_successful_tips']    = 'Payment transaction is completed.';
$_['settings_order_unsuccessful']       = 'Payment unsuccessful';
$_['settings_order_unsuccessful_tips']  = 'Payment transaction failed due to technical or payment method issues (i.e. system is busy)';
$_['settings_order_cancelled']          = 'Payment cancelled';
$_['settings_order_cancelled_tips']     = 'Payment transaction failed due to customer\'s action (i.e. payment transaction is expired)';
$_['settings_order_rejected']           = 'Payment rejected';
$_['settings_order_rejected_tips']      = 'Payment transaction failed due to credit card issue.';

//  -   4.  Payment Method
$_['settings_payment']                  = '3. Payment Method';
$_['settings_payment_message']          = 'Your supported payment method(s) on FiberConnect payment page will be retrieved from your available payment method(s) in FiberConnect account > Profile > Payment Method page.';


//  Error handling
$_['error_permission_required']         = 'Warning : You do not have permission to modify ' . $_['heading_title'];
$_['error_field_required']              = 'This is required field.';
$_['error_api_key_invalid']             = 'The inputted API key is invalid.';
