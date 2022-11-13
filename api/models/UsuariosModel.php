<?php

class UsuariosModel{

    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> openConnect();
        $this -> Conn = $oConexao -> getConnect();        

    }

    public function consultaUsuario( $username ){

        $sql = "SELECT * FROM pessoas 
                WHERE usuarioPessoa='$username'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }
    
    public function consultaUsuariosSession( $sessionId ){
        
        $sql = "SELECT * FROM pessoas 
                WHERE sessaoPessoa='$sessionId'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function AtualizarUsuario( $arrayUsuarios ){
        $sql = "UPDATE pessoas 
                SET sessaoPessoa='" . $arrayUsuarios["sessaoPessoa"] . "'
                WHERE idPessoa=" . $arrayUsuarios["idPessoa"];
        $this -> resultado = $this -> Conn -> query($sql);
    }

    public function getConsult(){

        return $this -> resultado;

    }
}