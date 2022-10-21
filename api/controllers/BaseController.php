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
    var $SecondParam = null;

    public function __construct(){}

    public function index(){
        $this -> listThis();
    }

    public function listThis(){}

    public function listPagination($pagina,$SecondParam){

        // Paginação
        $this -> Model -> CountRows( $SecondParam );
        $linha = $this -> Model -> getConsult() -> fetch_assoc();
        $this -> nTotalItens = intval( $linha["total_linhas"] );
        $this -> nTotalPaginas = ceil( $this -> nTotalItens / $this -> PaginacaoItensPorPagina );
        if( isset($pagina) and $pagina > 0 ){
            $this -> nPagina = $pagina;
        }
        if( $this -> nPagina > $this -> nTotalPaginas and $this -> nTotalPaginas > 0 ){
            $this -> nPagina = $this -> nTotalPaginas;
        }
        $this -> nComecarPor = $this -> PaginacaoItensPorPagina * ( $this -> nPagina - 1 );

    }

}

?>