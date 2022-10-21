<?php

class PessoasController extends BaseController {
    
    function __construct(){
		
		/* require_once("controllers/UsersController.php");
		$User = new UsersController();
		if( ! isset( $_SESSION[ "login" ] ) ){
			$User -> validateLogin();
		}else{
			$User -> validateToken();
		} */
		
		require_once("models/PessoasModel.php");
        $this -> Model = new PessoasModel();
		
    }

    public function listThis(){

        $this -> nPagina = intval( (isset($_GET["pag"])) ? $_GET["pag"] : $this -> nPagina );
        $this -> listPagination( $this -> nPagina, $this -> SecondParam );
        $this -> Model -> listThis( $this -> nComecarPor, 
                                    $this -> PaginacaoItensPorPagina, 
                                    $this -> SecondParam );
        $result = $this -> Model -> getConsult();
        $arrayPessoas = array();
        if( $result != false ){
            if( $result -> num_rows != 0 ){ 
                while( $line = $result -> fetch_assoc() ) {
                    array_push( $arrayPessoas, $line );
                }
            }
        }
        header( 'Content-Type: application/json' );
        $retorno['message'] = "Lista de pessoas obtida com sucesso!";
        $retorno['codeHttp'] = 201;
        $data['page'] = $this -> nPagina;
        $data['pagesTotal'] = $this -> nTotalPaginas;
        $data['itemsPerPage'] = $this -> PaginacaoItensPorPagina;
        $data['itemsTotal'] = $this -> nTotalItens;
        $data['items'] = $arrayPessoas;
        $retorno['data'] = $data;
        echo json_encode( $retorno );

    }

	public function consultPessoa( $id ){
		
		$this -> Model -> consultPessoa( $id );
		$result = $this -> Model -> getConsult();
		$pessoa = $result -> fetch_assoc();		
		header( 'Content-Type: application/json' );
		echo json_encode($pessoa);
		
	}

    public function insertPessoa(){
		
        $oPessoa = json_decode( file_get_contents("php://input") );

        $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa;
        $arrayPessoas["identidadePessoa"] = $oPessoa -> identidadePessoa;
        $arrayPessoas["emailPessoa"]  = $oPessoa -> emailPessoa;
        $arrayPessoas["tipoPessoa"] = $oPessoa -> tipoPessoa;
        $arrayPessoas["senhaPessoa"] = $oPessoa -> senhaPessoa;
        $arrayPessoas["usuarioPessoa"] = $oPessoa -> usuarioPessoa;

        $this -> Model -> insertPessoa($arrayPessoas);
        $id_pessoa = $this -> Model -> getConsult();

        header('Content-Type: application/json');
        echo('{ "Result": "true", "id": '.$id_pessoa.' }');

    }

    public function updatePessoa( $idPessoa ){

		$oPessoa = json_decode(file_get_contents("php://input"));
        
		if( empty( $idPessoa ) ){
			$idPessoa = $oPessoa -> idPessoa;
		}
        
        if( !empty($idPessoa)){
            $this -> Model -> consultPessoa( $idPessoa );
            $arrayPessoas["idPessoa"] = $idPessoa;
            $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa;
            $arrayPessoas["identidadePessoa"] = $oPessoa -> identidadePessoa;
            $arrayPessoas["emailPessoa"] = $oPessoa -> emailPessoa;
            $arrayPessoas["usuarioPessoa"] = $oPessoa -> usuarioPessoa;
            $arrayPessoas["senhaPessoa"] = $oPessoa -> senhaPessoa;
            $arrayPessoas["tipoPessoa"] = $oPessoa -> tipoPessoa;
            $this -> Model -> updatePessoa($arrayPessoas);
            header('Content-Type: application/json');
            echo('{ "Result": "true" }');
        }else{
            header('Content-Type: application/json');
            echo('{ "Result": "false" }');
        }
        
    }

    public function deletePessoa( $idPessoa ){

		if( empty( $idPessoa ) ){
			$idPessoa = json_decode(file_get_contents("php://input")) -> idPessoa;
		}
        
        if( !empty($idPessoa)){
            $this -> Model -> deletePessoa( $idPessoa );
            header('Content-Type: application/json');
            echo('{ "Result": "true" }');
        }else{
            header('Content-Type: application/json');
            echo('{ "Result": "false" }');
        }    

    }

}

?>