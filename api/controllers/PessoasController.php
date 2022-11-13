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
        /* será que o objeto oPessoa não pode 
        ser repassado à Model, ao invés de 
        armazenar cada valor na matriz arrayPessoas? */

/*         // Confere se login já está em uso
        $this -> Model -> ConsultUsuario( $_POST["usuarioPessoa"] );
        $arrayUsers = $this -> Model -> getConsult() -> fetch_assoc();
        if( !empty( $arrayUsers ) ){
            $this -> RespostaRuimHTTP(400,"já existe usuário com este nome de usuário informado!","Requisição Mal Feita",0);
            exit;
        } */

        if($lRetorno){

            $this -> Model -> ConsultUsuarioEmail( $_POST["emailPessoa"] );
            $arrayUsers = $this -> Model -> getConsult() -> fetch_assoc();
            if( !empty( $arrayUsers ) ){
                $this -> RespostaRuimHTTP(400,"já existe usuário com este e-mail informado!","Requisição Mal Feita",0);
                exit;
            }

            $this -> Model -> InsertPessoa($arrayPessoas);
            $idPessoa = $this -> Model -> GetConsult();

            $this -> SendMail();

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

    public function SendMail(){

        // Gera nova senha provisória e cadastra o usuário
        $novaSenha = $this -> gerar_senha( 6, true, true, true, true );
        $prazoDias = 3;
        $validadeSenha = new DateTime();
        $prazoSenha = new DateInterval( "P" . $prazoDias . "D" );
        $validadeSenha -> add( $prazoSenha );

        $arrayUsers["usuarioNome"]      = mb_convert_case( $_POST["usuarioNome"],  MB_CASE_TITLE, 'UTF-8' );
        $arrayUsers["usuarioEmail"]     = $_POST["usuarioEmail"];
        $arrayUsers["usuarioCliente"]        = $_POST["usuarioCliente"];
        $arrayUsers["usuarioTelefoneCelular"]     = $_POST["usuarioTelefoneCelular"];
        $arrayUsers["usuarioNivel"]     = $_POST["usuarioNivel"];
        $arrayUsers["usuarioLogin"]     = $_POST["usuarioLogin"];
        $arrayUsers["usuarioSenha"]     = md5( $novaSenha );
        $arrayUsers["usuarioSenhaValidade"] = $validadeSenha -> format( "Y-m-d" );
 
        //$this -> Model -> insertUser( $arrayUsers );
        $this -> Model -> InsertPessoa( $arrayUsers );

        $idUser = $this -> Model -> getConsult();

        if( $idUser > 0 ){

            $foto_temp = $_FILES["foto"]["tmp_name"]; //caminho temporário
            $foto_nome = $_FILES["foto"]["name"]; //obtem o nome
            $extensao = str_replace('.','',mb_strrchr($foto_nome,'.'));
            $max_width = 600;
            $max_height = 600;

            $img = null;

            // converte a imagem em JPG
            if($extensao=='jpg'||$extensao=='jpeg'){
                $img = @imagecreatefromjpeg($foto_temp);
            }else if($extensao=='png'){
                $img = @imagecreatefrompng($foto_temp);
            }else if($extensao=='gif'){
                $img = @imagecreatefromgif($foto_temp);
            }else{
                $img = @imagecreatefromjpeg($foto_temp);
            }

            // verifica tamanho da imagem
            if($img){
                $width = imagesx($img);
                $height = imagesy($img);
                $scale = min($max_width/$width,$max_height/$height);
                // Se a é maior que o permitido
                if($scale<1){
                    $new_width = floor($scale*$width);
                    $new_height = floor($scale*$height);
                    // Cria imagem temporária
                    $tmp_img = @imagecreatetruecolor($new_width,$new_height);
                    // Copia e redimenciona imagem
                    @imagecopyresampled($tmp_img,$img,0,0,0,0,$new_width,$new_height,$width,$height);
                    @imagedestroy($img);
                    $img = $tmp_img;
                }
            }

            // cria imagem na pasta adequada
            @imagejpeg( $img, $this->diretorios["arquivos"] . "/" . "users/" . $idUser . ".jpg" );
        
            // Envia mensagem por e-Mail
            $cMailCharSet = 'UTF-8';
            $cMailHeaders = '';
            $cMailOrigem = 'suporte@dataprol.com.br';
            $cMailNomeOrigem = 'Suporte Dataprol Sistemas';
            $cMailResposta = 'suporte@dataprol.com.br';
            $cMailNomeResposta = 'Suporte Dataprol Sistemas';
            $cMailDestino = $arrayUsers["usuarioEmail"];
            $cMailNomeDestino = $arrayUsers["usuarioNome"];
            $cMailAssunto = 'Seu usuário ' . $arrayUsers["usuarioLogin"] . ' foi criado com sucesso' ;
            $cMailmensagem = '
            <html lang="pt">
                <meta charset="' . mb_strtolower($cMailCharSet) . '">
                <meta name="author" content="Luiz Carlos Costa Rodrigues born in Santa Maria RS Brazil, www.dataprol.com.br">
                <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, shrink-to-fit=no">
                <head>
                    <title>Novo Usuário Dataprol</title>
                </head>
                <body>
                    <h1>Novo Usuário Dataprol</h1>
                    <h3>Seu cadastro foi concluído, com sucesso!</h3>
                    <p>Nome: ' . $arrayUsers["usuarioNome"] . '<br>
                    Usuário: <b>' . $arrayUsers["usuarioLogin"] . '</b><br>
                    Senha provisória: <b>' . $novaSenha . '</b><br>
                    <b>
                    Validade: ' . $prazoDias . ' dias</b>
                    <br>
                    Para trocar a senha, acesse <b><a href="https://dataprol.com.br/painel">Área do Cliente Dataprol</a></b>, 
                    entre com sua senha provisória, clique na sua imagem, no canto superior direito da tela, e clique em <b>Trocar Senha</b>.
                    <br>
                    <b>Troque sua senha, o quanto antes, para evitar seu bloqueio.</b>
                    </p>
                    <br><br>
                    Luiz Carlos Costa Rodrigues - Dataprol
                    <br>
                    <a href="https://www.dataprol.com.br/">www.dataprol.com.br</a>
                </body>
            </html>
            ';
            
            $this -> Mail -> setLanguage('br');                             // Habilita as saídas de erro em Português
            $this -> Mail -> CharSet='UTF-8';                               // Habilita o envio do email como 'UTF-8'
            
            //$this -> Mail -> SMTPDebug = SMTP::DEBUG_SERVER;                               // Habilita a saída do tipo "verbose"
            
            $this -> Mail -> isSMTP();                                      // Configura o disparo como SMTP
            $this -> Mail -> Host = 'email-ssl.com.br';                        // Especifica o enderço do servidor SMTP da Locaweb
            $this -> Mail -> SMTPAuth = true;                               // Habilita a autenticação SMTP
            $this -> Mail -> Username = 'luiz@dataprol.com.br';                        // Usuário do SMTP
            $this -> Mail -> Password = 'DpLc*494875';                          // Senha do SMTP
            $this -> Mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;                            // Habilita criptografia TLS | 'ssl' também é possível
            $this -> Mail -> Port = 587;                                    // Porta TCP para a conexão
            
            $this -> Mail -> From = $cMailOrigem;                          // Endereço previamente verificado no painel do SMTP
            $this -> Mail -> FromName = $cMailNomeResposta;                     // Nome no remetente
            $this -> Mail -> addAddress($cMailDestino, $cMailNomeDestino);// Acrescente um destinatário
            $this -> Mail -> addAddress('luiz@dataprol.com.br');                // O nome é opcional
            $this -> Mail -> addReplyTo($cMailOrigem, $cMailNomeResposta);
            //$this -> Mail -> addCC('cc@exemplo.com');
            //$this -> Mail -> addBCC('bcc@exemplo.com');
            
            $this -> Mail -> isHTML(true);                                  // Configura o formato do email como HTML
            
            $this -> Mail -> Subject = $cMailAssunto;
            $this -> Mail -> Body    = $cMailmensagem;
            $this -> Mail -> AltBody = strip_tags( $cMailmensagem );
            
            if(!$this -> Mail -> send()) 
            {

                echo '<h2>A mensagem não pode ser enviada</h2>';
                //echo 'Mensagem de erro: ' . $this -> Mail -> ErrorInfo;

                require_once("views/users/Falhou.php");
                exit;
                return;
                
            }else{
                
                require_once("views/users/AvisoSenhaEnviada.php");
                exit;
                return;
                
            }
                    
        }else{

            require_once("views/users/Falhou.php");
            exit;
            return;

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
            $arrayPessoas["senhaPessoa"] = $oPessoa -> senhaPessoa;
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