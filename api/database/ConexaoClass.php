<?php

class ConexaoClass{
    
    var $oConexaoInterna;

    public function OpenConnect(){

        global $db;

		$servername = $db["hostname"];
        $username = $db["username"];
        $password = $db["password"];
		$dbname = $db["database"];

        $this -> oConexaoInterna = new mysqli( $servername, $username, $password, $dbname, null, null );
        if( $this -> oConexaoInterna -> connect_error ){
            die( "Conexão com banco de dados " . $dbname . " falhou: " . $this -> oConexaoInterna -> connect_error );
        }
        $this -> oConexaoInterna -> set_charset('utf8');
    }

    public function GetConnect(){
        return $this -> oConexaoInterna;
    }

    public function CloseConnect(){

        if( isset( $oConexaoInterna ) ){
            $oConexaoInterna -> close();
        }

    }

}