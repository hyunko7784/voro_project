<?php
function shopifyCall($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
    $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
    if (!is_null($query) && in_array($method, array('GET',  'DELETE'))) $url = $url . "?" . http_build_query($query);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Shopify App');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    $request_headers[] = "";
    if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

    if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
        if (is_array($query)) $query = http_build_query($query);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
    }

    $response = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_message = curl_error($curl);

    curl_close($curl);

    if ($error_number) {
        return $error_message;
    } else {

        $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

        $headers = array();
        $header_data = explode("\n",$response[0]);
        $headers['status'] = $header_data[0];
        array_shift($header_data);
        foreach($header_data as $part) {
            $h = explode(":", $part);
            $headers[trim($h[0])] = trim($h[1]);
        }

        return array('headers' => $headers, 'response' => $response[1]);

    }

}

$timezone = "America/Bahia_Banderas";
date_default_timezone_set($timezone);

$token = "";
$shop = "voromotors";

$storeURL = "https://" . $shop . ".myshopify.com";

$sd = new DateTime($_GET['start_date'] . " 00:00:00");
$ed = new DateTime($_GET['end_date'] . " 23:59:59");

$array = array(
    'created_at_min' => $sd->format(DATE_ATOM),
    'created_at_max' => $ed->format(DATE_ATOM),
);
$orders = shopifyCall($token, $shop, "/admin/api/2022-07/orders.json", $array, 'GET');
$orders = json_decode($orders['response']);

echo json_encode($orders);
