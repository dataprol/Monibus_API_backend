<?php
//namespace Controlllers;
final class EmpresasController extends BaseController {
   
    var $Model;
    
    function __construct(){
		
		require_once("models/EmpresasModel.php");
        $this -> Model = new EmpresasModel();

		require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

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
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultEmpresa( $id );
            $result = $this -> Model -> GetConsult();
            $empresa = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $empresa;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertEmpresa( $ObjEmpresa ){
		
        $lRetorno = true;
        if( ! isset($ObjEmpresa) ){
            $ObjEmpresa = json_decode( file_get_contents("php://input") );
        }
        $aEmpresa["nomeEmpresa"] = isset($ObjEmpresa -> nomeEmpresa) ? $ObjEmpresa -> nomeEmpresa : $lRetorno = false;
        $aEmpresa["identidadeEmpresa"] = isset($ObjEmpresa -> identidadeEmpresa) ? $ObjEmpresa -> identidadeEmpresa : $lRetorno = false;
        $aEmpresa["emailEmpresa"] = isset($ObjEmpresa -> emailEmpresa) ? $ObjEmpresa -> emailEmpresa : $lRetorno = false;
        $aEmpresa["telefoneEmpresa"] = isset($ObjEmpresa -> telefoneEmpresa) ? $ObjEmpresa -> telefoneEmpresa : null;
        $aEmpresa["idPessoa"] = isset($ObjEmpresa -> idPessoa) ? $ObjEmpresa -> idPessoa : $lRetorno = false;
        $aEmpresa["tipoPessoa"] = isset($ObjEmpresa -> tipoPessoa) ? $ObjEmpresa -> tipoPessoa : $lRetorno = false;

        if($lRetorno){
            
            $this -> Model -> InsertEmpresa($aEmpresa);
            $nIdEmpresa = $this -> Model -> GetConsult();

            if( $nIdEmpresa > 0 ){
                
                $data['idEmpresa'] = strval($nIdEmpresa);
                $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
                $retorno['data'] = $data;
                header('Content-Type: application/json');
                echo json_encode($retorno);
                http_response_code(201);
                
                return $nIdEmpresa;
            
            }else{

                $cMensagemErro = "Sintaxe da empresa incorreta!";

                // Em caso de duplicidade detectada, retornar mensagem apropriada.
                if( $this -> Model -> Conn -> errno == 1062 ){
                    $msgerro = $this -> Model -> Conn -> error;
                    if (!empty($msgerro)) { 
                        $cMensagemErro = $msgerro;
                        $lpos = strpos($msgerro, "'"); 
                        if ($lpos !== false) { 
                            $msgerro = substr($msgerro, $lpos + 1);
                            $npos = strpos($msgerro, "'");
                            $valorDuplicado = substr($msgerro, 0, $npos);
                            $msgerro = substr($msgerro, $npos + 1);
                        } 
                        $lpos = strpos($msgerro, "."); 
                        if ($lpos !== false) { 
                            $msgerro = substr($msgerro, $lpos + 1);
                            $npos = strpos($msgerro, "'");
                            $indiceValorDuplicado = substr($msgerro, 0, $npos);
                        }
                        $msgerro = $cMensagemErro;
                        if($indiceValorDuplicado == 'emailEmpresa_UNIQUE'){
                            $cMensagemErro = "O e-mail $valorDuplicado";
                        }
                        if($indiceValorDuplicado == 'identidadeEmpresa_UNIQUE'){
                            $cMensagemErro = "A identidade $valorDuplicado";
                        }
                        $cMensagemErro .= " já existe no cadastro de outra empresa!";
                    }
                }
                
                $this -> RespostaRuimHTTP(400,$cMensagemErro,"Requisição Mal Feita",0);
                exit;

            }

        }else{

            $this -> RespostaRuimHTTP(400,"Sintaxe incorreta da empresa!","Requisição Mal Feita",0);
            exit;
            
        }
        
        

    }

    public function UpdateEmpresa( $idEmpresa ){

		$oEmpresa = json_decode(file_get_contents("php://input"));
        
		if( empty( $idEmpresa ) ){
			$idEmpresa = $oEmpresa -> idEmpresa;
		}
        
        if( !empty($idEmpresa)){
            $arrayEmpresas["idEmpresa"] = $idEmpresa;
            $arrayEmpresas["nomeEmpresa"] = $oEmpresa -> nomeEmpresa;
            $arrayEmpresas["telefoneEmpresa"] = $oEmpresa -> telefoneEmpresa;
            $arrayEmpresas["emailEmpresa"] = $oEmpresa -> emailEmpresa;
            $this -> Model -> UpdateEmpresa($arrayEmpresas);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            header('Content-Type: application/json');
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
        if( !empty($idEmpresa)){
            $this -> Model -> DeleteEmpresa( $idEmpresa );
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