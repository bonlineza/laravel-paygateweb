<?php

namespace MisterBrownRSA\PayGateWeb;

/**
 * A service class that wraps the main calls that can be done to DHL
 */
class PayGateWeb
{
    private $initiateURL = 'https://secure.paygate.co.za/payweb3/initiate.trans';

    private $paygate_id;
    private $reference;
    private $amount; //user
    private $currency;
    private $return_url; //server
    private $transaction_date;
    private $locale;
    private $country; //user country code
    private $email; //user
    private $notify_url; //server
    private $user1; //user
    private $encryption_key;

    private $checksum;

    private $processRequest;

    public function __construct($options = [])
    {
        $this->encryption_key = getenv('PAYGATE_SECRET') ?: config('paygate.secret', 'secret');
        $this->paygate_id = getenv('PAYGATE_ID') ?: config('paygate.ID', '10011072130');
        $this->currency = getenv('PAYGATE_CURRENCY') ?: config('paygate.currency', 'USD');
        $this->transaction_date = date('Y-m-d H:i:s');
        $this->locale = getenv('PAYGATE_LOCALE') ?: config('paygate.locale', 'en-za');

        $_SESSION['paygate']['pgid'] = $this->paygate_id;
        $_SESSION['paygate']['reference'] = $this->reference;
        $_SESSION['paygate']['key'] = $this->encryption_key;

        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function create($options = [])
    {
        if (empty($options)) {
            return false;
        }

        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
            /*TODO:: maybe return errors if the key does not exist ?*/
        }

        return $this;
    }

    public function reference($stringValue = null)
    {
        if (empty($stringValue)) {
            return $this->reference;
        }

        $this->reference = $stringValue;

        return $this;
    }

    public function amount($floatValue = null)
    {
        if (empty($floatValue)) {
            return $this->amount;
        }

        $this->amount = $floatValue;

        return $this;
    }

    public function user($user)
    {
        $this->email = $user->email;
        $this->country = "ZAF"; /*TODO:: implement to get from user model*/
        $this->user1 = $user->id;

        return $this;
    }

    public function returnURL($URL = null)
    {
        if (empty($URL)) {
            return $this->return_url;
        }

        $this->return_url = $URL;

        return $this;
    }

    public function notifyURL($URL = null)
    {
        if (empty($URL)) {
            return $this->notify_url;
        }

        $this->notify_url = $URL;

        return $this;
    }

    public function initiate()
    {
        $result = $this->doCurlPost($this->getDataWithChecksum(), $this->initiateURL);

        if ($result === false) {
            return false; /*TODO:: error message ?*/
        }

        $response = [];
        parse_str($result, $response);

        if (array_key_exists('ERROR', $response)) {
            return 'error in response';
        }

        $this->processRequest = [
            'PAYGATE_ID'     => $response['PAYGATE_ID'],
            'PAY_REQUEST_ID' => $response['PAY_REQUEST_ID'],
            'REFERENCE'      => $response['REFERENCE'],
            'CHECKSUM'       => $response['CHECKSUM'],
        ];

        if (!$this->validateChecksum($this->processRequest)) {
            return false;
        }

        return $response;
    }

    public function generateChecksum($postData = null)
    {
        $checksum = '';
        $this->checksum = '';

        if (empty($postData)) {
            $data = $this->getData();
            $checksum = implode('', $data);
        } else {
            $checksum = implode('', $postData);
        }
        $checksum .= $this->encryption_key;
        $checksum = md5($checksum);

        $this->checksum = $checksum;

        return $this;
    }

    public function validateChecksum($data)
    {
        $returnedChecksum = $data['CHECKSUM'];
        unset($data['CHECKSUM']);

        $checksum = implode('', $data);
        $checksum .= $this->encryption_key;
        $checksum = md5($checksum);

        return ($returnedChecksum == $checksum);
    }

    public function validateResponse($data)
    {
        $responseData = [
            'PAYGATE_ID'         => $this->paygate_id,
            'PAYGATE_REQUEST_ID' => $data['PAY_REQUEST_ID'],
            'TRANSACTION_STATUS' => $data['TRANSACTION_STATUS'],
            'REFERENCE'          => $this->reference,
            'CHECKSUM'           => $data['CHECKSUM'],
        ];

        return $this->validateChecksum($responseData);
    }

    public function getData()
    {
        /*TODO:: errors perhaps ?*/
        $response['PAYGATE_ID'] = $this->paygate_id;
        $response['REFERENCE'] = $this->reference;
        $response['AMOUNT'] = $this->amount;
        $response['CURRENCY'] = $this->currency;
        $response['RETURN_URL'] = $this->return_url;
        $response['TRANSACTION_DATE'] = $this->transaction_date;
        $response['LOCALE'] = $this->locale;
        $response['COUNTRY'] = $this->country;
        $response['EMAIL'] = $this->email;
        $response['NOTIFY_URL'] = $this->notify_url;
        $response['USER1'] = $this->user1;

        return $response;
    }

    public function getDataWithChecksum()
    {
        $this->generateChecksum();
        $response = $this->getData();
        $response['CHECKSUM'] = $this->checksum;

        return $response;
    }

    public function doCurlPost($postData, $url)
    {

        if (!$this->isCurlInstalled()) {
            $this->lastError = 'cURL is NOT installed on this server. http://php.net/manual/en/curl.setup.php';

            return false;
        }

        $fields_string = http_build_query($postData);

        //open connection
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //ssl
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $result;
    }

    private function isCurlInstalled()
    {
        return (in_array('curl', get_loaded_extensions()));
    }
}