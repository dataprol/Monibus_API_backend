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
 
        $this -> nPagina = intval( isset($_GET["pagina"]) ? $_GET["pagina"] : $this -> nPagina ) ;
        $this -> PaginacaoItensPorPagina = intval( isset($_GET["itensPorPagina"]) ? $_GET["itensPorPagina"] : $this -> PaginacaoItensPorPagina ) ;
        $nIdRelacionamento = $this -> nIdRelacionamento ;
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

        $data['nome'] = $cNome;
        $data['mensagem'] = $cMensagem;
        $data['código'] = $nCodigo;
        $data['situação'] = $nCodigoHTTP;
        $retorno['sucesso'] = "false";
        $retorno['dados'] = $data;
        echo json_encode( $retorno );
        http_response_code($nCodigoHTTP);

    }

    public function RespostaBoaHTTP($nCodigoHTTP,$arrayItens){

        header( 'Content-Type: application/json' );
        $pagination['página'] = $this -> nPagina;
        $pagination['páginasTotais'] = $this -> nTotalPaginas;
        $pagination['itensPorPágina'] = $this -> PaginacaoItensPorPagina;
        $pagination['itensTotais'] = $this -> nTotalItens;
        $retorno['sucesso'] = "true";
        $retorno['paginação'] = $pagination;
        $retorno['dados'] = $arrayItens;
        echo json_encode( $retorno );
        http_response_code($nCodigoHTTP);

    }

}

?>