<?php
/**
 * PHP Library for WebToPay provided services.
 * Copyright (C) 2010  http://www.webtopay.com/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    WebToPay
 * @author     Mantas Zimnickas <mantas@evp.lt>
 * @license    http://www.gnu.org/licenses/lgpl.html
 * @version    1.0
 * @link       http://www.webtopay.com/
 */

class WebToPay {

    /**
     * WebToPay Library version.
     */
    const VERSION = '1.0';


    /**
     * Server URL where all requests should go.
     */
    const PAY_URL = 'https://www.mokejimai.lt/pay/';


    /**
     * Idetifies what verification method was used.
     *
     * Values can be:
     *  - false     not verified
     *  - RESPONSE  only response parameters are verified
     *  - SS1       SS1 verification
     *  - SS2       SS2 verification
     */
    public static $verified = false;


    /**
     *
     */
    public static function throwResponseError($code) {
        $errors = array(
                '0x1'   => self::_('Mokėjimo suma per maža'),
                '0x2'   => self::_('Mokėjimo suma per didelė'),
                '0x3'   => self::_('Nurodyta valiuta neaptarnaujama'),
                '0x4'   => self::_('Nėra sumos arba valiutos'),
                '0x6'   => self::_('Neįrašytas merchantID arba tokio ID neegzistuoja'),
                '0x7'   => self::_('Išjungtas testavimo rėžimas'),
                '0x8'   => self::_('Jūs uždraudėte šį mokėjimo būdą'),
                '0x9'   => self::_('Blogas "paytext" kintamojo kodavimas (turi būti utf-8)'),
                '0x10'  => self::_('Tuščias arba neteisingai užpildytas "orderID"'),
            );

        if (isset($errors[$code])) {
            $msg = $errors[$code];
        }
        else {
            $msg = self::_('Nenumatyta klaida');
        }

        throw new WebToPayException($msg);
    }


    /**
     * Returns list of supported payment types.
     *
     * Array structure:
     *     0 - Country code
     *     1 - Payment type code
     *     2 - Minimal possible amount that can be transfered in cents.
     *     3 - Maximal possible amount that can be transfered in cents.
     *     4 - Human readable description.
     *
     * Min/max amount equal to 0 means unlimited.
     *
     * @return array
     */
    public static function getPaymentTypes() {
        return array(
            array(
                    'LT', 'hanza', 200, 0,
                    'Swedbanko el. banko sistema. (swedbank.lt)'
                ),
            array(
                    'LT', 'vb2', 500, 0,
                    'SEB banko el. banko sistema'
                ),
            array(
                    'LT', 'nord', 500, 0,
                    'DnB Nord banko el. banko sistema. (I-linija)'
                ),
            array(
                    'LT', 'snoras', 100, 0,
                    'Snoras banko el. banko sistema (Bankas Internetu+)'
                ),
            array(
                    'LT', 'sampo', 100, 0,
                    'Danske banko el. banko sistema'
                ),
            array(
                    'LT', 'parex', 100, 0,
                    'Parex banko el.banko sistema'
                ),
            array(
                    'LT', 'ukio', 100, 0,
                    'Ūkio banko el. banko sistema Eta Bankas'
                ),
            array(
                    'LV', 'nordealv', 100, 0,
                    'Nordea Bank Filnland Plc Internetinės bankininkystės '.
                    'sistema.'
                ),
            array(
                    'LT', 'nordealt', 100, 0,
                    'Nordea Bank Filnland Plc Internetinės bankininkystės '.
                    'sistema.'
                ),
            array(
                    'LT', 'sb', 100, 0,
                    'Šiaulių banko SB Linija'
                ),
            array(
                    'LT', 'barcode', 1000, 200000,
                    'Apmokėjimas Lietuvos spaudos kioskuose'
                ),
            array(
                    'LV', 'hanzalv', 800, 0,
                    'Swedbanko el. banko sistema Latvijoje'
                ),
            array(
                    'EE', 'nordeaee', 100, 0,
                    'Nordea banko Net bank sistema Estijoje'
                ),
            array(
                    'EE', 'hanzaee', 1200, 0,
                    'Swedbanko el. banko sistema Estijoje'
                ),
            array(
                    'LT', 'maximalt', 100, 1000000,
                    'Atsiskaitymas visose Maxima parduotuvių kasose Lietuvoje'
                ),
            array(
                    'LV', 'maximalv', 100, 1000000,
                    'Atsiskaitymas visose Maxima parduotuvių kasose Latvijoje '.
                    '(jau greitai)'
                ),
            array(
                    '', 'paypal', 5000, 1000000,
                    'Paypal sistema (tik pagal atskirą susitarimą)'
                ),
            array(
                    '', 'webmoney', 100, 0,
                    'Atsiskaitymas virtualių pinigų sistema webmoney.ru'
                ),
            array(
                    'LT', 'wap', 100, 1000,
                    'Atsiskaitymas wap svetainėse. Norėdami naudoti šį mokėjimo '.
                    'būdą jus turite pasirašyti Mikro mokėjimų sutartį.'
                ),
            array(
                    '', 'sms', 1, 15000,
                    'Atsiskaitymas padidinto tarifo trumposiomis žinutėmis (SMS '.
                    'Bank).  Norėdami naudoti ši mokėjimo būda jus turite '.
                    'pasirašyti Mikro mokėjimų sutartį.'
                ),
            array(
                    'LT', 'EME', 1, 0,
                    'Atsiskaitymas virtualiais mokėjimai.lt dovanų pinigais '.
                    '(jau greitai)'
                ),
            array(
                    'LT', 'coupon', 1, 0,
                    'Atsiskaitymas mokėjimai.lt dovanų kuponais'
                ),
        );
    }


