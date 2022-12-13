<?php
//namespace Models;
final class TurnosModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nidrelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM turnos";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nidrelacionamento ){

        $sql = "SELECT idTurno, nomeTurno, horarioInicialTurno, horarioFinalTurno";
        $sql .= " FROM turnos";
        $sql .= " ORDER BY idTurno DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultTurno($id){

        $sql = "SELECT * FROM turnos WHERE idTurno = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertTurno($arrayturnos){

        $sql = "INSERT INTO turnos(
                nomeTurno,
                horarioInicialTurno,
                horarioFinalTurno) 
                VALUE('" . $arrayturnos['nomeTurno'] . "'
                    ,'" . $arrayturnos['horarioInicialTurno'] . "'
                    ,'" . $arrayturnos['horarioFinalTurno'] . "'
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function UpdateTurno($arrayturnos){

        $sql = "UPDATE turnos 
            SET nomeTurno='" . $arrayturnos['nomeTurno'] . "'
                ,horarioInicialTurno='" . $arrayturnos['horarioInicialTurno'] . "'
                ,horarioFinalTurno='" . $arrayturnos['horarioFinalTurno'] . "'
            WHERE idTurno=" . $arrayturnos['idTurno'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function DeleteTurno($id){

        $sql = "DELETE FROM turnos WHERE idTurno='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

    }

    public function GetConsult(){
        return $this -> resultado;
    }

}

?>