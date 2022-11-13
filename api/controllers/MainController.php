<?php
    
    class MainController{

        public function Index(){
            if( !isset($_SESSION["usuarioLogin"]) ){
				header("Location: main/login");
            }
        }
        
        public function Login(){
			header("Location: usuarios/vlogin");
        }
        
        public function Logoff(){
            $this -> DestroySession();
			exit;
        }
        
        public function DestroySession(){
            session_destroy();
			header('Content-Type: application/json');
			echo('{ "Result": "sessão destruída!" }');
			exit;
        }

    }
    
?>