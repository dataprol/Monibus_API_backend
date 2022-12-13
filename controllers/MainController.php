<?php
    
    class MainController{

        var $Usuario;
    
        public function Index(){
            require_once("controllers/UsuariosController.php");
            $this -> Usuario = new UsuariosController();
            $this -> Usuario -> ValidateTokenAction();
        }
        
        public function Login(){
			header("Location: usuarios/vlogin");
        }
        
        public function Logoff(){
            $this -> DestroySession();
			exit;
        }
        
        public function DestroySession(){
			header('Content-Type: application/json');
			echo('{ "Result": "sessão destruída!" }');
			exit;
        }

    }
    
?>