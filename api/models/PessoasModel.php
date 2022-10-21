<?php

class PessoasModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> openConnect();
        $this -> Conn = $oConexao -> getConnect();

    }

    public function CountRows($id){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM pessoas";
        if( $id != null ){
            $sql .= ", responsaveis_tem_dependentes";
            $sql .= " WHERE responsaveis_tem_dependentes.idResponsavel = $id";
            $sql .= " and pessoas.idPessoa = responsaveis_tem_dependentes.idDependente";
        }
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function listThis( $nComecarPor, $nItensPorPagina, $id ){

        $sql = "SELECT idPessoa, nomePessoa, emailPessoa, usuarioPessoa";
        if( $id != null ){
            $sql .= ", responsaveis_tem_dependentes";
            $sql .= " WHERE responsaveis_tem_dependentes.idResponsavel = $id";
            $sql .= " and pessoas.idPessoa = responsaveis_tem_dependentes.idDependente";
        }
        $sql .= " FROM pessoas";
        $sql .= " ORDER BY dataCadastroPessoa DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function consultPessoa($id){

        $sql = "SELECT * FROM pessoas WHERE idPessoa = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function insertPessoa($arraypessoas){

        $sql = "INSERT INTO pessoas(
                `nomePessoa`,
                `identidadePessoa`,
                `emailPessoa`,
                `tipoPessoa`,
                `senhaPessoa`,
                `usuarioPessoa`,
                `dataCadastroPessoa`) 
                VALUE(
                    '" . $arraypessoas['nomePessoa'] . "', 
                    '" . $arraypessoas['identidadePessoa'] . "', 
                    '" . $arraypessoas['emailPessoa'] . "', 
                    '" . $arraypessoas['tipoPessoa'] . "'
                    '" . $arraypessoas['senhaPessoa'] . "'
                    '" . $arraypessoas['usuarioPessoa'] . "'
                    now()
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function updatePessoa($arraypessoas){
        $sql = "UPDATE pessoas 
            SET nomePessoa='" . $arraypessoas['nomePessoa'] . "', 
                emailPessoa='" . $arraypessoas['emailPessoa'] . "', 
                telefone1Pessoa='" . $arraypessoas['telefone1Pessoa'] . "', 
                senhaPessoa='" . $arraypessoas['senhaPessoa'] . "' 
        WHERE idPessoa=" . $arraypessoas['idPessoa'] . ";" ;
        $this -> resultado = $this -> Conn -> query($sql);
    }

    public function deletePessoa($idPessoa){
        $sql = "DELETE FROM pessoas WHERE idPessoa='" . $idPessoa . "';" ;
        $this -> resultado = $this -> Conn-> query($sql);
    }

    public function getConsult(){
        return $this -> resultado;
    }

}

?>