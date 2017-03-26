<?php
/**
 * @package	KPay for HikaShop
 * @version	1.0.0
 * @author	www.k-ict.org
 * @copyright	(C) 2017 Konsortium ICT Pantai Timur. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="hikashop_kpay_end" id="hikashop_kpay_end">
<form id="hikashop_kpay_form" name="hikashop_kpay_form" action="<?php echo $this->payment_params->url; ?>" method="post">
<span id="hikashop_kpay_end_message" class="hikashop_kpay_end_message">
<?php
$login_id=@$this->plugin_params->kpay_user_loginid;
$password=@$this->plugin_params->kpay_user_password;
$portal_key=@$this->plugin_params->kpay_portal_key;
$payment_description_type=@$this->plugin_params->kpay_order_payment_description;

$KPay_order_no=@$this->vars['order_no'];
$KPay_order_amount=@$this->vars['amount'];
$KPay_order_description=@$this->vars['description'];
$KPay_order_buyer_name=@$this->vars['buyer_name'];
$KPay_order_buyer_tel=@$this->vars['buyer_tel'];
$KPay_order_buyer_email=@$this->vars['buyer_email'];

			# Fetch the seller name via API.
			$KPG_API_data=array(
			"UserLoginID"=>$login_id,
			"UserPassword"=>$password,
			"Category"=>"getSellerDetails",
			"PortalKey"=>$portal_key,
			);

			# Perform API operations.
			$KPG_API_operations=$this->KPay_API_operations($KPG_API_data);

			# Set the seller name.
			$KPG_API_seller_name=$KPG_API_operations["BusinessName"];

			# KPG security notice for buyers.
			$KPG_greenbar_GoogleChrome="Green bar in Google Chrome";
			$KPG_greenbar_MozillaFirefox="Green bar in Mozilla Firefox";
			$KPG_security_notice='<div style="color:#a00;font-size:15px;font-weight:bold;">
			<p>IMPORTANT!!! DO ONLY PROCEED with online payment if you see the <font style="color:#0a0;">green bar</font> as shown below after clicking on the <font style="color:#a46497;">Continue</font> button;</p>
			<p><img alt="'.$KPG_greenbar_GoogleChrome.'" src="'.HIKASHOP_LIVE.'/plugins/hikashoppayment/kpay/EVSSLGoogleChrome.jpg"> '.$KPG_greenbar_GoogleChrome.'</p>
			<p><img alt="$KPG_greenbar_MozillaFirefox" src="'.HIKASHOP_LIVE.'/plugins/hikashoppayment/kpay/EVSSLMozillaFirefox.jpg"> '.$KPG_greenbar_MozillaFirefox.'</p>
			<p>Please report any scam to abuse@k-ict.org</p>
			</div>';

			$custom_order_description='
			<div style="background-color:#eee;border-color:#eee;color:#333333;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;padding:10px;">
			<div align="center" style="font-weight:bold;font-size:15px;">';

			if(!$KPG_API_seller_name)
			{ # Display error message for no seller name fetched from KPG start.
				$custom_order_description.='<table align="center" width="80%" style="background:#eee;border:0px;">
				<tr align="left" valign="top" style="background:#eee;">
				<td colspan="2" style="border-bottom:1px dotted #ccc;"><div style="font-size:25px;color:#cb8700;font-weight:normal;margin-top:25px;">Invalid seller information</div>
				<div style="color:#936;font-weight:normal;padding-top:20px;">Sorry, we are unable to continue due to the invalid seller information.</div>
				<div style="color:#936;font-weight:normal;padding-top:20px;">If you are the store owner, please request for KPG seller registration by sending email to <a href="mailto:sales@k-ict.org">sales@k-ict.org</a></div></td>
				</tr>
				</table>
				</div>
				<div align="center" style="">
				<input type="button" class="btn btn-primary" value="Back" onclick="history.back();">
				<input type="button" class="btn btn-primary" value="Visit provider site" onclick="window.open(\''.$KPG_provider.'\');">';
			} # Display error message for no seller name fetched from KPG end.
			else
			{ # Display the Order Payment Description page start.
				$custom_order_description.='<table align="center" width="80%" style="background:#eee;border:0px;">
				<tr align="left" valign="top" style="background:#eee;">
				<td colspan="2" style="border-bottom:1px dotted #ccc;"><div style="font-size:25px;color:#0087cb;font-weight:normal;margin-top:25px;">Complete your order information</div>
				<div style="color:#936;font-weight:normal;padding-top:20px;">Below are the information that will be submitted for Online Payment.</div></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Order number</font>
				<br><span id="KPGorderNumber" style="color:#008;">'.$KPay_order_no.'</span></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Amount</font>
				<br><span id="KPGorderAmount" style="color:#008;">MYR '.number_format($KPay_order_amount,2,".",",").'</span></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Seller</font>
				<br><span id="KPGsellerName" style="color:#008;">'.$KPG_API_seller_name.'</span></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Buyer name</font>
				<br><span id="KPGorderBuyerName" style="color:#008;">'.$KPay_order_buyer_name.'</span></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Buyer tel. no.</font>
				<br><span id="KPGorderBuyerTel" style="color:#008;">'.$KPay_order_buyer_tel.'</span></td>
				</tr>
				<tr align="left" valign="top" style="background:#eee;">
				<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Buyer email</font>
				<br><span id="KPGorderBuyerEmail" style="color:#008;">'.$KPay_order_buyer_email.'</span></td>
				</tr>';

				if($payment_description_type=='Type02')
				{
					$custom_order_description.='
					<tr align="left" valign="top" style="background:#eee;">
					<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Payment for</font>
					<br><input id="KPGorderDescriptionYear" name="KPGorderDescriptionYear" type="text" style="width:auto;text-align:center;border:1px solid #ccc;height:19px;padding:2px;" size="4" placeholder="YYYY" value="'.date("Y").'">
					<select id="KPGorderDescriptionMonth" name="KPGorderDescriptionMonth" style="width:auto;text-align:center;height:25px;border-radius:1px;border:1px solid #ccc;width:80px;">
					<option value="">Month</option>';
					for($a=1;$a<=12;$a++) # There are only 12 months.
					{
					if($a<10)
					$a='0'.$a;
					$custom_order_description.='<option value="'.date("M",strtotime(date(date("Y")."-".$a."-01"))).'">'.date("M",strtotime(date("Y")."-".$a."-01")).'</option>';
					}
					$custom_order_description.='</select>
					<input id="KPGorderDescription" name="KPGorderDescription" type="text" style="width:auto;" size="40" placeholder="Describe your payment here.">
					</td>
					</tr>
					</table>
					</div>
					<div align="center" style="">
					<div id="KPG_error_message" align="center" style="padding:5px;font-size:15px;background:#800;color:#fff;display:none;font-weight:bold;">&nbsp;</div>
					'.$KPG_security_notice.'
					<input type="button" class="btn btn-primary" value="Continue" onclick="if(document.getElementById(\'KPGorderDescriptionYear\').value && document.getElementById(\'KPGorderDescriptionYear\').value.length==4 && /^[0-9]*$/g.test(document.getElementById(\'KPGorderDescriptionYear\').value) && document.getElementById(\'KPGorderDescriptionMonth\').value && document.getElementById(\'KPGorderDescriptionMonth\').value.length==3 && /^[a-zA-Z]*$/g.test(document.getElementById(\'KPGorderDescriptionMonth\').value) && document.getElementById(\'KPGorderDescription\').value) { document.getElementById(\'description\').value=document.getElementById(\'KPGorderDescriptionYear\').value+\'/\'+document.getElementById(\'KPGorderDescriptionMonth\').value+\'/\'+document.getElementById(\'KPGorderDescription\').value; document.getElementById(\'KPG_error_message\').style.display=\'none\'; document.getElementById(\'hikashop_kpay_end_spinner\').style.display=\'block\'; document.getElementById(\'hikashop_kpay_form\').submit(); } else { document.getElementById(\'KPG_error_message\').innerHTML=\'Please provide the description (YEAR, MONTH, and notes) for your payment.\'; document.getElementById(\'KPG_error_message\').style.display=\'block\'; document.getElementById(\'KPGorderDescriptionYear\').focus(); };">';
				}
				elseif($payment_description_type=='Type03')
				{
					$custom_order_description.='
					<tr align="left" valign="top" style="background:#eee;">
					<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Payment for</font>
					<br><input id="KPGorderDescriptionYear" name="KPGorderDescriptionYear" type="text" style="width:auto;text-align:center;border:1px solid #ccc;height:19px;padding:2px;" size="4" placeholder="YYYY" value="'.date("Y").'">
					<select id="KPGorderDescriptionMonth" name="KPGorderDescriptionMonth" style="width:auto;text-align:center;height:25px;border-radius:1px;border:1px solid #ccc;width:80px;">
					<option value="">Month</option>';
					for($a=1;$a<=12;$a++) # There are only 12 months.
					{
					if($a<10)
					$a='0'.$a;
					$custom_order_description.='<option value="'.date("M",strtotime(date("Y")."-".$a."-01")).'">'.date("M",strtotime(date("Y")."-".$a."-01")).'</option>';
					}
					$custom_order_description.='</select>
					<select id="KPGorderDescriptionDay" name="KPGorderDescriptionDay" style="width:auto;text-align:center;height:25px;border-radius:1px;border:1px solid #ccc;width:80px;">
					<option value="">Day</option>';
					for($a=1;$a<=31;$a++) # There are only 31 days.
					{
					if($a<10)
					$a='0'.$a;
					$custom_order_description.='<option value="'.date(d,strtotime(date("Y")."-01-".$a)).'">'.date("d",strtotime(date("Y")."-01-".$a)).'</option>'; # Hard-code 01 to indicate January because this month has 31 days.
					}
					$custom_order_description.='</select>
					<input id="KPGorderDescription" name="KPGorderDescription" type="text" style="width:auto;" size="40" placeholder="Describe your payment here.">
					</td>
					</tr>
					</table>
					</div>
					<div align="center" style="">
					<div id="KPG_error_message" align="center" style="padding:5px;font-size:15px;background:#800;color:#fff;display:none;font-weight:bold;">&nbsp;</div>
					'.$KPG_security_notice.'
					<input type="button" class="btn btn-primary" value="Continue" onclick="if(document.getElementById(\'KPGorderDescriptionYear\').value && document.getElementById(\'KPGorderDescriptionYear\').value.length==4 && /^[0-9]*$/g.test(document.getElementById(\'KPGorderDescriptionYear\').value) && document.getElementById(\'KPGorderDescriptionMonth\').value && document.getElementById(\'KPGorderDescriptionMonth\').value.length==3 && /^[a-zA-Z]*$/g.test(document.getElementById(\'KPGorderDescriptionMonth\').value) && document.getElementById(\'KPGorderDescriptionDay\').value && document.getElementById(\'KPGorderDescriptionDay\').value.length==2 && /^[0-9]*$/g.test(document.getElementById(\'KPGorderDescriptionDay\').value) && document.getElementById(\'KPGorderDescription\').value) { document.getElementById(\'description\').value=document.getElementById(\'KPGorderDescriptionYear\').value+\'/\'+document.getElementById(\'KPGorderDescriptionMonth\').value+\'/\'+document.getElementById(\'KPGorderDescriptionDay\').value+\'/\'+document.getElementById(\'KPGorderDescription\').value; document.getElementById(\'KPG_error_message\').style.display=\'none\'; document.getElementById(\'hikashop_kpay_end_spinner\').style.display=\'block\'; document.getElementById(\'hikashop_kpay_form\').submit(); } else { document.getElementById(\'KPG_error_message\').innerHTML=\'Please provide the description (DATE, and notes) for your payment.\'; document.getElementById(\'KPG_error_message\').style.display=\'block\'; document.getElementById(\'KPGorderDescriptionYear\').focus(); };">';
				}
				else
				{
					$custom_order_description.='
					<tr align="left" valign="top" style="background:#eee;">
					<td style="border:0px;padding:5px;"><font style="color:#630;">&#10148; Payment for</font>
					<br><input id="KPGorderDescription" name="KPGorderDescription" type="text" style="width:auto;" size="40" placeholder="Describe your payment here." value="'.$KPay_order_description.'">
					</td>
					</tr>
					</table>
					</div>
					<div align="center" style="">
					<div id="KPG_error_message" align="center" style="padding:5px;font-size:15px;background:#800;color:#fff;display:none;font-weight:bold;">&nbsp;</div>
					'.$KPG_security_notice.'
					<input type="button" class="btn btn-primary" value="Continue" onclick="if(document.getElementById(\'KPGorderDescription\').value) { document.getElementById(\'KPG_error_message\').style.display=\'none\'; document.getElementById(\'description\').value=document.getElementById(\'KPGorderDescription\').value; document.getElementById(\'hikashop_kpay_end_spinner\').style.display=\'block\'; document.getElementById(\'hikashop_kpay_form\').submit(); } else { document.getElementById(\'KPG_error_message\').innerHTML=\'Please provide the description for your payment.\'; document.getElementById(\'KPG_error_message\').style.display=\'block\'; document.getElementById(\'KPGorderDescription\').focus(); } ">';
				}

				$custom_order_description.='
				<input type="button" class="btn btn-primary" value="Cancel" onclick="location.href=\''.HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$KPay_order_no.'\';"';
				echo $custom_order_description;
			} # Display the Order Payment Description page end.
?>
</span>
<span id="hikashop_kpay_end_spinner" class="hikashop_kpay_end_spinner hikashop_checkout_end_spinner">
</span>
<br/>
		<div id="hikashop_kpay_end_image" class="hikashop_kpay_end_image">
		</div>
		<?php
			foreach($this->vars as $name => $value)
			{
				echo '<input type="hidden" ';
				if($name=='description')
				echo 'id="'.$name.'" name="'.$name.'" ';
				else
				echo 'name="'.$name.'" value="';
				if($name!='description')
				echo htmlspecialchars((string)$value);
				echo '" />';
			}
			JFactory::getApplication()->input->setVar('noform','1');
		?>
	</form>
	<script type="text/javascript">
		<!--
		function isIframe(){
			try{
				return window.self !== window.top;
			}catch(e){
				return false;
			}
		}
		if(isIframe()){
			document.getElementById('hikashop_kpay_form').target = '_blank';
		}
		//document.getElementById('hikashop_kpay_form').submit();
		//-->
	</script>
	<!--[if IE]>
	<script type="text/javascript">
			document.getElementById('hikashop_kpay_button').style.display = 'none';
			document.getElementById('hikashop_kpay_button_message').innerHTML = '';
	</script>
	<![endif]-->
</div>
