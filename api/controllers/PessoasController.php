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

    public function updatePessoa( $id_pessoa ){

		$oPessoa = json_decode(file_get_contents("php://input"));

		if( ! isset( $id_pidPessoaessoa ) ){
			$idPessoa = $oPessoa -> idPessoa;
		}

		$this -> Model -> consultPessoa( $idPessoa );

		$arrayPessoas["idPessoa"] = $idPessoa;
        $arrayPessoas["nomePessoa"] = $oPessoa -> nomePessoa;
        $arrayPessoas["emailPessoa"] = $oPessoa -> emailPessoa;
        $arrayPessoas["telefone1Pessoa"] = $oPessoa -> telefone1Pessoa;
        $arrayPessoas["senhaPessoa"] = $oPessoa -> senhaPessoa;

        $this -> Model -> updatePessoa($arrayPessoas);

        header('Content-Type: application/json');
        echo('{ "Result": "true" }');
        
    }

    public function deletePessoa( $idPessoa ){

		if( ! isset( $idPessoa ) ){
			$idPessoa = json_decode(file_get_contents("php://input")) -> idPessoa;
		}

        $this -> Model -> deletePessoa( $idPessoa );

        header('Content-Type: application/json');
        echo('{ "Result": "true" }');

    }

}

?>