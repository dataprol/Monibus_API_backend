<?php

	class UsersController{

		public function index(){
			if( ! isset($_SESSION["login"]) ){
				header("Location: index.php/users/vlogin");
			}else{
				header("Location: index.php/users/vtoken");
			}
		}
		
		public function validateToken(){
			//$header = apache_request_headers();
			$header = $_SERVER;
			
			if( array_key_exists( 'HTTP_AUTHORIZATION', $header ) != false ){
				$token = $header['HTTP_AUTHORIZATION'];
				$token = str_replace("Bearer ", "", $token); //se tiver o prefixo "Bearer" remover
				$part = explode(".",$token);
				$header = $part[0];
				$payload = $part[1];
				$signature = $part[2];
				$valid = hash_hmac('sha256',"$header.$payload",'SenhaSecreta*20190627',true);
				$valid = base64_encode($valid);
				$valid = str_replace(['+', '/', '='], ['-', '_', ''], $valid); //base64url
				if($signature != $valid){
					echo('{ "Result": "Token inválido!" }');
					exit;
				}else{
					echo ('{"acess":"true","token":"Válido"}');
				}
			}else{
				echo('{ "Result": "Requer autorização!" }');
				exit;
			}
		}

		public function validateLogin(){

			$oUser = json_decode( file_get_contents("php://input") );
			if( empty( $oUser ) ){ 
				header('Content-Type: application/json');
				echo('{ "Result": "Requer login!" }');
				exit;
			}
			$login = $oUser -> login;
			$password = $oUser -> password;
			require("models/UsersModel.php");

			$User = new UsersModel();
			$User -> consultUser( $login );
			$result = $User -> getConsult();

			if( $line = $result -> fetch_assoc() ){
				if( $line[ 'password' ] == $password ){
					$_SESSION['idUser'] = $line['idUser'];
					$_SESSION['name'] = $line['name'];
					$_SESSION['login'] = $line['login'];

					// Gera chave token 
					$header = [
								'alg' => 'HS256',
								'typ' => 'JWT'
								];
					$header = json_encode($header);
					$header = base64_encode($header);
					$header = str_replace(['+', '/', '='], ['-', '_', ''], $header); //base64url

					$payload = [
								'iss' => 'localhost',
								'name' => 'lccr',
								'email' => 'lccr@rede.ulbra.br'
								];
					$payload = json_encode($payload);
					$payload = base64_encode($payload);
					$payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload); //base64url

					$signature = hash_hmac('sha256',"$header.$payload",'SenhaSecreta*20190627',true);
					$signature = base64_encode($signature);
					$signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature); //base64url

					$token = $header . "." . $payload . "." . $signature;

					header('Content-Type: application/json');	
					echo ('{"acess":"true","token":"'.$token.'"}');
					exit;

				}else{
					header('Content-Type: application/json');
					echo('{ "Result": "Senha incorreta!" }');
					exit;
				}
			}else{
				header('Content-Type: application/json');
				echo('{ "Result": "Usuário inválido!" }');
				exit;
			}
		}
	}

?>