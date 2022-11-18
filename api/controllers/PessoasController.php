<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

//namespace Controlllers;
final class PessoasController extends BaseController {
    
    var $Mail;
    var $Model;

    function __construct(){
		
		require_once("models/PessoasModel.php");
        $this -> Model = new PessoasModel();

        if( ! isset( $_SESSION[ "usuarioLogin" ] ) ){
			header("Location: usuarios/vlogin");exit;
		}else{
			header("Location: usuarios/vtoken");exit;
		}

        require_once('Utils/PHPMailer/src/Exception.php');
        require_once('Utils/PHPMailer/src/SMTP.php');
        require_once('Utils/PHPMailer/src/PHPMailer.php');
        $this -> Mail = new PHPMailer;

    }

    public function ListThis(){

        $result = $this -> ListPagination();
        $arrayPessoas = array();
        if( $result != false ){
            if( $result -> num_rows > 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayPessoas, $line );
                }
            }
            $this -> RespostaBoaHTTP(200,$arrayPessoas);
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
        $oPessoa = json_decode( file_get_contents("php://input") );
        isset($oPessoa -> nomePessoa) ? $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa : $lRetorno = false;
        isset($oPessoa -> identidadePessoa) ? $arrayPessoas["identidadePessoa"] = $oPessoa -> identidadePessoa : $lRetorno = false;
        isset($oPessoa -> emailPessoa) ? $arrayPessoas["emailPessoa"] = $oPessoa -> emailPessoa : $lRetorno = false;
        isset($oPessoa -> tipoPessoa) ? $arrayPessoas["tipoPessoa"] = $oPessoa -> tipoPessoa : $lRetorno = false;
        isset($oPessoa -> senhaPessoa) ? $arrayPessoas["senhaPessoa"] = $oPessoa -> senhaPessoa : $lRetorno = false;
        isset($oPessoa -> usuarioPessoa) ? $arrayPessoas["usuarioPessoa"] = $oPessoa -> usuarioPessoa : $lRetorno = false;

        if($lRetorno){

            $this -> Model -> ConsultUsuarioEmail( $arrayPessoas["emailPessoa"] );
            $arrayPessoas = $this -> Model -> getConsult() -> fetch_assoc();
            if( !empty( $arrayPessoas ) ){
                $this -> RespostaRuimHTTP(400,"já existe usuário com este e-mail informado!","Requisição Mal Feita",0);
                exit;
            }

            $this -> Model -> InsertPessoa($arrayPessoas);
            $idPessoa = $this -> Model -> GetConsult();

                
            // Gera nova senha provisória e cadastra o usuário
            $novaSenha = $this -> gerar_senha( 6, true, true, true, true );
            $arrayPessoas["nomePessoa"]      = mb_convert_case( $arrayPessoas["nomePessoa"],  MB_CASE_TITLE, 'UTF-8' );
            $arrayPessoas["usuarioEmail"]     = $arrayPessoas["usuarioEmail"];
            $arrayPessoas["usuarioCliente"]        = $arrayPessoas["usuarioCliente"];
            $arrayPessoas["usuarioTelefoneCelular"]     = $arrayPessoas["usuarioTelefoneCelular"];
            $arrayPessoas["usuarioNivel"]     = $arrayPessoas["usuarioNivel"];
            $arrayPessoas["usuarioPessoa"]     = $arrayPessoas["usuarioPessoa"];
            $arrayPessoas["usuarioSenha"]     = md5( $novaSenha );
            $arrayPessoas["usuarioSenhaValidade"] = "null";

            // Envia mensagem por e-Mail
            $cMailCharSet = 'UTF-8';
            $cMailHeaders = '';
            $cMailOrigem = 'sac@monibus.tecnologia.ws';
            $cMailNomeOrigem = 'SAC Monibus';
            $cMailResposta = 'sac@monibus.tecnologia.ws';
            $cMailNomeResposta = 'SAC Monibus';
            $cMailDestino = $arrayPessoas["usuarioEmail"];
            $cMailNomeDestino = $arrayPessoas["nomePessoa"];
            $cMailAssunto = 'Seu usuário ' . $arrayPessoas["usuarioPessoa"] . ' foi criado com sucesso' ;
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
                    <p>Nome: ' . $arrayPessoas["nomePessoa"] . '<br>
                    Usuário: <b>' . $arrayPessoas["usuarioPessoa"] . '</b><br>
                    Senha provisória: <b>' . $novaSenha . '</b><br>
                    <br>
                    <b>Assim que acessar o sistema, solicitaremos que altere a senha.</b>
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
            $this -> Mail -> FromName = $cMailNomeResposta; 
            $this -> Mail -> addAddress($cMailDestino, $cMailNomeDestino); 
            $this -> Mail -> addAddress('sac@monibus.tecnologia.ws'); 
            $this -> Mail -> addReplyTo($cMailOrigem, $cMailNomeResposta);
            //$this -> Mail -> addCC('cc@exemplo.com');
            //$this -> Mail -> addBCC('bcc@exemplo.com');
            $this -> Mail -> isHTML(true); 
            $this -> Mail -> Subject = $cMailAssunto;
            $this -> Mail -> Body    = $cMailmensagem;
            $this -> Mail -> AltBody = strip_tags( $cMailmensagem );
            
            if( ! $this -> Mail -> send() ) 
            {
                // A mensagem não pode ser enviada   
            }
            

            header('Content-Type: application/json');
            $data['idPessoa'] = $idPessoa;
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            $retorno['data'] = $data;
            echo json_encode($retorno);
            http_response_code(201);

        }else{

            $this -> RespostaRuimHTTP(400,"Requisição mal feita! Revisar a sintaxe!","Requisição Mal Feita",0);
            exit;
            
        }

    }


    //Exemplo retirado do website DevMedia
    function gerar_senha( $tamanho, $maiusculas, $minusculas, $numeros, $simbolos ){

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

		$oPessoa = json_decode(file_get_contents("php://input"));
        
		if( empty( $idPessoa ) ){
			$idPessoa = $oPessoa -> idPessoa;
		}
        
        if( !empty($idPessoa)){
            $arrayPessoas["idPessoa"] = $idPessoa;
            $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa;
            $arrayPessoas["emailPessoa"] = $oPessoa -> emailPessoa;
            $arrayPessoas["tipoPessoa"] = $oPessoa -> tipoPessoa;
            $this -> Model -> UpdatePessoa($arrayPessoas);
            $retorno['success'] = $this -> Model -> Conn -> affected_rows > 0 ? "true": "false";
            header('Content-Type: application/json');
            echo json_encode($retorno);
            http_response_code(200);
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

?>