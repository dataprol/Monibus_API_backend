<?php

class usersModel{

    var $resultado;

    function __construct(){
        
        require_once("bd/ConexaoClass.php");

    }

    public function consultUser( $login ){
        
        $oConexao = new ConexaoClass();
        $oConexao -> openConnect();

        $oConsulta = $oConexao -> getConnect();        

        $sql = "SELECT * FROM users WHERE login='" . $login . "'";
        $this -> resultado = $oConsulta -> query( $sql );

    }

    public function getConsult(){

        return $this -> resultado;

    }
}

?>