<?php
//namespace Models;
final class CarrosModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nidrelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM carros";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nidrelacionamento ){

        $sql = "SELECT idCarro, nomeCarro, idEmpresa, idLinha";
        $sql .= " FROM carros";
        $sql .= " ORDER BY idCarro DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultCarro($id){

        $sql = "SELECT * FROM carros WHERE idCarro = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertCarro($arraycarros){

        $sql = "INSERT INTO carros(
                `nomeCarro`,
                `idEmpresa`) 
                VALUE('" . $arraycarros['nomeCarro'] . "'
                    ," . $arraycarros['idEmpresa'] . "
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdateCarro($arraycarros){

        $sql = "UPDATE carros 
            SET nomeCarro='" . $arraycarros['nomeCarro'] . "'
                ,idEmpresa=" . $arraycarros['idEmpresa'] . "
            WHERE idCarro=" . $arraycarros['idCarro'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeleteCarro($id){

        $sql = "DELETE FROM carros WHERE idCarro='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

}

?>