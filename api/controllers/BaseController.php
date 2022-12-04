<?php

//namespace Controlllers;

class BaseController{

    // Paginação
    var $nLimiteNaBarra = 6;
    var $nPagina = 1;
    var $PaginacaoItensPorPagina = _PaginacaoItensPorPagina;
    var $nTotalItens = 0;
    var $nTotalPaginas = 0;
    var $cViewListURL;
    var $nComecarPor;
    var $nIdRelacionamento = null;

    public function __construct(){}

    public function Index(){
        $this -> ListThis();
    }

    public function ListThis(){}

    public function ListPagination(){
 
        if( isset($_GET["pagina"]) && intval( $_GET["pagina"] ) > 0 ){
            $this -> nPagina = intval( $_GET["pagina"] );
        }
        if( isset($_GET["itensPorPagina"]) && intval( $_GET["itensPorPagina"] ) > 0 ){
            $this -> PaginacaoItensPorPagina = intval( $_GET["itensPorPagina"] );
        }
        $nIdRelacionamento = $this -> nIdRelacionamento;
        $this -> Model -> CountRows( $nIdRelacionamento );
        $linha = $this -> Model -> GetConsult() -> fetch_assoc();
        $this -> nTotalItens = intval( $linha["total_linhas"] );
        $this -> nTotalPaginas = ceil( $this -> nTotalItens / $this -> PaginacaoItensPorPagina );
        if( $this -> nPagina > $this -> nTotalPaginas and $this -> nTotalPaginas > 0 ){
            $this -> nPagina = $this -> nTotalPaginas;
        }
        $this -> nComecarPor = $this -> PaginacaoItensPorPagina * ( $this -> nPagina - 1 );

        $this -> Model -> ListThis( 
                                $this -> nComecarPor, 
                                $this -> PaginacaoItensPorPagina, 
                                $this -> nIdRelacionamento );
        
        return $this -> Model -> GetConsult();

    }

    public function RespostaRuimHTTP($nCodigoHTTP,$cMensagem,$cNome,$nCodigo){
        
        $data['name'] = $cNome;
        $data['message'] = $cMensagem;
        $data['code'] = $nCodigo;
        $data['status'] = $nCodigoHTTP;
        $retornoHASH['success'] = "false";
        $retornoHASH['data'] = $data;
        header( 'Content-Type: application/json' );
        $retornoJSON =  json_encode( $retornoHASH );
        if($retornoJSON){
            echo $retornoJSON;
        }else{
            echo '{"message":"Erro na tentativa de conversão para JSON!","success":"false"}';
        }
        http_response_code( $nCodigoHTTP );

    }

    public function RespostaBoaHTTP($nCodigoHTTP,$dataInfo){

        $pagination['page'] = $this -> nPagina;
        $pagination['pagesTotal'] = $this -> nTotalPaginas;
        $pagination['itemsPerPage'] = $this -> PaginacaoItensPorPagina;
        $pagination['itemsTotal'] = $this -> nTotalItens;
        $retornoHASH['success'] = "true";
        $retornoHASH['pagination'] = $pagination;
        if( ! is_null($dataInfo) ){
            if( is_string($dataInfo) ){
                $retornoHASH['message'] = $dataInfo;
            }else{
                $retornoHASH['data'] = $dataInfo;
            }
        }
        $retornoJSON = json_encode( $retornoHASH );
        header( 'Content-Type: application/json' );
        if($retornoJSON){
            echo $retornoJSON;
        }else{
            $this -> RespostaRuimHTTP(500,"Algo deu errado com a conversão para JSON!","Erro Interno",0);
        }
        http_response_code( $nCodigoHTTP );

    }

}

?>