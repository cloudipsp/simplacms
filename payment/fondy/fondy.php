<?php

require_once('api/Simpla.php');
require_once('payment/fondy/fondy.cls.php');

class Fondy extends Simpla
{	
	public function checkout_form($order_id, $button_text = null)
	{
		if(empty($button_text))
			$button_text = 'Перейти к оплате';
		
		$order = $this->orders->get_order((int)$order_id);
		$payment_method = $this->payment->get_payment_method($order->payment_method_id);
		$payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
		$settings = $this->payment->get_payment_settings($payment_method->id);
		
		$price = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
		
		
		// описание заказа
		// order description
		$desc = 'Order:'.$order->id;
		
		// Способ оплаты
		$paymode = $settings['fondy_paymode'];

		$success_url = $this->config->root_url.'/order/';
		$result_url = $this->config->root_url.'/payment/fondy/callback.php';
       // print_r($settings);
		$currency = $payment_currency->code;
		if ($currency == 'RUR')
		  $currency = 'RUB';
        if ($settings[lang]=='') {
            $settings[lang] ='ru';
        }
		$oplata_args = array('order_id' => $order_id . fondycsl::ORDER_SEPARATOR . time(),
			'merchant_id' => $settings['fondy_merchantid'],
			'order_desc' => $desc,
			'amount' => $price,
			'currency' => $currency,
			'server_callback_url' => $result_url,
			'response_url' => $result_url,
			'lang' =>  $settings['lang'],
			'sender_email' => $order->email);

		$oplata_args['signature'] = fondycsl::getSignature($oplata_args, $settings[fondy_secret]);

		return '    <script src="https://api.fondy.eu/static_common/v1/checkout/ipsp.js"></script>
                    <script src="https://rawgit.com/dimsemenov/Magnific-Popup/master/dist/jquery.magnific-popup.js"></script>
                    <link href="https://rawgit.com/dimsemenov/Magnific-Popup/master/dist/magnific-popup.css" type="text/css" rel="stylesheet" media="screen">
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

<style>
#checkout_wrapper a{
    font-size: 20px;
    top: 30px;
    padding: 20px;
    position: relative;
}
#checkout_wrapper {
    text-align: left;
    position: relative;
    background: #FFF;
    /* padding: 30px; */
    padding-left: 15px;
    padding-right: 15px;
    padding-bottom: 30px;
    width: auto;
    max-width: 2000px;
    margin: 9px auto;
}
</style>
<div id="checkout">
<div id="checkout_wrapper">
</div>
</div>
<script>
function callmag(){
$.magnificPopup.open({
showCloseBtn:false,
        items: {
            src: $("#checkout_wrapper"),
            type: "inline"
        }
    });
}
$(document).ready(function() {
 $.magnificPopup.open({
 showCloseBtn:false,
        items: {
            src: $("#checkout_wrapper"),
            type: "inline"
        }
    });
    })
</script>
<script>
function checkoutInit(url, val) {
	$ipsp("checkout").scope(function() {
		this.setCheckoutWrapper("#checkout_wrapper");
		this.addCallback(__DEFAULTCALLBACK__);
		this.action("show", function(data) {
           $("#checkout_loader").remove();
            $("#checkout").show();
        });
		this.action("hide", function(data) {
            $("#checkout").hide();
        });
        if(val){
        this.width(val);
        this.action("resize", function(data) {
        $("#checkout_wrapper").width(val).height(data.height);
            });
        }else{
         this.action("resize", function(data) {
        $("#checkout_wrapper").width(480).height(data.height);
            });
        }
		this.loadUrl(url);
	});
    };
    var button = $ipsp.get("button");
    button.setMerchantId('.$oplata_args[merchant_id].');
    button.setAmount('.$oplata_args[amount].', "'.$oplata_args[currency].'", true);
    button.setHost("api.fondy.eu");
    button.addParam("order_desc","'.$oplata_args[order_desc].'");
    button.addParam("order_id","'.$oplata_args[order_id].'");
	button.addParam("signature","' . $oplata_args[signature] . '");
    button.addParam("lang","'.$oplata_args[lang].'");//button.addParam("delayed","N");
    button.addParam("server_callback_url","'.$oplata_args[server_callback_url].'");
    button.addParam("sender_email","'.$oplata_args[sender_email].'");
    button.setResponseUrl("'.$oplata_args[response_url].'");
    checkoutInit(button.getUrl());
    </script>
    <input type="button" onclick="callmag();" class="checkout_button" value="'.$button_text.'">
    ';

	}
}