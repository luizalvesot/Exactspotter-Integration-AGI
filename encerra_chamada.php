#!/usr/bin/php -q

<?php

/**
 *
 * Arquivo resposável por atualizar informações da ligação no banco 
 * EXACTSPOTTER após encerrar a ligação.
 *
 **/

require __DIR__ . '/geral.php';

/**
 *
 * Informações enviadas pelo contexto /etc/asterisk/easytec/exactspotter_ativo.conf
 * ${CDR(UNIQUEID)}, finalized, ${CDR(disposition)}, ${CDR(duration)}, ${CDR(billsec)}
 * 
 **/

$data = [
    'uniqueid'    => trim($argv[1]),
    'status'      => trim($argv[2]),
    'disposition' => trim($argv[3]),
    'duration'    => trim($argv[4]),
    'billsec'     => trim($argv[5]),
];

$query = "UPDATE call_out_exactspotter SET
                duration={$data['duration']},
                billsec={$data['billsec']},
                status='{$data['status']}',
                disposition='{$data['disposition']}'
            WHERE uniqueid='{$data['uniqueid']}'";

updateData($query);

$query = "SELECT * FROM call_out_exactspotter WHERE uniqueid='{$data['uniqueid']}'";
$result = selectFirst($query);

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

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "http://10.254.0.199/api/exactspotter-info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data_api),
        CURLOPT_HTTPHEADER => array(
            'authname: nubbi_salesforce',
            'authtoken: 4f5a3723a0d00d5e08c9d9848f082071',
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
