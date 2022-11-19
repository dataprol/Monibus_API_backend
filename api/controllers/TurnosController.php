<?php
//namespace Controlllers;
final class TurnosController extends BaseController {
   
    function __construct(){
		
		require_once("models/TurnosModel.php");
        $this -> Model = new TurnosModel();
		
		require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayTurnos = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayTurnos, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayTurnos);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultTurno( $id ){
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultTurno( $id );
            $result = $this -> Model -> GetConsult();
            $turno = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $turno;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertTurno(){
        
        $lRetorno = true;
        $oTurno = json_decode( file_get_contents("php://input") );
        isset($oTurno -> horarioInicialTurno) ? $arrayTurnos["horarioInicialTurno"] = $oTurno -> horarioInicialTurno : $lRetorno = false;
        isset($oTurno -> horarioFinalTurno) ? $arrayTurnos["horarioFinalTurno"] = $oTurno -> horarioFinalTurno : $lRetorno = false;
        isset($oTurno -> nomeTurno) ? $arrayTurnos["nomeTurno"] = $oTurno -> nomeTurno : $lRetorno = false;
        if($lRetorno){
            $this -> Model -> InsertTurno($arrayTurnos);
            $idTurno = $this -> Model -> GetConsult();
    
            $data['idTurno'] = strval($idTurno);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $data;
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(201);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }

    }

    public function UpdateTurno( $idTurno ){
        
        $lRetorno = true;
		$oTurno = json_decode(file_get_contents("php://input"));
        
		if( empty( $idTurno ) ){
			$idTurno = $oTurno -> idTurno;
		}
        
        if( !empty($idTurno)){
            $arrayTurnos["idTurno"] = $idTurno;
            isset($oTurno -> horarioInicialTurno) ? $arrayTurnos["horarioInicialTurno"] = $oTurno -> horarioInicialTurno : $lRetorno = false;
            isset($oTurno -> horarioFinalTurno) ? $arrayTurnos["horarioFinalTurno"] = $oTurno -> horarioFinalTurno : $lRetorno = false;
            isset($oTurno -> nomeTurno) ? $arrayTurnos["nomeTurno"] = $oTurno -> nomeTurno : $lRetorno = false;
            if( $lRetorno ){
                $this -> Model -> UpdateTurno($arrayTurnos);
                $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
                header('Content-Type: application/json');
                echo json_encode($retorno);
                http_response_code(200);
            }else{
                $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
            }
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
        
    }

    public function DeleteTurno( $idTurno ){

		if( empty( $idTurno ) ){
			$idTurno = json_decode(file_get_contents("php://input")) -> idTurno;
		}
        if( !empty($idTurno)){
            $this -> Model -> DeleteTurno( $idTurno );
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }    

    }
}

?>