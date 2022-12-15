<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

class UsuariosController extends BaseController{
	
	var $Model;
	var $token;
	var $tokenPayload;
	var $dados;
	var $Mail;

	function __construct(){

        require_once("models/UsuariosModel.php");
        $this -> Model = new UsuariosModel();

        require_once('Utils/PHPMailer/src/Exception.php');
        require_once('Utils/PHPMailer/src/SMTP.php');
        require_once('Utils/PHPMailer/src/PHPMailer.php');
        $this -> Mail = new PHPMailer;

	}

	public function Index(){
		
        $this -> ValidateTokenAction();

	}
	
	public function ValidateToken(){
		
		if( $this -> ValidateTokenAction() ){
			$this -> RespostaBoaHTTP(200,"Token validado! ");
			//$this -> RespostaBoaHTTP( 200, ($this -> tokenPayload) );
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
			$valid1 = hash_hmac('sha256',"$header.$payload",_SenhaToken,true);
			$valid2 = base64_encode($valid1);
			$valid3 = str_replace(['+', '/', '='], ['-', '_', ''], $valid2);
			if($signature != $valid3){
				
				$this -> RespostaRuimHTTP(401,"Token inválido!","Não autorizado",0);
				exit;
				
			}else{
				
				$this -> token = $token;
				$this -> tokenPayload = json_decode(base64_decode($payload));
				$this -> Model -> consultaUsuarioId( $this -> getId() );
				$result = $this -> Model -> getConsult();

				if( $result == false ){
					$_SESSION['usuarioSituacao'] = "erro";
					sleep($_SESSION['usuarioErroEsperar']);

					$this -> RespostaRuimHTTP(401,"Token invalidado! Requer login!","Não autorizado",0);

					exit;
				}
				
				$this -> dados = $result -> fetch_assoc();

				if( $this -> getTokenValidade() < (new DateTime) -> getTimestamp() ){
					
					$this -> RespostaRuimHTTP(401,"Token vencido! Requer login!","Não autorizado",0);

				}

			}
			$_SESSION['usuarioId'] = $this -> getId();
			$_SESSION['usuarioLogin'] = $this -> getUsuario();
			$_SESSION['usuarioTipo'] = $this -> getTipo();
			$_SESSION['usuarioIdentidade'] = $this -> getIdentidade();
			$_SESSION['usuarioNome'] = $this -> getNome();
			$_SESSION['usuarioEmail'] = $this -> getEmail();
			$_SESSION['usuarioTelefone'] = $this -> getTelefone();
			$_SESSION['usuarioToken'] = $this -> getToken();
			$_SESSION['usuarioTokenValidade'] = $this -> getTokenValidade();

			$lRetorno = true;
		}else{
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			$this -> RespostaRuimHTTP(401,"Requer autorização HTTP!","Não autorizado",0);
			exit;
		}
		return $lRetorno;
		
	}

	public function ValidateLogin(){

		$ModelInfo = json_decode( file_get_contents("php://input") );
		
		if( empty( $ModelInfo ) || empty($ModelInfo -> username) || empty($ModelInfo -> password) ){ 
			$_SESSION['usuarioSituacao'] = "erro";
			sleep($_SESSION['usuarioErroEsperar']);
			
			$this -> RespostaRuimHTTP(400,"Dado em branco!","Requisição Mal Feita",0);
			exit;
		}

		$username = trim( strtolower( $ModelInfo -> username ) );
		$password = trim( $ModelInfo -> password );

		$this -> Model -> consultaUsuarioEmpresas( $username );
		$result = $this -> Model -> getConsult();
        if( $result != false ){

			$aLinhas = array();
            if( $result -> num_rows > 1 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $aLinhas, $line );  // usa as várias linhas retornadas
                }
				$cUsuario = $aLinhas[0];  // usa só a primeira linha retornada
            }else{
				$cUsuario = $result -> fetch_assoc();
			}

			if( $cUsuario[ 'senhaPessoa' ] == md5($password) ){

				$_SESSION["idPessoa"] 			= $cUsuario["idPessoa"];
				$_SESSION['nomePessoa'] 		= $cUsuario['nomePessoa'];
				$_SESSION['usuarioLogin'] 		= $cUsuario['usuarioPessoa'];
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
							'sub' => $cUsuario["idPessoa"],
							'jti' => 'lccr'.time().'dpl',
							'iat' => time(),
							'exp' => (new DateTime) -> add( new DateInterval('P90D') ) -> getTimestamp(),
							'aud' => 'Luiz Carlos Costa Rodrigues - DATAPROL',
							'iss' => 'monibus.tecnologia.ws',
							'name' => $cUsuario["nomePessoa"],
							'user' => $cUsuario["usuarioPessoa"],
							'email' => $cUsuario["emailPessoa"]
							];
				$payload = json_encode($payload);
				$payload = base64_encode($payload);
				$payload = str_replace(['+', '/', '='], ['-', '_', ''], $payload);

