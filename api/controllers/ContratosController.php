<?php
//namespace Controlllers;
final class ContratosController extends BaseController {
   
    function __construct(){
		
		require_once("models/ContratosModel.php");
        $this -> Model = new ContratosModel();
		
		require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayContratos = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayContratos, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayContratos);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultContrato( $id ){
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultContrato( $id );
            $result = $this -> Model -> GetConsult();
            $contrato = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $contrato;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertContrato(){
        
        $lRetorno = true;
        $oContrato = json_decode( file_get_contents("php://input") );
        isset($oContrato -> numeroContrato) ? $arrayContratos["numeroContrato"] = $oContrato -> numeroContrato : $lRetorno = false;
        isset($oContrato -> dataInicio) ? $arrayContratos["dataInicio"] = $oContrato -> dataInicio : $lRetorno = false;
        isset($oContrato -> dataValidade) ? $arrayContratos["dataValidade"] = $oContrato -> dataValidade : $lRetorno = false;
        isset($oContrato -> idEmpresa) ? $arrayContratos["idEmpresa"] = $oContrato -> idEmpresa : $lRetorno = false;
        isset($oContrato -> idPessoa) ? $arrayContratos["idPessoa"] = $oContrato -> idPessoa : $lRetorno = false;
        isset($oContrato -> idTurno) ? $arrayContratos["idTurno"] = $oContrato -> idTurno : $lRetorno = false;
        isset($oContrato -> idLinha) ? $arrayContratos["idLinha"] = $oContrato -> idLinha : $lRetorno = false;
        /* será que o objeto oContrato não pode 
        ser repassado à Model, ao invés de 
        armazenar cada valor na matriz arrayContratos? */
        if($lRetorno){
            $this -> Model -> InsertContrato($arrayContratos);
            $idContrato = $this -> Model -> GetConsult();
    
            $data['idContrato'] = strval($idContrato);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $data;
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(201);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }

    }

    public function UpdateContrato( $idContrato ){
        
        $lRetorno = true;
		$oContrato = json_decode(file_get_contents("php://input"));
        
		if( empty( $idContrato ) ){
			$idContrato = $oContrato -> idContrato;
		}
        
        if( !empty($idContrato)){
            $arrayContratos["idContrato"] = $idContrato;
            isset($oContrato -> numeroContrato) ? $arrayContratos["numeroContrato"] = $oContrato -> numeroContrato : $lRetorno = false;
            isset($oContrato -> dataInicio) ? $arrayContratos["dataInicio"] = $oContrato -> dataInicio : $lRetorno = false;
            isset($oContrato -> dataValidade) ? $arrayContratos["dataValidade"] = $oContrato -> dataValidade : $lRetorno = false;
            isset($oContrato -> idEmpresa) ? $arrayContratos["idEmpresa"] = $oContrato -> idEmpresa : $lRetorno = false;
            isset($oContrato -> idPessoa) ? $arrayContratos["idPessoa"] = $oContrato -> idPessoa : $lRetorno = false;
            isset($oContrato -> idTurno) ? $arrayContratos["idTurno"] = $oContrato -> idTurno : $lRetorno = false;
            isset($oContrato -> idLinha) ? $arrayContratos["idLinha"] = $oContrato -> idLinha : $lRetorno = false;    
            if( $lRetorno ){
                $this -> Model -> UpdateContrato($arrayContratos);
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

    public function DeleteContrato( $idContrato ){

		if( empty( $idContrato ) ){
			$idContrato = json_decode(file_get_contents("php://input")) -> idContrato;
		}
        if( !empty($idContrato)){
            $this -> Model -> DeleteContrato( $idContrato );
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