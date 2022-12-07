<?php
//namespace Models;
final class PessoasModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nIdRelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM pessoas";
        if( ! is_null($nIdRelacionamento) ){
            $sql .= ", responsaveis_tem_dependentes";
            $sql .= " WHERE responsaveis_tem_dependentes.idResponsavel = $nIdRelacionamento";
            $sql .= " and pessoas.idPessoa = responsaveis_tem_dependentes.idDependente";
        }
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nIdRelacionamento ){
        
        $colunas = "*";
        $sql = "SELECT $colunas";
        if( ! is_null($nIdRelacionamento) ){
            $sql .= ", responsaveis_tem_dependentes";
            $sql .= " WHERE responsaveis_tem_dependentes.idResponsavel = $nIdRelacionamento";
            $sql .= " and pessoas.idPessoa = responsaveis_tem_dependentes.idDependente";
        }
        $sql .= " FROM pessoas";
        $sql .= " ORDER BY dataCadastroPessoa DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultPessoa($id){

        $sql = "SELECT * FROM pessoas WHERE idPessoa = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertPessoa($arrayPessoa){

        $sql = "INSERT INTO pessoas(
                `nomePessoa`,
                `identidadePessoa`,
                `emailPessoa`,
                `tipoPessoa`,
                `usuarioPessoa`,
                `senhaPessoa`,
                `dataNascimentoPessoa`,
                `telefone1Pessoa`,
                `enderecoLogradouroPessoa`,
                `enderecoNumeroPessoa`,
                `enderecoBairroPessoa`,
                `enderecoMunicipioPessoa`,
                `enderecoUFPessoa`,
                `enderecoCEPPessoa`,
                `enderecoIBGEPessoa`,
                `enderecoSIAFIPessoa`,
                `enderecoGIAPessoa` ) 
                VALUE(
                    '" . $arrayPessoa["nomePessoa"] . "', 
                    '" . $arrayPessoa['identidadePessoa'] . "', 
                    '" . $arrayPessoa['emailPessoa'] . "', 
                    '" . $arrayPessoa['tipoPessoa'] . "', 
                    '" . $arrayPessoa['usuarioPessoa'] . "', 
                    '" . $arrayPessoa['senhaPessoa'] . "', 
                    '" . $arrayPessoa['dataNascimentoPessoa'] . "',
                    '" . $arrayPessoa['telefone1Pessoa'] . "', 
                    '" . $arrayPessoa['enderecoLogradouroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoNumeroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoBairroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoMunicipioPessoa'] . "', 
                    '" . $arrayPessoa['enderecoUFPessoa'] . "', 
                    '" . $arrayPessoa['enderecoCEPPessoa'] . "', 
                    '" . $arrayPessoa['enderecoIBGEPessoa'] . "', 
                    '" . $arrayPessoa['enderecoSIAFIPessoa'] . "', 
                    '" . $arrayPessoa['enderecoGIAPessoa'] . "'
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdatePessoa($arrayPessoa){

        $sql = "UPDATE pessoas 
            SET nomePessoa='" . $arrayPessoa['nomePessoa'] . "'
                ,emailPessoa='" . $arrayPessoa['emailPessoa'] . "'
                ,identidadePessoa='" . $arrayPessoa['identidadePessoa'] . "'
                ,dataNascimentoPessoa='" . $arrayPessoa['dataNascimentoPessoa'] . "' 
                ,telefone1Pessoa='" . $arrayPessoa['telefone1Pessoa'] . "' 
                ,enderecoLogradouroPessoa='" . $arrayPessoa['enderecoLogradouroPessoa'] . "' 
                ,enderecoNumeroPessoa='" . $arrayPessoa['enderecoNumeroPessoa'] . "' 
                ,enderecoBairroPessoa='" . $arrayPessoa['enderecoBairroPessoa'] . "' 
                ,enderecoMunicipioPessoa='" . $arrayPessoa['enderecoMunicipioPessoa'] . "' 
                ,enderecoCEPPessoa='" . $arrayPessoa['enderecoCEPPessoa'] . "' 
                ,enderecoIBGEPessoa='" . $arrayPessoa['enderecoIBGEPessoa'] . "' 
                ,enderecoSIAFIPessoa='" . $arrayPessoa['enderecoSIAFIPessoa'] . "' 
                ,enderecoGIAPessoa='" . $arrayPessoa['enderecoGIAPessoa'] . "' 
            WHERE idPessoa=" . $arrayPessoa['idPessoa'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeletePessoa($id){

        $sql = "DELETE FROM pessoas WHERE idPessoa='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

    public function consultUsuario( $username ){

        $sql = "SELECT * FROM pessoas 
                WHERE usuarioPessoa='$username'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultUsuarioEmail( $emailPessoa ){
        
        $sql = "SELECT * FROM pessoas 
                WHERE emailPessoa='$emailPessoa'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }

}

?>