<?php
namespace Seeruo;

/**
/**
 * Curl类
 */
class Curl
{
	/**
	 * Curl send get request, support HTTPS protocol
	 * @param string $url The request url
	 * @param string $refer The request refer
	 * @param int $timeout The timeout seconds
	 * @return mixed
	 */
    static public function get($url, $refer = "", $timeout = 10)
    {
        $ssl     = stripos($url, 'https://') === 0 ? true : false;
        $curlObj = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_AUTOREFERER    => 1,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
            CURLOPT_HTTPHEADER     => ['Expect:'],
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        ];
        if ($refer) {
            $options[CURLOPT_REFERER] = $refer;
        }
        if ($ssl) {
            //support https
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        curl_setopt_array($curlObj, $options);
        $returnData = curl_exec($curlObj);
        if (curl_errno($curlObj)) {
            //error message
            $returnData = curl_error($curlObj);
        }
        curl_close($curlObj);
        return $returnData;
    }

	/**
	 * Curl send post request, support HTTPS protocol
	 * @param string $url The request url
	 * @param array $data The post data
	 * @param string $refer The request refer
	 * @param int $timeout The timeout seconds
	 * @param array $header The other request header
	 * @return mixed
	 */
    static public function post($url, $data, $refer = "", $timeout = 10, $header = [])
    {
        $curlObj = curl_init();
        $ssl     = stripos($url, 'https://') === 0 ? true : false;
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_AUTOREFERER    => 1,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
            CURLOPT_HTTPHEADER     => ['Expect:'],
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_REFERER        => $refer,
        ];
        if (!empty($header)) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        if ($refer) {
            $options[CURLOPT_REFERER] = $refer;
        }
        if ($ssl) {
            //support https
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        curl_setopt_array($curlObj, $options);
        $returnData = curl_exec($curlObj);
        if (curl_errno($curlObj)) {
            //error message
            $returnData = curl_error($curlObj);
        }
        curl_close($curlObj);
        return $returnData;
    }
}
