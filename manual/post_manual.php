#!/usr/bin/php -q

<?php

/**
 *
 * Arquivo resposável por atualizar informações da ligação no banco 
 * EXACTSPOTTER após encerrar a ligação.
 *
 **/

require __DIR__ . '/geral.php';
date_default_timezone_set('America/Sao_Paulo');


$query = "SELECT * FROM call_out_exactspotter WHERE uniqueid='1685552539.839'";
$result = selectFirst($query);

print_r($result);
if (!empty($result)) {
    //Dados rescuperados do BD para enviar pela API
    $data_api = array(
        "agent_number" => $result['agent_number'],
        "agent_id" => 0,
        "client_number" => $result['client_number'],
        "start_at" => $result['created_at'],
        "call_duration" => $result['billsec'],
        "key" => md5(uniqid(rand(), true)) . ".WAV",
        "filename" => $result['recordingfile'],
        "uniqueid" => $result['uniqueid'],
        "disposition" => $result['disposition'],
        "content_type" => 'audio/WAV'
    );

    /**
    * 
    * https://nubbi.easypabx.com.br/api/exactspotter-info
    *
    **/
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://nubbi.easypabx.com.br/api/exactspotter-info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data_api),
        CURLOPT_HTTPHEADER => array(
            'authname: nubbi',
            'authtoken: 03205df4bdca67ac3c575c9a0336bdd2',
            'Content-Type: application/json'
        ),
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $exactspotter_result = json_decode($response);

    $url_ligacao = 'https://nubbi.easypabx.com.br/url-audio?key=' . $data_api["key"];
    $seconds = $result['billsec']/*+ $result['duration']*/;

    if(($seconds == 0) || ($seconds == NULL)){
        $seconds = 0;
    }

    $data_formated = date('Y-m-d H:i:s', strtotime("+{$seconds} seconds", strtotime($result['created_at'])));
    $data_fim = date('Y-m-d H:i:s', strtotime($data_formated));

    $data_api2 = array(
        "UrlLigacao" => $url_ligacao,
        "OrigemTel" => $result['agent_number'],
        "DestinoTel" => substr($result['client_number'], 1),
        "DtInicioChamada" => $result['created_at'],
        "DtFimChamada" => $data_fim,
        "TempoConversacao" => $seconds
    );

    print_r($data_api2);

    /**
     * 
     * https://api.exactspotter.com/v2/call
     * 
     **/

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.exactspotter.com/v2/call", //"https://api.exactspotter.com/v2/call",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data_api2),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'token_exact: 2803a1d3-4961-4b75-a13b-573154383bcf'
        ),
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $exactspotter_result_nubbi = json_decode($response);

    if ($exactspotter_result->status == 'success') {
        $query = "UPDATE call_out_exactspotter SET voice_opened=NOW() WHERE id={$result['id']}";
        updateData($query);
    } else {
        writeLog('encerra_chamada_ativa', $result['id'], json_encode($exactspotter_result));
    }

    if ($exactspotter_result_nubbi->status != 'success') {
        writeLog('api_exactspotter', $result['id'], json_encode($exactspotter_result_nubbi));
    }
}

return 0;
