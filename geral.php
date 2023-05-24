<?php

/**
 * 
 * Pega informações do arquivo config.ini.
 * 
 **/
function getConfig($section)
{
    return parse_ini_file(__DIR__ . '/config.ini', true)[$section];
}

/**
 * 
 * Função que faz a conexão no BD usando os parâmetros do arquivo .ini.
 * 
 **/
function getConn()
{
    $config = getConfig('database');

    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8";
    $user = $config['user'];
    $password = $config['password'];
    $options = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    ];

    return new PDO($dsn, $user, $password, $options);
}

/**
 * 
 * Escreve dados no DB.
 * 
 **/
function insertData($query, $data)
{
    $db = getConn();

    return $db->prepare($query)->execute($data);
}

/**
 * 
 * Busca todos os registros que correspondem a clausula informada.
 * 
 **/
function selectAll($query)
{
    $db = getConn();

    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 
 * Busca o primeiro registro do BD que corresponde à cláusula informada.
 * 
 **/
function selectFirst($query)
{
    $db = getConn();

    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 
 * Atualiza informações no BD.
 * 
 **/
function updateData($query)
{
    $db = getConn();

    return $db->prepare($query)->execute();
}

/**
 * 
 * Função usada para gravar logs na pasta logs em
 * em casos de erro da API ou envio da gravação.
 * 
 **/
function writeLog($section, $id, $json_data)
{
    date_default_timezone_set('America/Sao_Paulo');

    $file = __DIR__ . '/logs/' . date('Y') . '-' . date('m') . '-' . date('d') . '.log';

    $time = date('H') . ':' . date('i') . ':' . date('s');

    $text = "[{$time}]" . PHP_EOL;
    $text .= "Section: {$section}" . PHP_EOL;
    $text .= "ID: {$id}" . PHP_EOL . "{$json_data}";
    $text .= PHP_EOL . PHP_EOL . PHP_EOL;

    $fp = fopen($file, 'a+');
    fwrite($fp, $text);
    fclose($fp);
}

/**
 * 
 * Envia arquivos via rsync para o servidor remoto.
 * 
 **/
function sendFiles($file)
{
    $config = getConfig('rsync_destination');

    $pieces = explode('/', $file);
    $destination_host = $config['host'];
    $destination_path = $config['path'] . "{$pieces[0]}/{$pieces[1]}/{$pieces[2]}/";
    $base_path = '/var/spool/asterisk/monitor/';

    if (file_exists($base_path . $file)) {
        $command = "rsync -avz -e 'ssh -p {$config['port']}' {$base_path}{$file} {$config['user']}@{$destination_host}:{$destination_path}";

        shell_exec("ssh {$config['user']}@{$destination_host} -p {$config['port']}  mkdir -p {$destination_path}");

        return shell_exec($command);
    } else {
        writeLog('php_geral_send_files', 0, 'File: ' . $base_path . $file);

        return null;
    }
}