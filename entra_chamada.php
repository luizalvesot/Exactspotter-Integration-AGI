#!/usr/bin/php -q
<?php

/**
 *
 * Arquivo resposável por gravar informações da ligação no banco EXACTSPOTTER.
 *
 **/

require __DIR__ . '/geral.php';

/**
 *
 * Informações enviadas pelo contexto /etc/asterisk/easytec/exactspotter_ativo.conf
 * ${source},${destination},${CHANNEL},${status},${UNIQUEID},${filename} 
 * 
 **/

$data = [
    'agent_number'  => trim($argv[1]),
    'client_number' => trim($argv[2]),
    'channel'       => trim($argv[3]),
    'status'        => trim($argv[4]),
    'uniqueid'      => trim($argv[5]),
    'recordingfile' => trim($argv[6]),
];

$query = "INSERT INTO call_out_exactspotter
                (
                    agent_number, client_number, channel, status, uniqueid, recordingfile
                )
            VALUES
                (
                    :agent_number, :client_number, :channel, :status, :uniqueid, :recordingfile
                )";

insertData($query, $data);

return 0;
