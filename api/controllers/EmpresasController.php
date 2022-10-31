<?php
//namespace Controlllers;
final class EmpresasController extends BaseController {
   
    function __construct(){
		
		require_once("models/EmpresasModel.php");
        $this -> Model = new EmpresasModel();
		
    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayEmpresas = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayEmpresas, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayEmpresas);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultEmpresa( $id ){
		
        header( 'Content-Type: application/json' );
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultEmpresa( $id );
            $result = $this -> Model -> GetConsult();
            $empresa = $result -> fetch_assoc();

            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $empresa;
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertEmpresa(){
		
        $oEmpresa = json_decode( file_get_contents("php://input") );

        $arrayEmpresas["nomeEmpresa"] = $oEmpresa -> nomeEmpresa;
        $arrayEmpresas["identidadeEmpresa"] = $oEmpresa -> identidadeEmpresa;
        $arrayEmpresas["emailEmpresa"]  = $oEmpresa -> emailEmpresa;
        $arrayEmpresas["telefoneEmpresa"] = $oEmpresa -> telefoneEmpresa;

        $this -> Model -> InsertEmpresa($arrayEmpresas);
        $idEmpresa = $this -> Model -> GetConsult();

        header('Content-Type: application/json');
        $data['idEmpresa'] = strval($idEmpresa);
        $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
        $retorno['data'] = $data;
        echo json_encode($retorno);
        http_response_code(201);

    }

    public function UpdateEmpresa( $idEmpresa ){

		$oEmpresa = json_decode(file_get_contents("php://input"));
        
		if( empty( $idEmpresa ) ){
			$idEmpresa = $oEmpresa -> idEmpresa;
		}
        
        header('Content-Type: application/json');
        if( !empty($idEmpresa)){
            $arrayEmpresas["idEmpresa"] = $idEmpresa;
            $arrayEmpresas["nomeEmpresa"] = $oEmpresa -> nomeEmpresa;
            $arrayEmpresas["telefoneEmpresa"] = $oEmpresa -> telefoneEmpresa;
            $arrayEmpresas["emailEmpresa"] = $oEmpresa -> emailEmpresa;
            $this -> Model -> UpdateEmpresa($arrayEmpresas);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
        
    }

    public function DeleteEmpresa( $idEmpresa ){

		if( empty( $idEmpresa ) ){
			$idEmpresa = json_decode(file_get_contents("php://input")) -> idEmpresa;
		}
        header('Content-Type: application/json');
        if( !empty($idEmpresa)){
            $this -> Model -> DeleteEmpresa( $idEmpresa );
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }    

    }
}

?>