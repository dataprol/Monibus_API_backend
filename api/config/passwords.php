<?php

    /* Banco de Dados */
    $bd_local = array(
    'hostname' => 'localhost', 
    'username' => 'root',
    'password' => 'Xsdzlca#$*494875',
    'database' => 'monibus');

    $bd_remoto = array(
    'hostname' => 'monibus.tecnologia.ws', 
    'username' => '',
    'password' => '',
    'database' => '');

    /* Substitui o BD local pelo remoto, nos testes */
    //$bd_local = $bd_remoto;
        
    /* e-mail */
    $mail_server = array(
    'hostname' => 'email-ssl.com.br',
    'username' => 'sac@monibus.tecnologia.ws',
    'password' => '');

    /* Chaves de APIs */
    define( "_GOOGLE_API_KEY", "AIzaSyCgjttK9hotGBFFlF87V2k-Gn3tuqji_2A" );

?>