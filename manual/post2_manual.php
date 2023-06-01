#!/usr/bin/php -q

<?php

/**
 *
 * Arquivo resposável por atualizar campo voice_opened no BD para que
 * o áudio da ligação seja enviado logo em seguida.
 * 
 * Deverá ser executado no console 'php post2_manual.php', após isso o campo
 * do cliente será atualizado.
 *
 **/

require __DIR__ . '../geral.php';
date_default_timezone_set('America/Sao_Paulo');


$query = "SELECT * FROM call_out_exactspotter WHERE voice_opened IS NULL";
$result = selectFirst($query);


if (!empty($result)) {
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

   
    if ($exactspotter_result->status == 'success') {
        $query = "UPDATE call_out_exactspotter SET voice_opened=NOW() WHERE id={$result['id']}";
        updateData($query);
    } else {
        writeLog('encerra_chamada_ativa', $result['id'], json_encode($exactspotter_result));
    }
}

return 0;
