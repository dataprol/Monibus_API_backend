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
    
    public function consultaUsuarioId( $userId ){

        $sql = "SELECT * FROM pessoas 
                WHERE idPessoa='$userId'";
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
    
    public function AtualizarSenhaUsuario($arrayUsers){

        $sql = "UPDATE pessoas 
                SET senhaPessoa='" . md5( $arrayUsers['senhaPessoa'] ) . "', 
                WHERE idPessoa=" . $arrayUsers['idPessoa'] ;
                //senhaValidadePessoa='" . $arrayUsers['senhaValidadePessoa'] . "' 
        $this -> resultado = $this -> Conn -> query($sql);
        
    }

}