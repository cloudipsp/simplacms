<?php
class fondycsl
{
    const RESPONCE_SUCCESS = 'success';
    const RESPONCE_FAIL = 'failure';
    const ORDER_SEPARATOR = '#';
    const SIGNATURE_SEPARATOR = '|';
    const ORDER_APPROVED = 'approved';
    const ORDER_DECLINED = 'declined';

    public static function getSignature($data, $password, $encoded = true)
    {
        $data = array_filter($data, function($var) {
            return $var !== '' && $var !== null;
        });
        ksort($data);
        $str = $password;
        foreach ($data as $k => $v) {
            $str .= self::SIGNATURE_SEPARATOR . $v;
        }
        if ($encoded) {
            return sha1($str);
        } else {
            return $str;
        }
    }
    public static function isPaymentValid($oplataSettings, $response)
    {
        if ($oplataSettings['merchant'] != $response['merchant_id']) {
            return 'An error has occurred during payment. Merchant data is incorrect.';
        }
        $originalResponse = $response;
        $strs = explode(fondycsl::SIGNATURE_SEPARATOR,$originalResponse['response_signature_string']);
        $str = (str_replace($strs[0],$oplataSettings['secretkey'],$originalResponse['response_signature_string']));
        if (sha1($str) != $originalResponse['signature']) {
            return 'An error has occurred during payment. Signature is not valid.';
        }
        return true;
    }

}
?>