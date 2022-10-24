<?php 
    
	require_once("config/config.php");

	session_start(); 
	
	require_once("controllers/BaseController.php");

	if( ! isset( $_SESSION[ "usuarioErroEsperar" ] ) ){
		$_SESSION["usuarioErroEsperar"] = .1;
	}

	if( ! isset( $_SESSION[ "status" ] ) ){
		$_SESSION['status'] = "logoff";
	}

	$request_method = $_SERVER["REQUEST_METHOD"];
	$query_str = $_SERVER['QUERY_STRING'];
	$local = $cHTTP_SERVER;
	$uri = $_SERVER['REQUEST_URI']; // ou $_SERVER["PHP_SELF"]. Depende do server.
	$rout = str_replace($local, "", $uri);
	$rout = str_replace($query_str, "", $rout);
	$rout = str_replace("?", "", $rout);
	$uriSegments = explode("/", $rout);

    if( !isset( $uriSegments[1] ) || empty($uriSegments[1]) ){

		header( 'Content-Type: application/json' );
		$data['name'] = "End-point principal API do Monibus";
		$data['message'] = "Esta é a raiz desta API!";
		$data['code'] = 0;
		$data['status'] = 200;
		$retorno['success'] = "true";
		$retorno['data'] = $data;
		echo json_encode( $retorno );
		http_response_code(200);
		exit;

	}
	else
	{
		switch( $uriSegments[1] ){

            case 'pessoas':
				
				require_once( "controllers/PessoasController.php" );
				$PessoaCTRL = new PessoasController();
				if( ! isset( $request_method ) ){
					$PessoaCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( isset( $uriSegments[2] ) ){
								$PessoaCTRL -> ConsultPessoa( $uriSegments[2] );
							}
							else{
								$PessoaCTRL -> ListThis();
							}
							break;

						case 'POST':
							$PessoaCTRL -> InsertPessoa();
							break;

						case 'PUT':
							$PessoaCTRL -> UpdatePessoa( $uriSegments[2] );
							break;

						case 'DELETE':
							$PessoaCTRL -> DeletePessoa( $uriSegments[2] );
							break;

						default:
							break;
					}
				}
            break;

            case 'empresas':

				require_once( "controllers/EmpresasController.php" );
				$EmpresaCTRL = new EmpresasController();
				if( ! isset( $request_method ) ){
					$EmpresaCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( ! isset( $uriSegments[2] ) ){
								$EmpresaCTRL -> ListThis();
							}
							else{
								$EmpresaCTRL -> ConsultEmpresa( $uriSegments[2] );
							}
							break;

						case 'POST':
							$EmpresaCTRL -> InsertEmpresa();
							break;

						case 'PUT':
							$EmpresaCTRL -> UpdateEmpresa( $uriSegments[2] );
							break;

						case 'DELETE':
							$EmpresaCTRL -> DeleteEmpresa( $uriSegments[2] );
							break;

						default:
							break;
					}
				}
            break;
			
			default:
				header( 'Content-Type: application/json' );
				$data['name'] = "Recurso Inexistente";
				$data['message'] = "O recurso requisitado não existe!";
				$data['code'] = 0;
				$data['status'] = 404;
				$retorno['success'] = "false";
				$retorno['data'] = $data;
				echo json_encode( $retorno );
				http_response_code(404);
				exit;
			break;
		}
	}

?>