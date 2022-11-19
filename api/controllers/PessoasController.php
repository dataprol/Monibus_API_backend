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
        isset($oPessoa -> nomePessoa) ? $arrayPessoa["nomePessoa"] = $oPessoa -> nomePessoa : $lRetorno = false;
        isset($oPessoa -> identidadePessoa) ? $arrayPessoa["identidadePessoa"] = $oPessoa -> identidadePessoa : $lRetorno = false;
        isset($oPessoa -> emailPessoa) ? $arrayPessoa["emailPessoa"] = $oPessoa -> emailPessoa : $lRetorno = false;
        isset($oPessoa -> tipoPessoa) ? $arrayPessoa["tipoPessoa"] = $oPessoa -> tipoPessoa : $lRetorno = false;
        isset($oPessoa -> usuarioPessoa) ? $arrayPessoa["usuarioPessoa"] = $oPessoa -> usuarioPessoa : $lRetorno = false;

        isset($oPessoa -> dataNascimentoPessoa) ? $arrayPessoa["dataNascimentoPessoa"] = $oPessoa -> dataNascimentoPessoa : null;
        isset($oPessoa -> telefone1Pessoa) ? $arrayPessoa["telefone1Pessoa"] = $oPessoa -> telefone1Pessoa : null;
        isset($oPessoa -> enderecoLogradouroPessoa) ? $arrayPessoa["enderecoLogradouroPessoa"] = $oPessoa -> enderecoLogradouroPessoa : null;
        isset($oPessoa -> enderecoNumeroPessoa) ? $arrayPessoa["enderecoNumeroPessoa"] = $oPessoa -> enderecoNumeroPessoa : null;
        isset($oPessoa -> enderecoBairroPessoa) ? $arrayPessoa["enderecoBairroPessoa"] = $oPessoa -> enderecoBairroPessoa : null;
        isset($oPessoa -> enderecoMunicipioPessoa) ? $arrayPessoa["enderecoMunicipioPessoa"] = $oPessoa -> enderecoMunicipioPessoa : null;
        isset($oPessoa -> enderecoUFPessoa) ? $arrayPessoa["enderecoUFPessoa"] = $oPessoa -> enderecoUFPessoa : null;
        isset($oPessoa -> enderecoCEPPessoa) ? $arrayPessoa["enderecoCEPPessoa"] = $oPessoa -> enderecoCEPPessoa : null;
        isset($oPessoa -> enderecoIBGEPessoa) ? $arrayPessoa["enderecoIBGEPessoa"] = $oPessoa -> enderecoIBGEPessoa : null;
        isset($oPessoa -> enderecoSIAFIPessoa) ? $arrayPessoa["enderecoSIAFIPessoa"] = $oPessoa -> enderecoSIAFIPessoa : null;
        isset($oPessoa -> enderecoGIAPessoa) ? $arrayPessoa["enderecoGIAPessoa"] = $oPessoa -> enderecoGIAPessoa : null;
        
        if($lRetorno){

            // Gera nova senha provisória e cadastra o usuário
            $senhaDescripto = $this -> gerar_senha( 6, true, true, true, true );
            $arrayPessoa["senhaPessoa"] = md5( $senhaDescripto );
            $arrayPessoa["nomePessoa"] = mb_convert_case( $arrayPessoa["nomePessoa"],  MB_CASE_TITLE, 'UTF-8' );

            $this -> Model -> InsertPessoa($arrayPessoa);
            $idPessoa = $this -> Model -> GetConsult();
            
            // Inspeciona erro retornado em caso de duplicidade detectada
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
                        $cMensagemErro = "O e-mail $valorDuplicado";
                    }
                    if($indiceValorDuplicado == 'identidadePessoa_UNIQUE'){
                        $cMensagemErro = "A identidade $valorDuplicado";
                    }
                    if($indiceValorDuplicado == 'usuarioPessoa_UNIQUE'){
                        $cMensagemErro = "O nome de usuário $valorDuplicado";
                    }
                    $cMensagemErro .= " já existe no cadastro de outra pessoa!";
                }
                $this -> RespostaRuimHTTP(400,$cMensagemErro,"Requisição Mal Feita",0);
                exit;
            }

            // Envia mensagem por e-Mail
            $cMailCharSet = 'UTF-8';
            $cMailHeaders = '';
            $cMailOrigem = 'sac@monibus.tecnologia.ws';
            $cMailNomeOrigem = 'SAC Monibus';
            $cMailResposta = 'sac@monibus.tecnologia.ws';
            $cMailNomeResposta = 'SAC Monibus';
            $cMailDestino = $arrayPessoa["emailPessoa"];
            $cMailNomeDestino = $arrayPessoa["nomePessoa"];
            $cMailAssunto = 'Seu usuário ' . $arrayPessoa["usuarioPessoa"] . ' foi criado com sucesso' ;
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
                    <p>Nome: ' . $arrayPessoa["nomePessoa"] . '<br>
                    Usuário: <b>' . $arrayPessoa["usuarioPessoa"] . '</b><br>
                    Senha provisória: <b>' . $senhaDescripto . '</b><br>
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
            $this -> Mail -> FromName = $cMailNomeOrigem; 
            $this -> Mail -> addAddress($cMailDestino, $cMailNomeDestino); 
            $this -> Mail -> addAddress($cMailResposta); 
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
            $arrayPessoa["idPessoa"] = $idPessoa;
            $arrayPessoa["nomePessoa"] = $oPessoa -> nomePessoa;
            $arrayPessoa["emailPessoa"] = $oPessoa -> emailPessoa;
            $arrayPessoa["tipoPessoa"] = $oPessoa -> tipoPessoa;
            $this -> Model -> UpdatePessoa($arrayPessoa);
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