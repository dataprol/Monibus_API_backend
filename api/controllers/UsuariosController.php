<?php

class UsuariosController extends BaseController{
	
	var $Model;
	var $SenhaToken;

	function __construct(){

        require_once("models/UsuariosModel.php");
        $this -> Model = new UsuariosModel();
		$this -> SenhaToken = 'Monibus%$#@!LuizCarlosDataprol19772022';

	}

	public function Index(){
		
        $this -> ValidateTokenAction();

	}
	
	public function ValidateToken(){
		
		if( $this -> ValidateTokenAction() ){
			$this -> RespostaBoaHTTP(200,"Token validado!");
		}

	}

	public function ValidateTokenAction(){
		
		$lRetorno = false;
		$header = $_SERVER;
		if( array_key_exists( 'HTTP_AUTHORIZATION', $header ) != false ){
			$token = $header['HTTP_AUTHORIZATION'];
			if( is_null( $token ) || empty($token) ){

				$this -> RespostaRuimHTTP(400,"Token não informado!","Requisição Mal Feita",0);
				exit;

			}
			$token = str_replace("Bearer ", "", $token);
			$part = explode(".",$token);
			try {
				$header = $part[0];
				$payload = $part[1];
				$signature = $part[2];
			} catch (Exception $th) {
				$this -> RespostaRuimHTTP(400,"Token não reconhecido! ".$th->getMessage(),"Requisição Mal Feita",0);
				exit;
			}
			$valid = hash_hmac('sha256',"$header.$payload",$this -> SenhaToken,true);
			$valid = base64_encode($valid);
			$valid = str_replace(['+', '/', '='], ['-', '_', ''], $valid);
			if($signature != $valid){

				$this -> RespostaRuimHTTP(400,"Token inválido!","Requisição Mal Feita",0);
				exit;

			}else{
				
				$this -> Model -> consultaUsuariosSession( session_id() );
				$result = $this -> Model -> getConsult();
				if( $result == false ){
					$_SESSION['usuarioSituacao'] = "erro";
					sleep($_SESSION['usuarioErroEsperar']);

					$this -> RespostaRuimHTTP(400,"Requer login! Sessão não autorizada!","Requisição Mal Feita",0);

					session_destroy();
					exit;
				}
			}
			$lRetorno = true;
		}else{
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			$this -> RespostaRuimHTTP(400,"Requer autorização HTTP!","Requisição Mal Feita",0);
			exit;
		}
		return $lRetorno;
		
	}

	public function ValidateLogin(){

		$ModelInfo = json_decode( file_get_contents("php://input") );
		
		if( empty( $ModelInfo ) || empty($ModelInfo -> username) || empty($ModelInfo -> password) ){ 
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			
			$this -> RespostaRuimHTTP(400,"Requer login! Dados em branco!","Requisição Mal Feita",0);
			exit;
		}

		$username = trim( strtolower( $ModelInfo -> username ) );
		$password = trim( $ModelInfo -> password );

		$this -> Model -> consultaUsuario( $username );
		$result = $this -> Model -> getConsult();

		if( $linha = $result -> fetch_assoc() ){
			if( $linha[ 'senhaPessoa' ] == md5($password) ){

				$_SESSION["idPessoa"] 			= $linha["idPessoa"];
				$_SESSION['nomePessoa'] 		= $linha['nomePessoa'];
				$_SESSION['usuarioLogin'] 		= $linha['usuarioPessoa'];
				$_SESSION['usuarioSituacao'] 	= "loged";
				
				if( ! isset( $_SESSION[ "usuarioErroEsperar" ] ) ){
					$_SESSION['usuarioErroEsperar'] = .1;
				}

				$header = [
							'alg' => 'HS256',
							'typ' => 'JWT'
							];
				$header = json_encode($header);
				$header = base64_encode($header);
				$header = str_replace(['+', '/', '='], ['-', '_', ''], $header);

				$payload = [
							'sub' => $linha["idPessoa"],
							'jti' => 'lccr'.time().'dpl',
							'iat' => time(),
							'exp' => (new DateTime) -> add( new DateInterval('P45D') ) -> getTimestamp(),
							'aud' => 'Luiz Carlos Costa Rodrigues - DATAPROL',
							'iss' => 'monibus.tecnologia.ws',
							'name' => $linha["nomePessoa"],
							'email' => $linha["emailPessoa"]
							];
				$payload = json_encode($payload);
				$payload = base64_encode($payload);
				$payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

				$signature = hash_hmac('sha256',"$header.$payload",$this -> SenhaToken,true);
				$signature = base64_encode($signature);
				$signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature);

				$token = $header . "." . $payload . "." . $signature;

				$linha['sessaoPessoa']=session_id();
				$this -> Model -> AtualizarUsuario($linha);

				$retorno["token"] = $token;
				$this -> RespostaBoaHTTP(200,$retorno);
				exit;

			}else{

				$this -> VerificaQueErrou();	

			}
		}

