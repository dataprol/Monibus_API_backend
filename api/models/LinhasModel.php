<?php
//namespace Models;
final class LinhasModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nidrelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM linhas";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nidrelacionamento ){

        $sql = "SELECT idLinha, nomeLinha, idEmpresa, geolocalizacaoLinha";
        $sql .= " FROM linhas";
        $sql .= " ORDER BY idLinha DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultLinha($id){

        $sql = "SELECT * FROM linhas WHERE idLinha = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertLinha($arraylinhas){

        $sql = "INSERT INTO linhas(
                `nomeLinha`,
                `idEmpresa`) 
                VALUE('" . $arraylinhas['nomeLinha'] . "'
                    ," . $arraylinhas['idEmpresa'] . "
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdateLinha($arraylinhas){

        $sql = "UPDATE linhas 
            SET nomeLinha='" . $arraylinhas['nomeLinha'] . "'
                ,idEmpresa=" . $arraylinhas['idEmpresa'] . "
                ,geolocalizacaoLinha='" . $arraylinhas['geolocalizacaoLinha'] . "'
            WHERE idLinha=" . $arraylinhas['idLinha'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeleteLinha($id){

        $sql = "DELETE FROM linhas WHERE idLinha='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

}

?>