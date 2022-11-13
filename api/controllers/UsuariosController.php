<?php

class UsuariosController{
	
	var $oUsuarioModel;
	var $SenhaToken;

	function __construct(){

        require_once("models/UsuariosModel.php");
        $this -> oUsuarioModel = new UsuariosModel();
		$this -> SenhaToken = 'Monibus%$#@!LuizCarlosDataprol19772022';

	}

	public function Index(){
		
		if( ! isset($_SESSION["usuarioPessoa"]) ){
			header("Location: index.php/usuarios/vlogin");
		}else{
			header("Location: index.php/usuarios/vtoken");
		}	

	}
	
	public function ValidateToken(){
		
		$header = $_SERVER;
		if( array_key_exists( 'HTTP_AUTHORIZATION', $header ) != false ){
			$token = $header['HTTP_AUTHORIZATION'];
			if( $token == null || empty($token) ){
				header('Content-Type: application/json');
				echo '{ "Result": "Token não informado!" }';
				exit;
			}
			$token = str_replace("Bearer ", "", $token);
			$part = explode(".",$token);
			$header = $part[0];
			$payload = $part[1];
			$signature = $part[2];
			$valid = hash_hmac('sha256',"$header.$payload",$this -> SenhaToken,true);
			$valid = base64_encode($valid);
			$valid = str_replace(['+', '/', '='], ['-', '_', ''], $valid);
			if($signature != $valid){
				echo('{ "Result": "Token inválido!" }');
				exit;
			}else{
				
				$this -> oUsuarioModel -> consultaUsuariosSession( session_id() );
				$result = $this -> oUsuarioModel -> getConsult();
				if( $result == false ){
					header('Content-Type: application/json');
					$_SESSION['usuarioSituacao'] = "erro";
					sleep($_SESSION['usuarioErroEsperar']);
					echo('{ "Result": "Requer login! Sessão não autorizada!" }');
					session_destroy();
					exit;
				}
			}
		}else{
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			echo('{ "Result": "Requer autorização!" }');
			exit;
		}
		header('Content-Type: application/json');
		echo('{ "Result": "true" }');
	}

	public function ValidateLogin(){

		$oUsuarioModelInfo = json_decode( file_get_contents("php://input") );
		if($oUsuarioModelInfo == null){
			$oUsuarioModelInfo -> username = $_GET["username"];
			$oUsuarioModelInfo -> password  = $_GET["password"];
		}
		
		if( empty( $oUsuarioModelInfo ) || empty($oUsuarioModelInfo -> username) || empty($oUsuarioModelInfo -> password) ){ 
			header('Content-Type: application/json');
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			echo('{ "Result": "Requer login! Dados em branco!" }');
			exit;
		}

		$username = trim( strtolower( $oUsuarioModelInfo -> username ) );
		$password = trim( $oUsuarioModelInfo -> password );

		$this -> oUsuarioModel -> consultaUsuario( $username );
		$result = $this -> oUsuarioModel -> getConsult();

		if( $linha = $result -> fetch_assoc() ){
			if( $linha[ 'senhaPessoa' ] == $password ){

				$_SESSION["idPessoa"] 			= $linha["idPessoa"];
				$_SESSION['nomePessoa'] 		= $linha['nomePessoa'];
				$_SESSION['usuarioLogin'] 		= $linha['usuarioPessoa'];
				$_SESSION['usuarioSituacao'] 	= "loged";
				
				if( $linha["senhaValidadePessoa"] < date("Y-m-d") ){
					$this -> ExpirouSenhaUsuario( $linha );
					exit;
				}

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
				$this -> oUsuarioModel -> AtualizarUsuario($linha);

				header('Content-Type: application/json');	
				echo('{ "acess": "true", "token": "'.$token.'" }');
				exit;

			}else{

				$this -> VerificaQueErrou();	

			}
		}

		$this -> VerificaQueErrou();

	}

	public function VerificaQueErrou(){
		
		header('Content-Type: application/json');
		$_SESSION['usuarioSituacao'] = "erro";
		sleep($_SESSION['usuarioErroEsperar']);
		$_SESSION['usuarioErroEsperar'] += $_SESSION['usuarioErroEsperar'];;
		if( $_SESSION['usuarioErroEsperar'] >= 5 ){
			echo('{ "Result": "Usuário ou senha está errado! Ocorreram muitas tentativa erradas." }');
		}else{
			echo('{ "Result": "Usuário ou senha está errado!" }');
		}
		exit;
		
	}

	public function ExpirouSenhaUsuario( $aLinha ){
		
		header('Content-Type: application/json');
		echo '{ "Result": "Sua senha expirou!" }';
		exit;

	}

	public function ConsultaUsuario( $username ){

		$this -> oUsuarioModel -> consultaUsuario( $username );
		$result = $this -> oUsuarioModel -> getConsult();

	}

}

?>