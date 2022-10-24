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
    var $nIdSegundaTabela = null;

    public function __construct(){}

    public function Index(){
        $this -> ListThis();
    }

    public function ListThis(){}

    public function ListPagination(){
 
        $pagina = intval( isset($_GET["pag"]) ? $_GET["pag"] : $this -> nPagina ) ;
        $nIdSegundaTabela = $this -> nIdSegundaTabela ;
        // Paginação
        $this -> Model -> CountRows( $nIdSegundaTabela );
        $linha = $this -> Model -> GetConsult() -> fetch_assoc();
        $this -> nTotalItens = intval( $linha["total_linhas"] );
        $this -> nTotalPaginas = ceil( $this -> nTotalItens / $this -> PaginacaoItensPorPagina );
        if( isset($pagina) and $pagina > 0 ){
            $this -> nPagina = $pagina;
        }
        if( $this -> nPagina > $this -> nTotalPaginas and $this -> nTotalPaginas > 0 ){
            $this -> nPagina = $this -> nTotalPaginas;
        }
        $this -> nComecarPor = $this -> PaginacaoItensPorPagina * ( $this -> nPagina - 1 );

        $this -> Model -> ListThis( 
                                $this -> nComecarPor, 
                                $this -> PaginacaoItensPorPagina, 
                                $this -> nIdSegundaTabela );
        
        return $this -> Model -> GetConsult();

    }

}

?>