    /**
     * Returns specification array for request.
     *
     * @return array
     */
    public static function getRequestSpec() {
        // Array structure:
        //  * name      – request item name
        //  * maxlen    – max allowed value for item
        //  * required  – is this item is required
        //  * user      – if true, user can set value of this item, if false
        //                item value is generated
        //  * isrequest – if true, item will be included in request array, if
        //                false, item only be used internaly and will not be
        //                included in outgoing request array.
        //  * regexp    – regexp to test item value
        return array(
                array('merchantid',     11,     true,   true,   true,   '/^\d+$/'),
                array('orderid',        40,     true,   true,   true,   '/^\d+$/'),
                array('lang',           3,      false,  true,   true,   '/^[a-z]{3}$/i'),
                array('amount',         11,     false,  true,   true,   '/^\d+$/'),
                array('currency',       3,      false,  true,   true,   '/^[a-z]{3}$/i'),
                array('accepturl',      255,    true,   true,   true,   ''),
                array('cancelurl',      255,    true,   true,   true,   ''),
                array('callbackurl',    255,    true,   true,   true,   ''),
                array('payment',        20,     false,  true,   true,   ''),
                array('country',        2,      false,  true,   true,   '/^[a-z]{2}$/i'),
                array('paytext',        0,     false,  true,   true,   ''),
                array('logo',           0,      false,  true,   true,   ''),
                array('p_firstname',    255,    false,  true,   true,   ''),
                array('p_lastname',     255,    false,  true,   true,   ''),
                array('p_email',        255,    false,  true,   true,   ''),
                array('p_street',       255,    false,  true,   true,   ''),
                array('p_city',         255,    false,  true,   true,   ''),
                array('p_state',        20,     false,  true,   true,   ''),
                array('p_zip',          20,     false,  true,   true,   ''),
                array('p_countrycode',  3,      false,  true,   true,   '/^[a-z]{3}$/i'),
                array('sign',           255,    false,  false,  true,   ''),
                array('sign_password',  255,    true,   true,   false,  ''),
                array('test',           1,      false,  true,   true,   '/^[01]$/'),
            );
    }


