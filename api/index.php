<?php 
    
	require_once("config/config.php");

	session_start(); 
	
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

    if( !isset( $uriSegments[1] ) ){

        require_once("controllers/MainController.php");
        $Main = new MainController();
        $Main -> index();

	}
	else
	{
		
		switch( $uriSegments[1] ){

            case 'main':
                
                require_once("controllers/MainController.php");
                $Main = new MainController();
                
                if( ! isset( $uriSegments[2] ) ){
                    $Main -> index();
                }
                else
                {
                    switch( $uriSegments[2] ){
                        case 'index': $Main -> index(); break;
                        case 'login': $Main -> login(); break;
						case 'sd': $Main -> destroySession(); break;
                    }
                }
			break;

            case 'users':
			
                require_once("controllers/UsersController.php");
                $User = new UsersController();
                if( ! isset( $uriSegments[2] ) ){
                    $User -> index();
                }
                else
                {
                    switch( $uriSegments[2] ){
                        case 'index': $User -> index(); break;
                        case 'vlogin': $User -> validateLogin(); break;
                        case 'vtoken': $User -> validateToken(); break;
                    }
                }
            break;
			
		}
	}

?>