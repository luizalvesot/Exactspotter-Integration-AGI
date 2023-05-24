#!/usr/bin/php -q
<?php

require __DIR__ . '/geral.php';

$base_path = '/var/spool/asterisk/monitor/';


/**
 * 
 * Upload chamadas de saída do Exactspotter para https://nubbi.easypabx.com.br/var/www/exactspotter-recordings/
 * 
 **/

    //Abaixo está a configuração do crontab para configurar no servidor PABX
    //*/1 * * * * php /var/lib/asterisk/agi-bin/easytec/exactspotter/send_files.php

$query = "SELECT * FROM call_out_exactspotter WHERE voice_opened IS NOT NULL AND recording_sent IS NULL";

$calls = selectAll($query);

if (!empty($calls)) {
    foreach ($calls as $call) {
        
        $result = sendFiles($call['recordingfile']);

        if (file_exists($base_path . $call['recordingfile'])) {

            $query = "UPDATE call_out_exactspotter SET recording_sent=NOW() WHERE id={$call['id']}";
            updateData($query);
        }
    }
}
