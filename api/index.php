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
			
            case 'main':
                
                require_once("controllers/MainController.php");
                $Main = new MainController();
                
                if( ! isset( $uriSegments[2] ) ){
                    $Main -> Index();
                }
                else
                {
                    switch( $uriSegments[2] ){
                        case 'index': $Main -> Index(); break;
                        case 'login': $Main -> Login(); break;
						case 'sd': $Main -> DestroySession(); break;
                    }
                }
			break;

            case 'usuarios':
			
                require_once("controllers/UsuariosController.php");
                $User = new UsuariosController();
                if( ! isset( $uriSegments[2] ) ){
                    $User -> Index();
                }
                else
                {
                    switch( $uriSegments[2] ){
                        case 'index': $User -> Index(); break;
                        case 'vlogin': $User -> ValidateLogin(); break;
                        case 'vtoken': $User -> ValidateToken(); break;
						default:
						break;
					}
				}
			break;

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
							$EmpresaCTRL -> InsertEmpresa(null);
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

            case 'carros':

				require_once( "controllers/CarrosController.php" );
				$CarroCTRL = new CarrosController();
				if( ! isset( $request_method ) ){
					$CarroCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( ! isset( $uriSegments[2] ) ){
								$CarroCTRL -> ListThis();
							}
							else{
								$CarroCTRL -> ConsultCarro( $uriSegments[2] );
							}
							break;

						case 'POST':
							$CarroCTRL -> InsertCarro();
							break;

						case 'PUT':
							$CarroCTRL -> UpdateCarro( $uriSegments[2] );
							break;

						case 'DELETE':
							$CarroCTRL -> DeleteCarro( $uriSegments[2] );
							break;

						default:
							break;
					}
				}
            break;
			
            case 'linhas':

				require_once( "controllers/LinhasController.php" );
				$LinhaCTRL = new LinhasController();
				if( ! isset( $request_method ) ){
					$LinhaCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( ! isset( $uriSegments[2] ) ){
								$LinhaCTRL -> ListThis();
							}
							else{
								$LinhaCTRL -> ConsultLinha( $uriSegments[2] );
							}
							break;

						case 'POST':
							$LinhaCTRL -> InsertLinha();
							break;

						case 'PUT':
							$LinhaCTRL -> UpdateLinha( $uriSegments[2] );
							break;

						case 'DELETE':
							$LinhaCTRL -> DeleteLinha( $uriSegments[2] );
							break;

						default:
							break;
					}
				}
            break;
			
            case 'contratos':

				require_once( "controllers/ContratosController.php" );
				$ContratoCTRL = new ContratosController();
				if( ! isset( $request_method ) ){
					$ContratoCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( ! isset( $uriSegments[2] ) ){
								$ContratoCTRL -> ListThis();
							}
							else{
								$ContratoCTRL -> ConsultContrato( $uriSegments[2] );
							}
							break;

						case 'POST':
							$ContratoCTRL -> InsertContrato();
							break;

						case 'PUT':
							$ContratoCTRL -> UpdateContrato( $uriSegments[2] );
							break;

						case 'DELETE':
							$ContratoCTRL -> DeleteContrato( $uriSegments[2] );
							break;

						default:
							break;
					}
				}
            break;

			case 'turnos':

				require_once( "controllers/TurnosController.php" );
				$TurnoCTRL = new TurnosController();
				if( ! isset( $request_method ) ){
					$TurnoCTRL -> ListThis();
				}
				else{
					switch($request_method)
					{
						case 'GET':
							if( ! isset( $uriSegments[2] ) ){
								$TurnoCTRL -> ListThis();
							}
							else{
								$TurnoCTRL -> ConsultTurno( $uriSegments[2] );
							}
							break;

						case 'POST':
							$TurnoCTRL -> InsertTurno();
							break;

						case 'PUT':
							$TurnoCTRL -> UpdateTurno( $uriSegments[2] );
							break;

						case 'DELETE':
							$TurnoCTRL -> DeleteTurno( $uriSegments[2] );
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