    /**
     * Returns specification array for response.
     *
     * @return array
     */
    public static function getResponseSpec() {
        // Array structure:
        //  * name      – request item name
        //  * maxlen    – max allowed value for item
        //  * required  – is this item is required
        //  * mustcheck – this item must be checked by user
        //  * regexp    – regexp to test item value
        return array(
                'merchantid'    => array(11,     true,   true,   '/^\d+$/'),
                'orderid'       => array(40,     true,   true,   '/^\d+$/'),
                'lang'          => array(3,      false,  false,  '/^[a-z]{3}$/i'),
                'amount'        => array(11,     true,   true,   '/^\d+$/'),
                'currency'      => array(3,      true,   true,   '/^[a-z]{3}$/i'),
                'payment'       => array(20,     false,  false,  ''),
                'country'       => array(2,      false,  false,  '/^[a-z]{2}$/i'),
                'paytext'       => array(0,      false,  false,  ''),
                '_ss2'          => array(0,      true,   false,  ''),
                '_ss1'          => array(0,      false,  false,  ''),
                'transaction'   => array(255,    false,  false,  ''),
                'transaction2'  => array(255,    false,  false,  ''),
                'name'          => array(255,    false,  false,  ''),
                'surename'      => array(255,    false,  false,  ''),
                'status'        => array(255,    false,  false,  ''),
                'error'         => array(20,     false,  false,  ''),
                'test'          => array(1,      false,  false,  '/^[01]$/'),

                'siteurl'       => array(0,      false,  false,  ''),
                'sign'          => array(0,      false,  false,  ''),
                'pay_hash'      => array(0,      false,  false,  ''),
                'm_email_pay'   => array(0,      false,  false,  ''),
                'p_email'       => array(0,      false,  false,  ''),
                'type'          => array(0,      false,  false,  ''),
                'payamount'     => array(0,      false,  false,  ''),
                'paycurrency'   => array(0,      false,  false,  ''),
            );
    }

    /**
     * Checks user given request data array.
     * 
     * If any errors occurs, WebToPayException will be raised.
     *
     * This method returns validated request array. Returned array contains
     * only those items from $data, that is needed.
     *
     * @param array     $data
     * @return array
     */
    public static function checkRequestData($data) {
        $request = array();
        $specs = self::getRequestSpec();
        foreach ($specs as $spec) {
            list($name, $maxlen, $required, $user, $isrequest, $regexp) = $spec;
            if (!$user) continue;
            if ($required && !isset($data[$name])) {
                throw new WebToPayException(
                    self::_("'%s' is required but missing.", $name),
                    WebToPayException::E_REQ_MISSING);
            }

            if (!empty($data[$name])) {
                if ($maxlen && strlen($data[$name]) > $maxlen) {
                    throw new WebToPayException(
                        self::_("'%s' value '%s' is too long, %d characters allowed.",
                                $name, $data[$name], $maxlen),
                        WebToPayException::E_REQ_INVALID);
                }

                if ('' != $regexp && !preg_match($regexp, $data[$name])) {
                    throw new WebToPayException(
                        self::_("'%s' value '%s' is invalid.", $name, $data[$name]),
                        WebToPayException::E_REQ_INVALID);
                }
            }

            if ($isrequest && isset($data[$name])) {
                $request[$name] = $data[$name];
            }
        }

        return $request;
    }


    /**
     * Puts signature on request data array.
     */
    public static function signRequest($request, $password) {
        $data = '';
        foreach ($request as $key => $val) {
            if (trim($val) != '') {
                $data .= sprintf("%03d", strlen($val)) . strtolower($val);
            }
        }
        $request['sign'] = md5($data . $password);

        return $request;
    }


    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises WebToPayException.
     *
     * Method accepts single parameter $data of array type. All possible array
     * keys are described here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * @param array     $data       Information about current payment request.
     * @return array
     */
    public static function buildRequest($data) {
        $request = self::checkRequestData($data);
        $request = self::signRequest($request, $data['sign_password']);
        return $request;
    }


	public static function getCert($cert) {
		$fp = fsockopen("downloads.webtopay.com", 80, $errno, $errstr, 30);
		if (!$fp) {
            throw new WebToPayException(
                self::_('Payment check: Can\'t get cert from '.
                        'downloads.webtopay.com/download/%s',
                        $cert),
                WebToPayException::E_RESP_INVALID);
            return false;
        }

        $out = "GET /download/" . $cert . " HTTP/1.1\r\n";
        $out .= "Host: downloads.webtopay.com\r\n";
        $out .= "Connection: Close\r\n\r\n";
    
        $content = '';
        
        fwrite($fp, $out);
        while (!feof($fp)) $content .= fgets($fp, 8192);
        fclose($fp);
        
        list($header, $content) = explode("\r\n\r\n", $content, 2);

        return $content;
	}


