<?php

class ConexaoClass{
    
    var $oConexaoInterna;

    public function openConnect(){

        global $db;

		$servername = $db["hostname"];
        $username = $db["username"];
        $password = $db["password"];
		$dbname = $db["database"];

        $this -> oConexaoInterna = new mysqli( $servername, $username, $password, $dbname );
        if( $this -> oConexaoInterna -> connect_error ){
            die( "Conexão com banco de dados " . $dbname . " falhou: " . $this -> oConexaoInterna -> connect_error );
        }
    }

    public function getConnect(){
        return $this -> oConexaoInterna;
    }

    public function closeConnect(){

        if( isset( $oConexaoInterna ) ){
            $oConexaoInterna -> close();
        }

    }

}