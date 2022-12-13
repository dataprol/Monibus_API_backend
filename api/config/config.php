<?php

    setlocale( LC_ALL, 'pt_BR', 'pt_BR.iso-8859-1', 'pt_BR.utf-8', 'portuguese' );
    date_default_timezone_set( 'America/Sao_Paulo' );
    
    require_once('passwords.php');
    
    // Banco de dados
    $sisConfig['banco_de_dados'] = $bd_remoto;

    // Servidor de email
    $sisConfig['email_servidor'] = $mail_server;

    $db = $bd_remoto;
    $activeConfig = 'prod';
    $cHTTP_SERVER ='/api';
    ini_set( 'display_errors', 1 ); // 0 após lançamento oficial
    ini_set( 'display_startup_errors', 1 ); // 0 após lançamento oficial
    error_reporting( E_ERROR ); //E_ERROR após lançamento oficial

    // Detecta se está em produção ou teste e faz ajustes//
    if( $_SERVER['SERVER_NAME'] == 'localhost' ){

        // Banco de dados
        $sisConfig['banco_de_dados'] = $bd_local;

        // Servidor de email
        $sisConfig['email_servidor'] = $mail_server;

        $db = $bd_local;
        $activeConfig = 'dev';
        $cHTTP_SERVER = '/dev/aulas/Monibus_PDS/api';
        ini_set( 'display_errors', 1 );
        ini_set( 'display_startup_errors', 1 );
        error_reporting( E_ALL );

    }
    
    $aTiposCliente = array
    ( 
    "Administrador",
    "Monitor",
    "Passageiro" 
    );

    // Cria as constantes do sistema
    define( "_FMT_DATA", "%d/%m/%Y" );
    define( "_FMT_DATA_HORA", "%d/%m/%Y %Hh%M" );
    define( "_PaginacaoItensPorPagina", 5 );
    define( "_SisConfigGeral", $sisConfig );
?>