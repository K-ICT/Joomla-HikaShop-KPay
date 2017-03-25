<?php
/**
 * @package	KPay for HikaShop
 * @version	1.0.0
 * @author	www.k-ict.org
 * @copyright	(C) 2017 Konsortium ICT Pantai Timur. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
$KPay_name="KPay";
$KPay_provider="https://www.k-ict.org/";
$KPay_MD5_hash_url=$KPay_provider."v4/online-security/md5-hash/";
$KPay_url=$KPay_provider."kpg/";
$KPay_payment_url=$KPay_url."payment.php";
$KPay_receipt_url=$KPay_url."receipt.php";
$API_url=$KPay_url."API.php";
$API_client_name="KPG";
$API_client_type="APIclient";
$API_client_version="v1.1";
$API_user_agent=$API_client_name." ".$API_client_type." ".$API_client_version;
$KPay_HS_callback_url=HIKASHOP_LIVE."index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=".strtolower($KPay_name)."&amp;tmpl=component";
$KPay_order_payment_description_img_options=HIKASHOP_LIVE."plugins/hikashoppayment/".strtolower($KPay_name);
$KPay_order_payment_description_array=array(
		array(
		'Simple field',
		'<div align="right" style="color:#ddf;"><i>Suitable for general sales.<br>Eg; Selling shirts, car accessories, and retail items.</i></div>',
		'Type01',
		$KPay_order_payment_description_img_options.'/PaymentDescription001.jpg',
		),
		array(
		'Year and month (YYYY/MM) with simple field',
		'<div align="right" style="color:#ddf;"><i>Suitable for monthly-basis billing cycle.<br>Eg; Tuition fee, and monthly maintenance.</i></div>',
		'Type02',
		$KPay_order_payment_description_img_options.'/PaymentDescription002.jpg',
		),
		array(
		'Date (YYYY/MM/DD) with simple field',
		'<div align="right" style="color:#ddf;"><i>Suitable for specific date billing cycle.<br>Eg; Hotel booking, and bus tickets.</i></div>',
		'Type03',
		$KPay_order_payment_description_img_options.'/PaymentDescription003.jpg',
		)
);
?>
<tr>
<td class="key">
<label for="data[payment][payment_params][kpay_user_loginid]"><?php echo JText::_('KPay User Login ID'); ?></label>
</td>
<td>
<input type="text" name="data[payment][payment_params][kpay_user_loginid]" value="<?php echo $this->escape(@$this->element->payment_params->kpay_user_loginid); ?>" />
<div style="color:#3071A9;"><i>Your <a href="<?php echo $KPay_url; ?>" target="KPayWindow"><?php echo $KPay_name; ?></a> User Login ID <b>MUST</b> be hashed using MD5 algorithm.</i></div>
<div><a title="Click here to hash your <?php echo $KPay_name; ?> User Login ID." href="<?php echo $KPay_MD5_hash_url; ?>" target="KICTwindow" style="text-decoration:underline;cursor:pointer;width:auto;background:#300;color:#fff;padding:3px;padding-left:20px;padding-right:20px">Hash your user login ID, copy, and paste it in the above field.</a></div>
</td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][kpay_user_password]"><?php echo JText::_('KPay User Password'); ?></label>
</td>
<td>
<input type="password" name="data[payment][payment_params][kpay_user_password]" value="<?php echo $this->escape(@$this->element->payment_params->kpay_user_password); ?>" />
<div style="color:#3071A9;"><i>Your <a href="<?php echo $this->KPay_url; ?>" target="KPayWindow"><?php echo $KPay_name; ?></a> User Password <b>MUST</b> be hashed using MD5 algorithm.</i></div>
<div><a title="Click here to hash your <?php echo $KPay_name; ?> User Password." href="<?php echo $KPay_MD5_hash_url; ?>" target="KICTwindow" style="text-decoration:underline;cursor:pointer;width:auto;background:#300;color:#fff;padding:3px;padding-left:20px;padding-right:20px">Hash your user password, copy, and paste it in the above field.</a></div>
</td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][kpay_portal_key]"><?php echo JText::_('Portal Key'); ?></label>
</td>
<td>
<input type="text" name="data[payment][payment_params][kpay_portal_key]" value="<?php echo $this->escape(@$this->element->payment_params->kpay_portal_key); ?>" />
<div style="color:#3071A9;"><i>Copy your Portal key from <a href="<?php echo $KPay_url; ?>" target="KPayWindow"><?php echo $KPay_name; ?></a> and paste in the field.</i></div>
<div style="background:#358;color:#fff;padding:10px" align="left">Please config the <font style="color:#ff0"><?php echo $KPay_name; ?> Receipt URL</font> as <font style="color:#ff0"><?php echo $KPay_HS_callback_url; ?></font></div>
</td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][kpay_order_payment_description]"><?php echo JText::_('Order Payment Description'); ?></label>
</td>
<td>
<?php
$kpay_order_payment_description=$this->escape(@$this->element->payment_params->kpay_order_payment_description);
?>
<select name="data[payment][payment_params][kpay_order_payment_description]" onchange="highlightPreview(this.options[selectedIndex].id);">
<?php
for($a=0;$a<count($KPay_order_payment_description_array);$a++)
{
?>
<option id="<?php echo $a; ?>" value="<?php echo $KPay_order_payment_description_array[$a][2]; ?>" <?php if($kpay_order_payment_description==$KPay_order_payment_description_array[$a][2]) echo 'selected'; ?>><?php echo $KPay_order_payment_description_array[$a][0]; ?></option>
<?php
}
?>
</select>
<br><div style="background:#300;color:#fff;padding:3px;padding-left:10px">Choose the Order Payment Description field that suits your selling as shown in the screenshots below. Click on the thumbnail to enlarge image.</div><br>
<?php for($a=0;$a<count($KPay_order_payment_description_array);$a++)
{
?>
<div id="previewSample<?php echo $a; ?>Div" style="background: #830 none repeat scroll 0% 0%;color: #FF0;padding: 3px 3px 3px 10px;width: 350px;<?php if($kpay_order_payment_description!=$KPay_order_payment_description_array[$a][2]) echo 'opacity:0.5;'; ?>"><?php echo $KPay_order_payment_description_array[$a][0].$KPay_order_payment_description_array[$a][1]; ?></div><a href="<?php echo $KPay_order_payment_description_array[$a][3]; ?>" title="Click here to view larger image." target="imgPreviewWindow"><img id="previewImg<?php echo $a; ?>" alt="<?php echo $KPay_order_payment_description_array[$a][0]; ?>" src="<?php echo $KPay_order_payment_description_array[$a][3]; ?>" style="width:350px;<?php if($kpay_order_payment_description!=$KPay_order_payment_description_array[$a][2]) echo 'opacity:0.5;'; ?>"></a><br>&nbsp;
<?php
}
?>
</div>
<script>
function highlightPreview(typeNo)
{
<?php
for($a=0;$a<count($KPay_order_payment_description_array);$a++)
{
?>
document.getElementById('previewSample'+<?php echo $a; ?>+'Div').style.opacity='0.5';
document.getElementById('previewImg'+<?php echo $a; ?>).style.opacity='0.5';
<?php
}
?>
document.getElementById('previewSample'+typeNo+'Div').style.opacity='1.0';
document.getElementById('previewImg'+typeNo).style.opacity='1.0';
}
</script>
</td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][invalid_status]"><?php echo JText::_('INVALID_STATUS'); ?></label>
</td>
<td><?php echo $this->data['order_statuses']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?></td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][pending_status]"><?php echo JText::_('PENDING_STATUS'); ?></label>
</td>
<td><?php echo $this->data['order_statuses']->display("data[payment][payment_params][pending_status]",@$this->element->payment_params->pending_status); ?></td>
</tr>
<tr>
<td class="key">
<label for="data[payment][payment_params][verified_status]"><?php echo JText::_('VERIFIED_STATUS'); ?></label>
</td>
<td><?php echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?></td>
</tr>
