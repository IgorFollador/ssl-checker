<?php
$file = file('websites.txt');

$today = date('d-m-Y');

$vencidos = '';
$erros = '';
$ativos = '';

echo "HOJE: $today ('d-m-Y')" . PHP_EOL;
foreach ($file as $link) {
    $url = trim($link);
    $url = str_replace(array("\r", "\n"), '', $url);
    if (empty($url)) {
        continue;
    }

    // REQUEST TO URL
    $orignal_parse = parse_url($url, PHP_URL_HOST);
    $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
    $read = @stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);

    if ($errstr || empty($read)) {
        $erros .= "$url -> NAO SUPORTA SSL" . PHP_EOL;
        continue;
    }

    // GET SSL CERTIFCATE INFORMATION
    $cert = stream_context_get_params($read);
    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
    $valid_to = date('d-m-Y', $certinfo['validTo_time_t']);

    $expDate =  date_create($valid_to);
    $todayDate = date_create($today);
    $diff =  date_diff($todayDate, $expDate);



    // OUTPUT
    if ($diff->format("%R%a") > 0) {
        $ativos .= '----------------------------------------------------------' . PHP_EOL;
        $ativos .= $url . PHP_EOL;
        $ativos .= "ATIVO -> VALIDADE ATÉ: $valid_to" . PHP_EOL;
        $ativos .= "Restam ".$diff->format("%R%a dias") . PHP_EOL;

    } else {
        $vencidos .= '----------------------------------------------------------' . PHP_EOL;
        $vencidos .= $url . PHP_EOL;
        $vencidos .= "VENCIDO -> VALIDADE ATÉ: $valid_to" . PHP_EOL;
    }
    echo '■';
}
echo PHP_EOL;

echo '===========================' . PHP_EOL;
echo '          VENCIDOS' . PHP_EOL;
echo '===========================' . PHP_EOL;
echo $vencidos . PHP_EOL;

echo '===========================' . PHP_EOL;
echo '            ERROS' . PHP_EOL;
echo '===========================' . PHP_EOL;
echo $erros . PHP_EOL;

echo '===========================' . PHP_EOL;
echo '           ATIVOS' . PHP_EOL;
echo '===========================' . PHP_EOL;
echo $ativos;


