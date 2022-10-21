<?php
    
    class MainController{

        public function index(){
            if( !isset($_SESSION["login"]) ){
				header("Location: index.php/main/login");
            }
        }
        
        public function login(){
			header("Location: index.php/users/vlogin");
        }
        
        public function destroySession(){
            session_destroy();
			header('Content-Type: application/json');
			echo('{ "Result": "session destroyed!" }');
			exit;
        }

    }
    
?>