		$this -> VerificaQueErrou();

	}

	public function VerificaQueErrou(){
		
		$msgErro = "Usuário ou senha está errado!";
		$_SESSION['usuarioSituacao'] = "erro";
		sleep($_SESSION['usuarioErroEsperar']);
		$_SESSION['usuarioErroEsperar'] += $_SESSION['usuarioErroEsperar'];;
		if( $_SESSION['usuarioErroEsperar'] >= 5 ){
			$msgErro = $msgErro + "Ocorreram muitas tentativa erradas.";
		}
		$this -> RespostaRuimHTTP(400,$msgErro,"Requisição Mal Feita",0);
		exit;
		
	}

	public function AlteraSenha( $id ){
                
        if( ! isset( $_SESSION[ "usuarioLogin" ] ) ){
			header("Location: usuarios/vlogin");exit;
		}else{
			header("Location: usuarios/vtoken");exit;
		}

		$lRetorno = true;
        $oPessoa = json_decode( file_get_contents("php://input") );
        isset($oPessoa -> idPessoa) ? $arrayPessoas["idPessoa"] = $oPessoa -> idPessoa : $lRetorno = false;
        isset($oPessoa -> nomePessoa) ? $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa : $lRetorno = false;
        isset($oPessoa -> identidadePessoa) ? $arrayPessoas["identidadePessoa"] = $oPessoa -> identidadePessoa : $lRetorno = false;
        isset($oPessoa -> emailPessoa) ? $arrayPessoas["emailPessoa"] = $oPessoa -> emailPessoa : $lRetorno = false;
        isset($oPessoa -> tipoPessoa) ? $arrayPessoas["tipoPessoa"] = $oPessoa -> tipoPessoa : $lRetorno = false;
        isset($oPessoa -> senhaPessoa) ? $arrayPessoas["senhaPessoa"] = trim( $oPessoa -> senhaPessoa ) : $lRetorno = false;
        isset($oPessoa -> usuarioPessoa) ? $arrayPessoas["usuarioPessoa"] = trim( strtolower( $oPessoa -> usuarioPessoa ) ) : $lRetorno = false;
        isset($oPessoa -> senhaNovaPessoa) ? $arrayPessoas["senhaNovaPessoa"] = $oPessoa -> senhaNovaPessoa : $lRetorno = false;
		
		if( $lRetorno ){

			$this -> Model -> consultaUsuario( $arrayPessoas["usuarioPessoa"] );
			$result = $this -> Model -> getConsult();

			if( $linha = $result -> fetch_assoc() ){

				if( $linha[ 'senhaPessoa' ] == md5( $arrayPessoas["senhaPessoa"] ) ){
							
					if( $arrayPessoas["senhaPessoa"] != $arrayPessoas["senhaNovaPessoa"] ){

						if( ! isset( $_SESSION[ "usuarioErroEsperar" ] ) ){
							$_SESSION['usuarioErroEsperar'] = .1;
						}

						$this -> Model -> AtualizarSenhaUsuario($arrayPessoas);
						if( $this -> Model -> getConsult() &&  $this -> Model -> Conn -> affected_rows > 0 ){

							$this -> RespostaBoaHTTP(200,"Senha alterada com sucesso!");
						
						}else{

							$this -> RespostaRuimHTTP(500,"A gravação dos dados falhou!","Erro interno",0);
							exit;            

						}

					}else{
						
						$this -> RespostaRuimHTTP(400,"A nova senha precisa ser diferente da antiga!","Requisição Mal Feita",0);
						exit;

					}

				}

			}

		}

    }

}

?>