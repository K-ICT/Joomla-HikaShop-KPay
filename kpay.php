<?php
/**
 * @package	KPay for HikaShop
 * @version	1.0.0
 * @author	www.k-ict.org
 * @copyright	(C) 2017 Konsortium ICT Pantai Timur. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentKpay extends hikashopPaymentPlugin
{ # Class plgHikashoppaymentKpay start.
	var $accepted_currencies=array(
		'MYR',
	);

	var $multiple=true;
	var $name='kpay';
	var $doc_form='kpay';

	function __construct(&$subject,$config)
	{ # The class constructor start.
		if(!file_exists(JPATH_SITE.'/media/com_hikashop/images/payment/FPX.jpg'))
		rename(JPATH_PLUGINS.'/hikashoppayment/kpay/FPX.jpg',JPATH_ROOT.'/media/com_hikashop/images/payment/FPX.jpg');
		
		parent::__construct($subject,$config);
		$this->KPay_name='KPay';
		$this->KPay_provider="https://www.k-ict.org/";
		$this->KPay_url=$this->KPay_provider."kpg/";
		$this->KPay_payment_url=$this->KPay_url."payment.php";
		$this->KPay_receipt_url=$this->KPay_url."receipt.php";
		$this->API_url=$this->KPay_url."API.php";
		$this->API_client_name="KPG";
		$this->API_client_type="APIclient";
		$this->API_client_version="v1.1";
		$this->API_user_agent=$this->API_client_name." ".$this->API_client_type." ".$this->API_client_version;
		
		if(!isset(JRequest::getVar('PortalKey')))
		{ # Do process on normal request by excluding the POST portalKey from KPG server start.
			# Check all orders with 'Created' and 'Pending' status for KPay payment method start.
			$db=JFactory::getDbo();
			$query=$db->getQuery(true);
			$query->select('order_id');
			$query->from($db->quoteName('#__hikashop_order'));
			$query->where("(".strtolower($db->quoteName('order_status'))."='created' or ".strtolower($db->quoteName('order_status'))."='pending') and ".$db->quoteName('order_payment_method')."='".strtolower($this->KPay_name)."'");
		
			$db->setQuery($query);
			$records=$db->loadColumn();
		
			foreach($records as $records_index=>$records_value)
			{ # Loop each result and proceed with the checking start.
				$dbOrder=$this->getOrder((int)@$records_value);
				$this->loadPaymentParams($dbOrder);
			
				$order_id=$dbOrder->order_id;
			
				# Perform remote checking at KPG server start.
				# Define the API data.
				$KPay_API_data=array(
				"UserLoginID"=>$this->payment_params->kpay_user_loginid,
				"UserPassword"=>$this->payment_params->kpay_user_password,
				"Category"=>"getTransactionDetailsByOrderNumber",
				"PortalKey"=>$this->payment_params->kpay_portal_key,
				"OrderNumber"=>$order_id,
				);
		
				# Perform API operations.
				$KPay_API_operations=$this->KPay_API_operations($KPay_API_data);
		
				$KPay_API_operations_result=$KPay_API_operations['Result'];
				$KPay_order_transaction_status=$KPay_API_operations['TransactionStatus'];
			
				if($KPay_order_transaction_status=='Successful' || $KPay_order_transaction_status=='Unsuccessful')
				{ # Do process transactions those are not pending start.
					# Define the current order status.
					$order_status_current=$dbOrder->order_status;
				
					if($KPay_order_transaction_status=='Successful')
					$order_status_new=$this->payment_params->verified_status;
					else
					$order_status_new=$this->payment_params->invalid_status;
				
					if($order_status_current!=$order_status_new)
					{ # Do update order if the status changed start.
						$history->notified=1;
						$this->modifyOrder($order_id,$order_status_new,true);
					} # Do update order if the status changed end.
					# Do nothing if the status was unchanged.
				} # Do process transactions those are not pending end.
				# Otherwise, do not process pending transaction.
			
				# Perform remote checking at KPG server end.
			} # Loop each result and proceed with the checking end.
		
			# Check all orders with 'Created' and 'Pending' status for KPay payment method end.
		} # Do process on normal request by excluding the POST portalKey from KPG server end.
	} # The class constructor end.

	function onBeforeOrderCreate(&$order,&$do)
	{ # Method to be executed before creating an order start.
		if(parent::onBeforeOrderCreate($order,$do)===true)
			return true;
		
		if(empty($this->payment_params->kpay_user_loginid) || empty($this->payment_params->kpay_user_password) || empty($this->payment_params->kpay_portal_key))
		{ # Check for the current KPay configuration error start.
			$this->app->enqueueMessage('<div style="color:#a00;padding:10px;background:#fff;border-radius:5px;">Error notes : NULL_KPay_Config<br>Sorry, there was a problem with '.$this->KPay_name.' plugin configuration.<br>If you are the shopper, please contact the seller.<br>Otherwise please check your plugin configuration.</div>');
			$do=false;
		} # Check for the current KPay configuration error end.
		else
		{ # Security features : Check for the supplied configuration validity from the KPG server start.
			# Define the API data.
			$KPay_API_data=array(
			"UserLoginID"=>$this->payment_params->kpay_user_loginid,
			"UserPassword"=>$this->payment_params->kpay_user_password,
			"Category"=>"getSellerDetails",
			"PortalKey"=>$this->payment_params->kpay_portal_key,
			);
			
			# Perform API operations.
			$KPay_API_operations=$this->KPay_API_operations($KPay_API_data);
			
			$KPay_API_operations_result=$KPay_API_operations['Result'];
			
			if($KPay_API_operations_result=='Error')
			{ # Display a friendly error message start.
				$this->app->enqueueMessage('<div style="color:#a00;padding:10px;background:#fff;border-radius:5px;">Error notes : Error_KPay_API_Response<br>Sorry, invalid seller information detected.<br>If you are the shopper, please contact the seller.<br>Otherwise please check your plugin configuration.</div>');
				$do=false;
			} # Display a friendly error message end.
		} # Security features : Check for the supplied configuration validity from the KPG server end.
	} # Method to be executed before creating an order end.

	function onAfterOrderConfirm(&$order,&$methods,$method_id)
	{ # Method to be executed during the order submission to KPay start.
		parent::onAfterOrderConfirm($order,$methods,$method_id);
		
		if($this->currency->currency_locale['int_frac_digits']>2)
			$this->currency->currency_locale['int_frac_digits']=2;

		$notify_url=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$return_url=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$cancel_url=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;

		$tax_total='';
		$discount_total='';

		$vars=array(
			'currency_code'=>$this->currency->currency_code,
			'charset'=>'utf-8',
		);
		
		$this->payment_params->url=$this->KPay_payment_url;
		
		$vars=array(
		"portal_key"=>$this->payment_params->kpay_portal_key,
		"order_no"=>preg_replace("/[^0-9]/","",$order->order_number),
		"amount"=>number_format($order->order_full_price,2,'.',''),
		"description"=>"Order no. ".$order->order_number,
		"buyer_name"=>$order->customer->name,
		"buyer_tel"=>$order->cart->billing_address->address_telephone,
		"buyer_email"=>$order->customer->email,
		);
		
		$this->vars=$vars;		
		return $this->showPage('end');
	} # Method to be executed during the order submission to KPay end.

	function onPaymentNotification(&$statuses)
	{ # Method to be executed when listening for the transaction status responses from KPay start.
		$vars=array();
		$data=array();
		$filter=JFilterInput::getInstance();
		
		foreach(JRequest::get('post') as $key=>$value)
		{
			$key=$filter->clean($key);
			$value=JRequest::getString($key);
			$vars[$key]=$value;
			$data[]=$key.'='.$value;
		}
		
		$dbOrder=$this->getOrder((int)@$vars['orderNo']);
		$this->loadPaymentParams($dbOrder);
		
		# Security features : Attempt to check this order at KPay server start.
		if($vars['portalKey']==$this->payment_params->kpay_portal_key)
		{ # Proceed only on matched portalKey start.
			# Define the API data.
			$KPay_API_data=array(
			"UserLoginID"=>$this->payment_params->kpay_user_loginid,
			"UserPassword"=>$this->payment_params->kpay_user_password,
			"Category"=>"getTransactionDetailsByOrderNumber",
			"PortalKey"=>$this->payment_params->kpay_portal_key,
			"OrderNumber"=>$vars['orderNo'],
			);
			
			# Perform API operations.
			$KPay_API_operations=$this->KPay_API_operations($KPay_API_data);
			
			$KPay_API_operations_result=$KPay_API_operations['Result'];
		} # Proceed only on matched portalKey end.
		# Security features : Attempt to check this order at KPay server end.
		
		if($KPay_API_operations_result=='OK')
		{ # Do only proceed if the API result was OK start.
			$this->loadOrderData($dbOrder);
			
			$order_id=$dbOrder->order_id;
			
			if(!empty($KPay_API_operations['TransactionStatus']))
			$KPay_order_transaction_status=$KPay_API_operations['TransactionStatus'];
			else
			$KPay_order_transaction_status=null;
			
			if($KPay_order_transaction_status!='Pending')
			{ # Do process transactions those are not pending start.
				# Define the current order status.
				$order_status_current=$dbOrder->order_status;
				
				if($KPay_order_transaction_status=='Successful')
				{
					$order_status_new=$this->payment_params->verified_status;
					$this->removeCart=true;
				}
				else
				$order_status_new=$this->payment_params->invalid_status;
				
				if($order_status_current!=$order_status_new)
				{ # Do update order if the status changed start.
					$history->notified=1;
					$this->modifyOrder($order_id,$order_status_new,true);
				} # Do update order if the status changed end.
				# Do nothing if the status was unchanged.
				
				# Redirect to the KPay transaction receipt page.
				header('location:'.$this->KPay_receipt_url.'?txnId='.JRequest::getVar('txnId').'&txnEx='.JRequest::getVar('txnEx'));
				
				return true;
			} # Do process transactions those are not pending end.
			else
			{ # Do not process pending transaction start.
				$order_status_new=$this->payment_params->pending_status;
				return false;
			} # Do not process pending transaction end.
			return true;			
		} # Do only proceed if the API result was OK end.
		else
		{ # Display error message if the API result was Error start.
			echo 'Portal Key : '.$_REQUEST['portalKey'].'<br>Order Number : '.$_REQUEST['orderNo'].'<br>Transaction ID : '.$_REQUEST['txnEx'].'<hr>';
			echo '<div style="font-weight:bold;color:#a00;">Error notes : '.$this->KPay_name.'_API_result_error<br>Sorry, an error has occurred.<br>If you are the visitor, please contact your seller.<br>Otherwise please take a screenshot and email to the <a href="mailto:support@k-ict.org?Subject='.$this->KPay_name.'_API_result_error-'.$_REQUEST['portalKey'].'">developer</a>.<br>We are sorry for the inconvenience caused.</div>';
			exit();
			return false;
		} # Display error message if the API result was Error end.
	} # Method to be executed when listening for the transaction status responses from KPay end.

	function onPaymentConfiguration(&$element)
	{ # Method to be executed in the back-end configuration pages start.
		$subtask=JRequest::getCmd('subtask','');
		parent::onPaymentConfiguration($element);
	} # Method to be executed in the back-end configuration pages end.

	function onPaymentConfigurationSave(&$element)
	{ # Method to be executed when saving the configuration start.
		if(!empty($element->payment_params->ips))
			$element->payment_params->ips=explode(',',$element->payment_params->ips);

		if(empty($element->payment_params->kpay_user_loginid))
		{
			$app=JFactory::getApplication();
			$app->enqueueMessage('<div style="color:#a00;">Please fill in your '.$this->KPay_name.' User Login ID.</div>');
			return false;
		}
		if(empty($element->payment_params->kpay_user_password))
		{
			$app=JFactory::getApplication();
			$app->enqueueMessage('<div style="color:#a00;">Please fill in your '.$this->KPay_name.' User Password.</div>');
			return false;
		}
		if(empty($element->payment_params->kpay_portal_key))
		{
			$app=JFactory::getApplication();
			$app->enqueueMessage('<div style="color:#a00;">Please fill in your '.$this->KPay_name.' Portal Key.</div>');
			return false;
		}
		
		return true;
	} # Method to be executed when saving the configuration end.

	function getPaymentDefaultValues(&$element)
	{ # Method to set the default parameters value start.		
		$element->payment_name='FPX';
		$element->payment_description='You can pay via Malaysia Internet Banking.';
		$element->payment_images=$element->payment_name;
		$element->payment_params->url=$this->KPay_payment_url;
		$element->payment_params->notification=1;
		$element->payment_params->ips='';
		$element->payment_params->details=1;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->address_override=1;
	} # Method to set the default parameters value end.
	
	public function KPay_API_cURL($KPay_API_data)
	{ # Function to connect to KPay API start.
		# Fetch the configuration data.
		$KPay_API_url=$this->API_url;
		$KPay_API_client_name=$this->API_client_name;
		$KPay_API_client_type=$this->API_client_type;
		$KPay_API_client_version=$this->API_client_version;
		$KPay_API_user_agent=$this->API_user_agent;

		# Fetch the API data.
		$KPay_login_id=$KPay_API_data["UserLoginID"];
		$KPay_password=$KPay_API_data["UserPassword"];
		$KPay_API_category=$KPay_API_data["Category"];
		$KPay_portal_key=$KPay_API_data["PortalKey"];
		$KPay_API_order_number=$KPay_API_data["OrderNumber"];

		# Use API call getTransactionDetailsByOrderNumber start.
		$KPay_API_data=array(
		"UserLoginID"=>rawurlencode($KPay_login_id),
		"UserPassword"=>rawurlencode($KPay_password),
		"Category"=>rawurlencode($KPay_API_category),
		"PortalKey"=>rawurlencode($KPay_portal_key),
		"OrderNumber"=>rawurlencode($KPay_API_order_number),
		);
		# Use API call getTransactionDetailsByOrderNumber end.

		# Count number of data to be POSTed.
		$KPay_API_data_count=count($KPay_API_data);

		$KPay_API_data_fields=""; # Initialize the data to be POSTed.
		foreach($KPay_API_data as $KPay_API_data_key=>$KPay_API_data_value)
		$KPay_API_data_fields.=$KPay_API_data_key.'='.$KPay_API_data_value.'&';
		rtrim($KPay_API_data_fields,'&');

		# cURL section start.
		$KPay_curl_output="";
		$KPay_curl=curl_init();
		curl_setopt($KPay_curl,CURLOPT_URL,$KPay_API_url);
		curl_setopt($KPay_curl,CURLOPT_USERAGENT,$KPay_API_user_agent);
		curl_setopt($KPay_curl,CURLOPT_POST,true);
		curl_setopt($KPay_curl,CURLOPT_POSTFIELDS,$KPay_API_data_fields);
		curl_setopt($KPay_curl,CURLOPT_RETURNTRANSFER,true);
		$KPay_curl_output=curl_exec($KPay_curl);
		curl_close($KPay_curl);
		# cURL section end.
		
		return $KPay_curl_output;
	} # Function to connect to KPay API end.

	public function KPay_API_cURL_response($KPay_curl_output)
	{ # Function to fetch the KPay API response start.
		# Decode JSON output to PHP object.
		$KPay_curl_output_object=json_decode($KPay_curl_output);

		# Initialize the output variables.
		$KPay_curl_output_reason="";
		$KPay_transaction_id="";
		$KPay_transaction_status="";
		$KPay_transaction_description="";
		$KPay_FPX_transaction_id="";
		$KPay_curl_output_result="";
		$KPay_curl_output_data_mode="";
		$KPay_curl_seller_name="";

		foreach($KPay_curl_output_object as $KPay_curl_output_object_data=>$KPay_curl_output_object_value)
		{ # Loop through each object start.

			if(is_object($KPay_curl_output_object_value))
			{ # If the return value is sub-object, loop through each sub-object start.

				foreach($KPay_curl_output_object_value as $KPay_curl_output_data=>$KPay_curl_output_value)
					{ # Fetch specific API response data start.
					if($KPay_curl_output_data=="Reason")
					$KPay_curl_output_reason=$KPay_curl_output_value;
					if($KPay_curl_output_data=="OrderNumber")
					$KPay_order_number=$KPay_curl_output_value;
					if($KPay_curl_output_data=="TransactionID")
					$KPay_transaction_id=$KPay_curl_output_value;
					if($KPay_curl_output_data=="TransactionStatus")
					$KPay_transaction_status=$KPay_curl_output_value;
					if($KPay_curl_output_data=="TransactionDescription")
					$KPay_transaction_description=$KPay_curl_output_value;
					if($KPay_curl_output_data=="FPXTransactionID")
					$KPay_FPX_transaction_id=$KPay_curl_output_value;
					if($KPay_curl_output_data=="BusinessName")
					$KPay_curl_seller_name=$KPay_curl_output_value;
					} # Fetch specific API response data end.

			} # If the return value is sub-object, loop through each sub-object end.
			else
			{ # Display normal object output start.

				if($KPay_curl_output_object_data=="Result")
				$KPay_curl_output_result=$KPay_curl_output_object_value;
				if($KPay_curl_output_object_data=="DataMode")
				$KPay_curl_output_data_mode=$KPay_curl_output_object_value;

			} # Display normal object output end.

		} # Loop through each object end.

		# Prepare the output to be returned.
		$KPay_curl_output_response_array=array(
		"Result"=>$KPay_curl_output_result,
		"Reason"=>$KPay_curl_output_reason,
		"DataMode"=>$KPay_curl_output_data_mode,
		"OrderNumber"=>$KPay_order_number,
		"TransactionID"=>$KPay_transaction_id,
		"TransactionStatus"=>$KPay_transaction_status,
		"TransactionDescription"=>$KPay_transaction_description,
		"FPXTransactionID"=>$KPay_FPX_transaction_id,
		"BusinessName"=>$KPay_curl_seller_name,
		);
		
		return $KPay_curl_output_response_array;
	} # Function to fetch the KPay API response end.
	
	public function KPay_API_operations($KPay_API_data)
	{ # Function to communicate with KPay API start.
		# Request for the latest transaction response from the server (API request).
		$KPay_curl_output=$this->KPay_API_cURL($KPay_API_data);

		# Fetch the API response.
		$KPay_curl_output_response=$this->KPay_API_cURL_response($KPay_curl_output);

		# Translate the API response.
		$KPay_curl_output_result=$KPay_curl_output_response["Result"];
		$KPay_curl_output_reason=$KPay_curl_output_response["Reason"];
		$KPay_curl_output_data_mode=$KPay_curl_output_response["DataMode"];
		if($KPay_curl_output_response["OrderNumber"])
		$KPay_order_number=$KPay_curl_output_response["OrderNumber"];
		if($KPay_curl_output_response["TransactionID"])
		$KPay_transaction_id=$KPay_curl_output_response["TransactionID"];
		if($KPay_curl_output_response["TransactionStatus"])
		$KPay_transaction_status=$KPay_curl_output_response["TransactionStatus"];
		if($KPay_curl_output_response["TransactionDescription"])
		$KPay_transaction_description=$KPay_curl_output_response["TransactionDescription"];
		if($KPay_curl_output_response["FPXTransactionID"])
		$KPay_FPX_transaction_id=$KPay_curl_output_response["FPXTransactionID"];
		if($KPay_curl_output_response["BusinessName"])
		$KPay_curl_seller_name=$KPay_curl_output_response["BusinessName"];

		return $KPay_curl_output_response;
	} # Function to communicate with KPay API end.
} # Class plgHikashoppaymentKpay end.
