<?php

chdir ('../../');
require_once('api/Simpla.php');
require_once('payment/fondy/fondy.cls.php');
require_once(dirname(__FILE__).'/FondyView.php');
$fonView = new FondyView();
$simpla = new Simpla();
$err = '';
////////////////////////////////////////////////
// Выберем заказ из базы
////////////////////////////////////////////////
if (empty($_POST))
{
    $fap = json_decode(file_get_contents("php://input"));
    $_POST=array();
    foreach($fap as $key=>$val)
    {
        $_POST[$key] =  $val ;
    }
    list($order_id,) = explode(fondycsl::ORDER_SEPARATOR, $_POST['order_id']);
    $order = $simpla->orders->get_order(intval($order_id));
    $payment_method = $simpla->payment->get_payment_method($order->payment_method_id);
    $payment_currency = $simpla->money->get_currency(intval($payment_method->currency_id));
    $settings = $simpla->payment->get_payment_settings($payment_method->id);

    $options = array(
        'merchant' => $settings['fondy_merchantid'],
        'secretkey' => $settings['fondy_secret']
    );
    $paymentInfo = fondycsl::isPaymentValid($options, $_POST);

    if (!$order->paid) {
        if ($_POST['amount'] / 100 >= round($simpla->money->convert($order->total_price, $payment_method->currency_id, false), 2)) {
            if ($paymentInfo === true) {
                if ($_POST['order_status'] == fondycsl::ORDER_APPROVED) {

                    // Установим статус оплачен

                    $simpla->orders->update_order(intval($order->id), array('paid' => 1));

                    // Отправим уведомление на email
                    $simpla->notify->email_order_user(intval($order->id));
                    $simpla->notify->email_order_admin(intval($order->id));

                    // Спишем товары
                    $simpla->orders->close(intval($order->id));

                    echo 'Ok';

                } else {
                    echo 'error';
                }
            }
        }
    }else{
        echo 'Order status already updated';
    }
}
else
{  // echo 2;
    list($order_id,) = explode(fondycsl::ORDER_SEPARATOR, $_POST['order_id']);
    $order = $simpla->orders->get_order(intval($order_id));
    $payment_method = $simpla->payment->get_payment_method($order->payment_method_id);
    $payment_currency = $simpla->money->get_currency(intval($payment_method->currency_id));
    $settings = $simpla->payment->get_payment_settings($payment_method->id);

    $options = array(
        'merchant' => $settings['fondy_merchantid'],
        'secretkey' => $settings['fondy_secret']
    );
    $paymentInfo = fondycsl::isPaymentValid($options, $_POST);

    if (!$order->paid) {
        if ($_POST['amount'] / 100 >= round($simpla->money->convert($order->total_price, $payment_method->currency_id, false), 2)) {
            if ($paymentInfo === true) {
                if ($_POST['order_status'] == fondycsl::ORDER_APPROVED) {

                    // Установим статус оплачен

                    $simpla->orders->update_order(intval($order->id), array('paid' => 1));

                    // Отправим уведомление на email
                    $simpla->notify->email_order_user(intval($order->id));
                    $simpla->notify->email_order_admin(intval($order->id));

                    // Спишем товары
                    $simpla->orders->close(intval($order->id));


                    $invoice['status'] = $_POST[order_status];
                    $invoice['transaction'] = $_POST['order_id'];
                    $invoice['system'] = 'fondy';
                    $invoice['amount'] = $_POST['amount'] / 100 . " " . $_POST['actual_currency'];

                    $fonView->design->assign('invoice', $invoice);

                    print $fonView->fetch();

                } else {
                    $simpla->orders->update_order(intval($order->id), array('paid' => 0));
                    //$err=$_POST[order_desc];
                    $invoice['status'] = $_POST[order_status];
                    $invoice['error_message'] = $_POST[response_description];
                    $invoice['error_code'] = $_POST[response_code];
                    $fonView->design->assign('invoice', $invoice);

                    print $fonView->fetch();

                }
            } else
                $err = $paymentInfo;
        } else
            $err = "Amount check failed";
    }
//$err = 'Order is paid';
    $invoice['error_code'] = 'unknown code';
    $invoice['status'] = $_POST[order_status];
    $invoice['error_message'] = $err;
    $fonView->design->assign('invoice', $invoice);
    print $fonView->fetch();
}