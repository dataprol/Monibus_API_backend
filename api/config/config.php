<?php

	//session_cache_expire(21600); 	// 21600 minutos são 15 dias. 
									// Cuidado! Dados ficarão gravados por todo esse tempo!
                                    // Risco de sobrecarga e de falha de segurança!

    // Configurações da instalação no servidor
    require_once('passwords.php');

    // Detecta se está em produção ou teste e faz ajustes//
    if( $_SERVER['SERVER_NAME'] == 'localhost' ){

        $cHTTP_SERVER = '/dev/aulas/Monibus_PDS/api';

        $activeConfig = 'dev';

        $db = $bd_local;

        ini_set( 'display_errors', 1 );
        ini_set( 'display_startup_errors', 1 );
        error_reporting( E_ALL );

    }else{
        
        $cHTTP_SERVER ='/api';

        $activeConfig = 'prod';

        $db = $bd_remoto;

        ini_set( 'display_errors', 0 );
        ini_set( 'display_startup_errors', 0 );
        error_reporting( E_ERROR );

    }
    
    $aTiposCliente = array
    ( 
    "Proprietário",
    "Administrador",
    "Monitor",
    "Cliente",
    "Passageiro" 
    );

    $sistemacobranca = array
    ( 
    'token' => '7aW5mH5-l144tm8u-944v-qghg-a1rira77',
    'url'   => 'https://www.f2b.com.br/api/v1' 
    );

    // Cria as constantes do sistema
    define( "_FMT_DATA", "%d/%m/%Y" );
    define( "_FMT_DATA_HORA", "%d/%m/%Y %Hh%M" );
    define( "_PaginacaoItensPorPagina", 5 );
?>