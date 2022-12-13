<?php
//namespace Controlllers;
final class CarrosController extends BaseController {

    var $Model;
    var $Usuario;

    function __construct(){
		
		require_once("models/CarrosModel.php");
        $this -> Model = new CarrosModel();
		
        require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayCarros = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayCarros, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayCarros);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultCarro( $id ){
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultCarro( $id );
            $result = $this -> Model -> GetConsult();
            $carro = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $carro;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertCarro(){
		
        $lRetorno = true;
        $oCarro = json_decode( file_get_contents("php://input") );
        isset($oCarro -> nomeCarro) ? $arrayCarros["nomeCarro"] = $oCarro -> nomeCarro : $lRetorno = false;
        isset($oCarro -> idEmpresa) ? $arrayCarros["idEmpresa"] = $oCarro -> idEmpresa : $lRetorno = false;

        if($lRetorno){
            $this -> Model -> InsertCarro($arrayCarros);
            $idCarro = $this -> Model -> GetConsult();
    
            $data['idCarro'] = strval($idCarro);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $data;
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(201);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }

    }

    public function UpdateCarro( $idCarro ){
        
        $lRetorno = true;
		$oCarro = json_decode(file_get_contents("php://input"));
        
		if( empty( $idCarro ) ){
			$idCarro = $oCarro -> idCarro;
		}
        
        if( !empty($idCarro)){
            $arrayCarros["idCarro"] = $idCarro;
            isset($oCarro -> nomeCarro) ? $arrayCarros["nomeCarro"] = $oCarro -> nomeCarro : $lRetorno = false;
            isset($oCarro -> idEmpresa) ? $arrayCarros["idEmpresa"] = $oCarro -> idEmpresa : $lRetorno = false;            
            if( $lRetorno ){
                $this -> Model -> UpdateCarro($arrayCarros);
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

    public function DeleteCarro( $idCarro ){

		if( empty( $idCarro ) ){
			$idCarro = json_decode(file_get_contents("php://input")) -> idCarro;
		}
        if( !empty($idCarro)){
            $this -> Model -> DeleteCarro( $idCarro );
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