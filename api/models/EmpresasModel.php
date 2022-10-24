<?php
//namespace Models;
final class EmpresasModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($id){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM empresas";
        if( $id != null ){
            $sql .= ", pessoas_tem_empresas";
            $sql .= " WHERE pessoas_tem_empresas.idPessoa = $id";
            $sql .= " and empresas.idEmpresa = pessoas_tem_empresas.idPessoa";
        }
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $id ){

        $sql = "SELECT idEmpresa, IdentidadeEmpresa, nomeEmpresa, emailEmpresa, telefoneEmpresa,dataCadastroEmpresa";
        if( $id != null ){
            $sql .= ", pessoas_tem_empresas";
            $sql .= " WHERE pessoas_tem_empresas.idPessoa = $id";
            $sql .= " and empresas.idEmpresa = pessoas_tem_empresas.idPessoa";
        }
        $sql .= " FROM empresas";
        $sql .= " ORDER BY idEmpresa DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultEmpresa($id){

        $sql = "SELECT * FROM empresas WHERE idEmpresa = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertEmpresa($arrayempresas){

        $sql = "INSERT INTO empresas(
                `nomeEmpresa`,
                `identidadeEmpresa`,
                `emailEmpresa`,
                `telefoneEmpresa`,
                `dataCadastroEmpresa`) 
                VALUE('" . $arrayempresas['nomeEmpresa'] . "'
                    ,'" . $arrayempresas['identidadeEmpresa'] . "'
                    ,'" . $arrayempresas['emailEmpresa'] . "'
                    ,'" . $arrayempresas['telefoneEmpresa'] . "'
                    ,now()
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdateEmpresa($arrayempresas){

        $sql = "UPDATE empresas 
            SET nomeEmpresa='" . $arrayempresas['nomeEmpresa'] . "'
                ,emailEmpresa='" . $arrayempresas['emailEmpresa'] . "'
                ,telefoneEmpresa='" . $arrayempresas['telefoneEmpresa'] . "'
            WHERE idEmpresa=" . $arrayempresas['idEmpresa'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeleteEmpresa($idEmpresa){

        $sql = "DELETE FROM empresas WHERE idEmpresa='" . $idEmpresa . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

}

?>