	public static function checkResponseCert($response, $cert='public.key') {
		$pKeyP = self::getCert($cert);
		if (!$pKeyP) {
            return false;
        }


		$pKey = openssl_pkey_get_public($pKeyP);
		if (!$pKey) {
            throw new WebToPayException(
                self::_('Can\'t get openssl public key for %s', $cert),
                WebToPayException::E_RESP_INVALID);
        }
		        
		$_SS2 = '';
		foreach ($response as $key => $value) {
            if ($key!='_ss2') $_SS2 .= "{$value}|";
        }
		$ok = openssl_verify($_SS2, base64_decode($response['_ss2']), $pKey);

        if ($ok !== 1) {
            throw new WebToPayException(
                self::_('Can\'t verify SS2 for %s', $cert),
                WebToPayException::E_RESP_INVALID);
        }

        return true;
	}

    public static function checkResponseData($response, $mustcheck_data) {
        $resp_keys = array();
        $specs = self::getResponseSpec();
        foreach ($specs as $name => $spec) {
            list($maxlen, $required, $mustcheck, $regexp) = $spec;
            if ($required && !isset($response[$name])) {
                throw new WebToPayException(
                    self::_("'%s' is required but missing.", $name),
                    WebToPayException::E_RESP_MISSING);
            }

            if ($mustcheck) {
                if (!isset($mustcheck_data[$name])) {
                    throw new WebToPayException(
                        self::_("'%s' must exists in array of second parameter ".
                                "of checkResponse() method.", $name),
                        WebToPayException::E_USER_PARAMS);
                }

                if ($response[$name] != $mustcheck_data[$name]) {
                    throw new WebToPayException(
                        self::_("'%s' yours and requested value is not ".
                                "equal ('%s' != '%s') ",
                                $name, $mustcheck_data[$name], $response[$name]),
                        WebToPayException::E_RESP_INVALID);
                }
            }

            if (!empty($response[$name])) {
                if ($maxlen && strlen($response[$name]) > $maxlen) {
                    throw new WebToPayException(
                        self::_("'%s' value '%s' is too long, %d characters allowed.",
                                $name, $response[$name], $maxlen),
                        WebToPayException::E_RESP_INVALID);
                }

                if ('' != $regexp && !preg_match($regexp, $response[$name])) {
                    throw new WebToPayException(
                        self::_("'%s' value '%s' is invalid.", $name, $response[$name]),
                        WebToPayException::E_RESP_INVALID);
                }
            }

            if (isset($response[$name])) {
                $resp_keys[] = $name;
            }
        }

        // Filter only parameters passed from webtopay
        $_response = array();
        foreach (array_keys($response) as $key) {
            if (in_array($key, $resp_keys)) {
                $_response[$key] = $response[$key];
            }
        }

        return $_response;
    }


    /**
     * Checks and validates respons from WebToPay server.
     *
     * First parameter usualy should by $_GET array.
     *
     * Description about response can be found here:
     * https://www.mokejimai.lt/makro_specifikacija.html
     *
     * If respons is not correct, WebToPayException will be raised.
     *
     * @param array     $response       Response array.
     * @param array     $keys
     * @return void
     */
    public static function checkResponse($response, $mustcheck_data) {
        self::$verified = false;

        $_response = self::checkResponseData($response, $mustcheck_data);
        self::$verified = 'RESPONSE';

        try {
            if (self::checkResponseCert($_response)) {
                self::$verified = 'SS2 public.key';
                return true;
            }
        }
        catch (WebToPayException $e) {
            if (self::checkResponseCert($_response, 'public_old.key')) {
                self::$verified = 'SS2 public_old.key';
                return true;
            }
        }

        return false;
    }


    /**
     * I18n support.
     */
    public static function _() {
        $args = func_get_args();
        if (sizeof($args) > 1) {
            return call_user_func_array('sprintf', $args);
        }
        else {
            return $args[0];
        }
    }

}



class WebToPayException extends Exception {

    /**
     * Missing request variable.
     */
    const E_REQ_MISSING = 1;

    /**
     * Invalid request variable value.
     */
    const E_REQ_INVALID = 2;

    /**
     * Missing response variable.
     */
    const E_RESP_MISSING = 3;

    /**
     * Invalid response variable value.
     */
    const E_RESP_INVALID = 4;

    /**
     * Missing or invalid user given parameters.
     */
    const E_USER_PARAMS = 5;

}

