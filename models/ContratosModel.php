<?php
//namespace Models;
final class ContratosModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nidrelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM contratos";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nidrelacionamento ){

        $sql = "SELECT idContrato, numeroContrato, dataInicio, dataValidade, idEmpresa, idPessoa, idTurno, idLinha";
        $sql .= " FROM contratos";
        $sql .= " ORDER BY idContrato DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultContrato($id){

        $sql = "SELECT * FROM contratos WHERE idContrato = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertContrato($arraycontratos){

        $sql = "INSERT INTO contratos(
                numeroContrato,
                dataInicio, 
                dataValidade, 
                idEmpresa, 
                idPessoa, 
                idTurno, 
                idLinha) 
                VALUE('" . $arraycontratos['numeroContrato'] . "'
                    ,'" . $arraycontratos['dataInicio'] . "'
                    ,'" . $arraycontratos['dataValidade'] . "'
                    ," . $arraycontratos['idEmpresa'] . "
                    ," . $arraycontratos['idPessoa'] . "
                    ," . $arraycontratos['idTurno'] . "
                    ," . $arraycontratos['idLinha'] . "
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdateContrato($arraycontratos){

        $sql = "UPDATE contratos 
            SET numeroContrato='" . $arraycontratos['numeroContrato'] . "'
                ,dataInicio='" . $arraycontratos['dataInicio'] . "'
                ,dataValidade='" . $arraycontratos['dataValidade'] . "'
                ,idEmpresa=" . $arraycontratos['idEmpresa'] . "
                ,idPessoa=" . $arraycontratos['idPessoa'] . "
                ,idTurno=" . $arraycontratos['idTurno'] . "
                ,idLinha=" . $arraycontratos['idLinha'] . "
            WHERE idContrato=" . $arraycontratos['idContrato'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeleteContrato($id){

        $sql = "DELETE FROM contratos WHERE idContrato='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

}

?>