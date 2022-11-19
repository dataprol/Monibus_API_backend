<?php
//namespace Controlllers;
final class LinhasController extends BaseController {
   
    function __construct(){
		
		require_once("models/LinhasModel.php");
        $this -> Model = new LinhasModel();
		
		require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayLinhas = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayLinhas, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayLinhas);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultLinha( $id ){
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultLinha( $id );
            $result = $this -> Model -> GetConsult();
            $linha = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $linha;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertLinha(){
		
        $lRetorno = true;
        $oLinha = json_decode( file_get_contents("php://input") );
        isset($oLinha -> nomeLinha) ? $arrayLinhas["nomeLinha"] = $oLinha -> nomeLinha : $lRetorno = false;
        isset($oLinha -> idEmpresa) ? $arrayLinhas["idEmpresa"] = $oLinha -> idEmpresa : $lRetorno = false;
        isset($oLinha -> geolocalizacaoLinha) ? $arrayLinhas["geolocalizacaoLinha"] = $oLinha -> geolocalizacaoLinha : $lRetorno = false;

        if($lRetorno){
            $this -> Model -> InsertLinha($arrayLinhas);
            $idLinha = $this -> Model -> GetConsult();
    
            $data['idLinha'] = strval($idLinha);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $data;
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(201);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }

    }

    public function UpdateLinha( $idLinha ){
        
        $lRetorno = true;
		$oLinha = json_decode(file_get_contents("php://input"));
        
		if( empty( $idLinha ) ){
			$idLinha = $oLinha -> idLinha;
		}
        
        if( !empty($idLinha)){
            $arrayLinhas["idLinha"] = $idLinha;
            isset($oLinha -> nomeLinha) ? $arrayLinhas["nomeLinha"] = $oLinha -> nomeLinha : $lRetorno = false;
            isset($oLinha -> idEmpresa) ? $arrayLinhas["idEmpresa"] = $oLinha -> idEmpresa : $lRetorno = false;            
            isset($oLinha -> geolocalizacaoLinha) ? $arrayLinhas["geolocalizacaoLinha"] = $oLinha -> geolocalizacaoLinha : $lRetorno = false;            
            if( $lRetorno ){
                $this -> Model -> UpdateLinha($arrayLinhas);
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

    public function DeleteLinha( $idLinha ){

		if( empty( $idLinha ) ){
			$idLinha = json_decode(file_get_contents("php://input")) -> idLinha;
		}
        if( !empty($idLinha)){
            $this -> Model -> DeleteLinha( $idLinha );
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