<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

//namespace Controlllers;
final class PessoasController extends BaseController {
    
    var $Mail;
    var $Model;
    var $Usuario;

    function __construct(){
		
		require_once("models/PessoasModel.php");
        $this -> Model = new PessoasModel();

		require_once("controllers/UsuariosController.php");
        $this -> Usuario = new UsuariosController();
        $this -> Usuario -> ValidateTokenAction();

        require_once('Utils/PHPMailer/src/Exception.php');
        require_once('Utils/PHPMailer/src/SMTP.php');
        require_once('Utils/PHPMailer/src/PHPMailer.php');
        $this -> Mail = new PHPMailer;

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $aPessoas = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $aPessoas, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$aPessoas);
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado em nosso servidor!","Erro Interno",0);
        }

    }

	public function ConsultPessoa( $id ){
		
        if( isset($id) && strval($id) > 0 ){
            $this -> Model -> ConsultPessoa( $id );
            $result = $this -> Model -> GetConsult();
            $pessoa = $result -> fetch_assoc();
            
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $pessoa;
            header( 'Content-Type: application/json' );
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }
		
	}

    public function InsertPessoa(){
		
        $lRetorno = true;
        $ObjPessoa = json_decode( file_get_contents("php://input") );
        isset($ObjPessoa -> nomePessoa) ? $aPessoa["nomePessoa"] = $ObjPessoa -> nomePessoa : $lRetorno = false;
        isset($ObjPessoa -> identidadePessoa) ? $aPessoa["identidadePessoa"] = $ObjPessoa -> identidadePessoa : $lRetorno = false;
        isset($ObjPessoa -> emailPessoa) ? $aPessoa["emailPessoa"] = $ObjPessoa -> emailPessoa : $lRetorno = false;
        
        // Campos opcionais        
        if( !isset($ObjPessoa -> tipoPessoa) ){
            $ObjPessoa -> tipoPessoa = "P";
        }
        
        $aPessoa["usuarioPessoa"] = isset($ObjPessoa -> usuarioPessoa) ? $ObjPessoa -> usuarioPessoa : null;
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
        $aPessoa["presencaPessoa"] = isset($ObjPessoa -> presencaPessoa) ? $ObjPessoa -> presencaPessoa : null;

        if($lRetorno){
            // Gera nova senha provisória e cadastra a pessoa
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

            $this -> Model -> InsertPessoa($aPessoa);
            $idPessoa = $this -> Model -> GetConsult();
            
            if( $idPessoa > 0 ){
                
                // Opcionalmente, cadastra empresa e a vincula à pessoa
                $ObjEmpresa = $ObjPessoa -> empresa;
                if( isset($ObjEmpresa) ){
                    
                    if( $ObjEmpresa -> idEmpresa > 0 ){
                        $idEmpresa = $ObjEmpresa -> idEmpresa;
                        $this -> Model -> VincularEmpresa($idEmpresa, $idPessoa, $aPessoa["tipoPessoa"]);
                        $idPessoa = $this -> Model -> GetConsult();
                    }else{
                        require_once("controllers/EmpresasController.php");
                        $EmpresaController = new EmpresasController();
                        $ObjEmpresa -> idPessoa = $idPessoa;
                        $ObjEmpresa -> tipoPessoa = $aPessoa["tipoPessoa"];
                        $idEmpresa = $EmpresaController -> InsertEmpresa($ObjEmpresa);
                    }
                    ob_clean();
                    
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

            $this -> RespostaRuimHTTP(400,"Sintaxe da pessoa incorreta!","Requisição Mal Feita",0);
            exit;
            
        }

    }


    //Exemplo retirado do website DevMedia
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

    public function UpdatePessoa( $idPessoa ){

        $lRetorno = true;
		$ObjPessoa = json_decode(file_get_contents("php://input"));
        
		if( empty( $idPessoa ) ){
			$idPessoa = $ObjPessoa -> idPessoa;
		}

        if( !empty($idPessoa) && !empty($ObjPessoa) ){
            
            isset($ObjPessoa -> nomePessoa) ? $aPessoa["nomePessoa"] = $ObjPessoa -> nomePessoa : $lRetorno = false;
            isset($ObjPessoa -> identidadePessoa) ? $aPessoa["identidadePessoa"] = $ObjPessoa -> identidadePessoa : $lRetorno = false;
            isset($ObjPessoa -> emailPessoa) ? $aPessoa["emailPessoa"] = $ObjPessoa -> emailPessoa : $lRetorno = false;
            
            // Campos opcionais        
            if( !isset($ObjPessoa -> tipoPessoa) ){
                $ObjPessoa -> tipoPessoa = "P";
            }
            if( !isset($ObjPessoa -> presencaPessoa) ){
                $ObjPessoa -> presencaPessoa = '0';
            }

            $aPessoa["idPessoa"] = $idPessoa;
            $aPessoa["tipoPessoa"] = $ObjPessoa -> tipoPessoa;
            $aPessoa["telefone1Pessoa"] = $ObjPessoa -> telefone1Pessoa;
            $aPessoa["dataNascimentoPessoa"] = $ObjPessoa -> dataNascimentoPessoa;
            $aPessoa["enderecoLogradouroPessoa"] = $ObjPessoa -> enderecoLogradouroPessoa;
            $aPessoa["enderecoNumeroPessoa"] = $ObjPessoa -> enderecoNumeroPessoa;
            $aPessoa["enderecoBairroPessoa"] = $ObjPessoa -> enderecoBairroPessoa;
            $aPessoa["enderecoMunicipioPessoa"] = $ObjPessoa -> enderecoMunicipioPessoa;
            $aPessoa["enderecoUFPessoa"] = $ObjPessoa -> enderecoUFPessoa;
            $aPessoa["enderecoCEPPessoa"] = $ObjPessoa -> enderecoCEPPessoa;
            $aPessoa["enderecoIBGEPessoa"] = $ObjPessoa -> enderecoIBGEPessoa;
            $aPessoa["enderecoSIAFIPessoa"] = $ObjPessoa -> enderecoSIAFIPessoa;
            $aPessoa["enderecoGIAPessoa"] = $ObjPessoa -> enderecoGIAPessoa;
            $aPessoa["presencaPessoa"] = $ObjPessoa -> presencaPessoa;

            if( $lRetorno ){

                $this -> Model -> UpdatePessoa($aPessoa);
                $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
                header('Content-Type: application/json');
                echo json_encode($retorno);
                http_response_code(200);

            }
            
        }else{

            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);

        }
        
    }

    public function DeletePessoa( $idPessoa ){

		if( empty( $idPessoa ) ){
			$idPessoa = json_decode(file_get_contents("php://input")) -> idPessoa;
		}
        if( !empty($idPessoa)){
            $this -> Model -> DeletePessoa( $idPessoa );
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(200);
        }else{
            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
        }

    }

}