				$signature = hash_hmac('sha256',"$header.$payload",_SenhaToken,true);
				$signature = base64_encode($signature);
				$signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature);

				$token = $header . "." . $payload . "." . $signature;

				$empresas = array();
				if( $result -> num_rows > 1 ){
					foreach ($aLinhas as $value) {
						# code...
						$emp["id"] = $value["idEmpresa"];
						$emp["nome"] = $value["nomeEmpresa"];
						$emp["identidade"] = $value["identidadeEmpresa"];
						$emp["tipo"] = $cUsuario["tipoPessoa"];
						array_push($empresas,$emp);
					}
				}else{
					$emp["id"] = $cUsuario["idEmpresa"];
					$emp["nome"] = $cUsuario["nomeEmpresa"];
					$emp["identidade"] = $cUsuario["identidadeEmpresa"];
					$emp["tipo"] = $cUsuario["tipoPessoa"];
					array_push($empresas,$emp);
				}
				$retorno["id"] = $cUsuario["idPessoa"];
				$retorno["nome"] = $cUsuario["nomePessoa"];
				$retorno["identidade"] = $cUsuario["identidadePessoa"];
				$retorno["email"] = $cUsuario["emailPessoa"];
				$retorno["telefone"] = $cUsuario["telefone1Pessoa"];
				$retorno["empresa"] = $empresas;
				
				$retorno["token"] = $token;

				$this -> RespostaBoaHTTP(200,$retorno);
				exit;

			}else{

				$this -> RespostaRuimHTTP(400,"Usuário ou senha está errado!","Requisição Mal Feita",0);

			}
			
		}

		$this -> RespostaRuimHTTP(500,"Algo deu errado na busca no banco de dados!","Erro Interno do Servidor",0);;

	}

	public function InformaQueDeuErro(){
		
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

			if( $cUsuario = $result -> fetch_assoc() ){

				if( $cUsuario[ 'senhaPessoa' ] == md5( $arrayPessoas["senhaPessoa"] ) ){
							
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

	public function getId(){
		return $this -> tokenPayload -> sub;
	}

	public function getNome(){
		return $this -> tokenPayload -> name;
	}

	public function getIdentidade(){
		return $this -> dados['identidadePessoa'];
	}

	public function getEmail(){
		return $this -> tokenPayload -> email;
	}

	public function getTelefone(){
		return $this -> dados['telefone1Pessoa'];
	}

	public function getTipo(){
		return $this -> dados['tipoPessoa'];
	}
	
	public function getUsuario(){
		return $this -> tokenPayload -> user;
	}
	
	public function getToken(){
		return $this -> token;
	}

	public function getTokenValidade(){
		return $this -> tokenPayload -> exp;
	}

	public function InsertUsuario(){
		
        $lRetorno = true;
        $ObjPessoa = json_decode( file_get_contents("php://input") );
        isset($ObjPessoa -> nomePessoa) ? $aPessoa["nomePessoa"] = $ObjPessoa -> nomePessoa : $lRetorno = false;
        isset($ObjPessoa -> identidadePessoa) ? $aPessoa["identidadePessoa"] = $ObjPessoa -> identidadePessoa : $lRetorno = false;
        isset($ObjPessoa -> emailPessoa) ? $aPessoa["emailPessoa"] = $ObjPessoa -> emailPessoa : $lRetorno = false;
        isset($ObjPessoa -> usuarioPessoa) ? $aPessoa["usuarioPessoa"] = $ObjPessoa -> usuarioPessoa : $lRetorno = false;
        
        // Campos opcionais        
        if( !isset($ObjPessoa -> tipoPessoa) ){
            $ObjPessoa -> tipoPessoa = "";
        }
        $aPessoa["senhaPessoa"] = isset($ObjPessoa -> senhaPessoa) ? $ObjPessoa -> senhaPessoa : null;
        $aPessoa["tipoPessoa"] = $ObjPessoa -> tipoPessoa;
        $aPessoa["telefone1Pessoa"] = isset($ObjPessoa -> telefone1Pessoa) ? $ObjPessoa -> telefone1Pessoa : null;
        $aPessoa["dataNascimentoPessoa"] = isset($ObjPessoa -> dataNascimentoPessoa) ? $ObjPessoa -> dataNascimentoPessoa : null;
        $aPessoa["enderecoLogradouroPessoa"] = isset($ObjPessoa -> enderecoLogradouroPessoa) ? $ObjPessoa -> enderecoLogradouroPessoa : null;
        $aPessoa["enderecoNumeroPessoa"] = isset($ObjPessoa -> enderecoNumeroPessoa) ? $ObjPessoa -> enderecoNumeroPessoa : null;
        $aPessoa["enderecoBairroPessoa"] = isset($ObjPessoa -> enderecoBairroPessoa) ? $ObjPessoa -> enderecoBairroPessoa : null;
        $aPessoa["enderecoMunicipioPessoa"] = isset($ObjPessoa -> enderecoMunicipioPessoa) ? $ObjPessoa -> enderecoMunicipioPessoa : null;
        $aPessoa["enderecoUFPessoa"] = isset($ObjPessoa -> enderecoUFPessoa) ? $ObjPessoa -> enderecoUFPessoa : null;
        $aPessoa["enderecoCEPPessoa"] = isset($ObjPessoa -> enderecoCEPPessoa) ? $ObjPessoa -> enderecoCEPPessoa : null;
        $aPessoa["enderecoIBGEPessoa"] = isset($ObjPessoa -> enderecoIBGEPessoa) ? $ObjPessoa -> enderecoIBGEPessoa : null;
        $aPessoa["enderecoSIAFIPessoa"] = isset($ObjPessoa -> enderecoSIAFIPessoa) ? $ObjPessoa -> enderecoSIAFIPessoa : null;
        $aPessoa["enderecoGIAPessoa"] = isset($ObjPessoa -> enderecoGIAPessoa) ? $ObjPessoa -> enderecoGIAPessoa : null;

        if($lRetorno){
            // Gera nova senha provisória e cadastra o usuário
            $cTxtSenhaProvisoria = '';
            if($aPessoa["senhaPessoa"] == null){
                $aPessoa["senhaPessoa"] = $this -> GerarSenha( 6, true, true, true, true );
                $cTxtSenhaProvisoria = 'Senha provisória: <b>' . $aPessoa["senhaPessoa"] . '</b><br><br>
				<b>Assim que acessar o sistema, solicitaremos que altere a senha.</b>';
			}
			// Criptografa a senha
			$aPessoa["senhaPessoa"] = md5( $aPessoa["senhaPessoa"] );

            // Ajusta campo nome
            $aPessoa["nomePessoa"] = mb_convert_case( $aPessoa["nomePessoa"],  MB_CASE_TITLE, 'UTF-8' );

            $this -> Model -> InsertUsuario($aPessoa);
            $idPessoa = $this -> Model -> GetConsult();
            
            if( $idPessoa > 0 ){

                // Opcionalmente, cadastra empresa e a vincula à pessoa
                $ObjEmpresa = $ObjPessoa -> empresa;
                if( isset($ObjEmpresa) ){
					
                    $ObjEmpresa -> idPessoa = $idPessoa;
                    $ObjEmpresa -> tipoPessoa = $aPessoa["tipoPessoa"];                    
					$aEmpresa["idPessoa"] = isset($ObjEmpresa -> idPessoa) ? $ObjEmpresa -> idPessoa : $lRetorno = false;
					$aEmpresa["tipoPessoa"] = $ObjEmpresa -> tipoPessoa;
					$aEmpresa["nomeEmpresa"] = isset($ObjEmpresa -> nomeEmpresa) ? $ObjEmpresa -> nomeEmpresa : $lRetorno = false;
					$aEmpresa["identidadeEmpresa"] = isset($ObjEmpresa -> identidadeEmpresa) ? $ObjEmpresa -> identidadeEmpresa : $lRetorno = false;			

					if($lRetorno){
						$this -> Model -> InsertEmpresaUsuario($aEmpresa);
					}else{

						$this -> RespostaRuimHTTP(400,"Sintaxe incorreta da empresa!","Requisição Mal Feita",0);
						exit;			
						ob_clean();

					}
				}

                // Envia mensagem por e-Mail confirmando o cadastro
                $cMailCharSet = 'UTF-8';
                $cMailHeaders = '';
                $cMailOrigem = 'sac@monibus.tecnologia.ws';
                $cMailNomeOrigem = 'SAC Monibus';
                $cMailResposta = 'sac@monibus.tecnologia.ws';
                $cMailNomeResposta = 'SAC Monibus';
                $cMailDestino = $aPessoa["emailPessoa"];
                $cMailNomeDestino = $aPessoa["nomePessoa"];
                $cMailAssunto = 'Seu usuário ' . $aPessoa["usuarioPessoa"] . ' foi criado com sucesso' ;
                $cMailmensagem = '
                <html lang="pt">
                    <meta charset="' . mb_strtolower($cMailCharSet) . '">
                    <meta name="author" content="Luiz Carlos Costa Rodrigues born in Santa Maria RS Brazil, www.dataprol.com.br">
                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, shrink-to-fit=no">
                    <head>
                        <title>Novo Usuário Monibus</title>
                    </head>
                    <body>
                        <h1>Novo Usuário Monibus</h1>
                        <h3>Seu cadastro foi concluído, com sucesso!</h3>
                        <p>Nome: ' . $aPessoa["nomePessoa"] . '<br>
                        Usuário: <b>' . $aPessoa["usuarioPessoa"] . '</b><br>
                        '.$cTxtSenhaProvisoria.'
                        </p>
                        <br><br>
                        Equipe Monibus
                        <br>
                        <a href="http://www.monibus.tecnologia.ws/">www.monibus.tecnologia.ws</a>
                    </body>
                </html>
                ';
                
                $this -> Mail -> setLanguage('br'); 
                $this -> Mail -> CharSet='UTF-8'; 
                //$this -> Mail -> SMTPDebug = SMTP::DEBUG_SERVER; 
                $this -> Mail -> isSMTP(); 
                $this -> Mail -> Host = _SisConfigGeral["email_servidor"]["hostname"]; 
                $this -> Mail -> SMTPAuth = true; 
                $this -> Mail -> Username = _SisConfigGeral["email_servidor"]["username"]; 
                $this -> Mail -> Password = _SisConfigGeral["email_servidor"]["password"]; 
                $this -> Mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                $this -> Mail -> Port = 587; 
                $this -> Mail -> From = $cMailOrigem; 
                $this -> Mail -> FromName = $cMailNomeOrigem; 
                $this -> Mail -> addAddress($cMailDestino, $cMailNomeDestino); 
                $this -> Mail -> addAddress($cMailResposta); 
                $this -> Mail -> addReplyTo($cMailOrigem, $cMailNomeResposta);
                $this -> Mail -> isHTML(true); 
                $this -> Mail -> Subject = $cMailAssunto;
                $this -> Mail -> Body    = $cMailmensagem;
                $this -> Mail -> AltBody = strip_tags( $cMailmensagem );
                
                if( ! $this -> Mail -> send() )
                {
                    // A mensagem não pode ser enviada
                }
				session_destroy();
                
                // Responde com resposta de sucesso
                header('Content-Type: application/json');
                $data['idPessoa'] = $idPessoa;
                $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
                $retorno['data'] = $data;
                echo json_encode($retorno);
                http_response_code(201);
                
            }else{

                $cMensagemErro = "Sintaxe incorreta!";

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
                        if($indiceValorDuplicado == 'emailPessoa_UNIQUE'){
                            $cMensagemErro = "E-mail $valorDuplicado";
                        }
                        if($indiceValorDuplicado == 'identidadePessoa_UNIQUE'){
                            $cMensagemErro = "Identidade $valorDuplicado";
                        }
                        if($indiceValorDuplicado == 'usuarioPessoa_UNIQUE'){
                            $cMensagemErro = "Nome de usuário $valorDuplicado";
                        }
                        $cMensagemErro .= " já existente no cadastro de outra pessoa!";
                    }
                }
                
                $this -> RespostaRuimHTTP(400,$cMensagemErro,"Requisição Mal Feita",0);
                exit;
                
            }

        }else{

            $this -> RespostaRuimHTTP(400,"Sintaxe de usuário incorreta!","Requisição Mal Feita",0);
            exit;
            
        }

	}

	// Exemplo retirado do website DevMedia
	function GerarSenha( $tamanho, $maiusculas, $minusculas, $numeros, $simbolos ){

		$senha = '';
		$ma = "ABCDEFGHJKLMNPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
		$mi = mb_strtolower($ma); // $mi contem as letras minusculas
		$nu = "123456789"; // $nu contem os números
		$si = "#$%&*"; // $si contem os símbolos
		
		if ($maiusculas){
				// se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
				$senha .= str_shuffle($ma);
		}
		
		if ($minusculas){
			// se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
			$senha .= str_shuffle($mi);
		}
	
		if ($numeros){
			// se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
			$senha .= str_shuffle($nu);
		}
	
		if ($simbolos){
			// se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
			$senha .= str_shuffle($si);
		}
	
		// retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
		return mb_substr( str_shuffle( $senha ), 0, $tamanho );

	}

	function GetGUIDv4($data = null) {

		// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
		$data = $data ?? random_bytes(16);
		assert(strlen($data) == 16);
	
		// Set version to 0100
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		
		// Set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
	
		// Output the 36 character UUID.
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		
	}
	
	function SolicitarRecuperarAcesso(){
		
		$ModelInfo = json_decode( file_get_contents("php://input") );
		
		if( empty( $ModelInfo ) || empty($ModelInfo -> email) ){ 
			$this -> RespostaRuimHTTP(400,"Dado em branco!","Requisição Mal Feita",0);
			exit;
		}
		
		$email = trim( strtolower( $ModelInfo -> email ) );
		
		$this -> Model -> ConsultaUsuarioEmail( $email );
		$result = $this -> Model -> getConsult();

		if( $cUsuario = $result -> fetch_assoc() ){

			$idRecuperacao = $this -> GetGUIDv4();
			
			$this -> Model -> SalvarIdRecuperacaoAcessoUsuario( $cUsuario["idPessoa"], $idRecuperacao );
			if( $this -> Model -> getConsult() &&  $this -> Model -> Conn -> affected_rows > 0 ){

                // Envia mensagem por e-Mail confirmando o cadastro
                $cMailCharSet = 'UTF-8';
                $cMailHeaders = '';
                $cMailOrigem = 'sac@monibus.tecnologia.ws';
                $cMailNomeOrigem = 'SAC Monibus';
                $cMailResposta = 'sac@monibus.tecnologia.ws';
                $cMailNomeResposta = 'SAC Monibus';
                $cMailDestino = $cUsuario["emailPessoa"];
                $cMailNomeDestino = ''; //$cUsuario["nomePessoa"];
                $cMailAssunto = 'Recuperação de Acesso ao Monibus' ;
                $cMailmensagem = '
                <html lang="pt">
                    <meta charset="' . mb_strtolower($cMailCharSet) . '">
                    <meta name="author" content="Luiz Carlos Costa Rodrigues born in Santa Maria RS Brazil, www.dataprol.com.br">
                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, shrink-to-fit=no">
                    <head>
                        <title>Recuperação de Acesso Monibus</title>
                    </head>
                    <body>
                        <h1>Recuperação de Acesso Monibus</h1>
                        <h3>Para recuperar o acesso e alterar sua senha, use o link abaixo!</h3>
                        Link: <a href="http://monibus.tecnologia.ws/recuperaracesso.php?idrecuperacao=' . $idRecuperacao . '">Recuperar Acesso</a><br>
                        <br><br>
                        Equipe Monibus
                        <br>
                        <a href="http://www.monibus.tecnologia.ws/">www.monibus.tecnologia.ws</a>
                    </body>
                </html>
                ';
                
                $this -> Mail -> setLanguage('br'); 
                $this -> Mail -> CharSet='UTF-8'; 
                //$this -> Mail -> SMTPDebug = SMTP::DEBUG_SERVER; 
                $this -> Mail -> isSMTP(); 
                $this -> Mail -> Host = _SisConfigGeral["email_servidor"]["hostname"]; 
                $this -> Mail -> SMTPAuth = true; 
                $this -> Mail -> Username = _SisConfigGeral["email_servidor"]["username"]; 
                $this -> Mail -> Password = _SisConfigGeral["email_servidor"]["password"]; 
                $this -> Mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                $this -> Mail -> Port = 587; 
                $this -> Mail -> From = $cMailOrigem; 
                $this -> Mail -> FromName = $cMailNomeOrigem; 
                $this -> Mail -> addAddress($cMailDestino, $cMailNomeDestino); 
                $this -> Mail -> addAddress($cMailResposta); 
                $this -> Mail -> addReplyTo($cMailOrigem, $cMailNomeResposta);
                $this -> Mail -> isHTML(true); 
                $this -> Mail -> Subject = $cMailAssunto;
                $this -> Mail -> Body    = $cMailmensagem;
                $this -> Mail -> AltBody = strip_tags( $cMailmensagem );
                
                if( ! $this -> Mail -> send() )
                {
                    // A mensagem não pôde ser enviada.
					$this -> RespostaRuimHTTP(500,"Falha no envio da sua solicitação!","Erro interno",0);
					exit;

				}
				session_destroy();
				
				$this -> RespostaBoaHTTP(200,"Solicitação atendida com sucesso!");
			
			}else{

				$this -> RespostaRuimHTTP(500,"A análise do usuário falhou!","Erro interno",0);
				exit;

			}

		}else{

			$this -> RespostaRuimHTTP(404,"Email não cadastrado!","Requisição Mal Feita",0);
			exit;

		}

	}
	
}

?>