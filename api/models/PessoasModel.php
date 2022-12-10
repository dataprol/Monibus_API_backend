<?php
//namespace Models;
final class PessoasModel{
    
    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> OpenConnect();
        $this -> Conn = $oConexao -> GetConnect();

    }

    public function CountRows($nIdRelacionamento){
        
        $sql = "SELECT COUNT(*) as total_linhas FROM pessoas as p";
        
        $sql .= $this -> FilterList($nIdRelacionamento);
        
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function FilterList($nIdRelacionamento){

        $cUserTipo = $_SESSION['usuarioTipo'];
        $cUserId = $_SESSION['usuarioId'];
        $sql = "";

        if( $cUserTipo == 'A' ){
            // Seleciona empresas em que a pessoa é administradora ou monitora
            $sqlEmpresaUsuario = "SELECT * FROM pessoas_tem_empresas";
            $sqlEmpresaUsuario .= " WHERE idPessoa = $cUserId and tipoPessoa ='A'"; 
            $aEmpresas = array();
            $resultado = $this -> Conn -> query( $sqlEmpresaUsuario );
            if( $resultado != false ){
                if( $resultado -> num_rows > 0 ){ 
                    while( $line = $resultado -> fetch_assoc() ) {
                        array_push( $aEmpresas, $line );
                    }
                    $nIdRelacionamento = $aEmpresas[0]["idEmpresa"];
                }
            }
            // filtra passageiros da empresa X
            $sql .= " inner join pessoas_tem_empresas as ep";
            $sql .= " on ep.idPessoa = p.idPessoa";
            $sql .= " and ep.idEmpresa = $nIdRelacionamento" ;
            $sql .= " and ep.tipoPessoa = 'P'";
        }else{
            $sql = " WHERE p.idPessoa = 0";
        }

        return $sql;
    }

    public function ListThis( $nComecarPor, $nItensPorPagina, $nIdRelacionamento ){

        
        $colunas = "*";
        $sql = "SELECT $colunas";
        $sql .= " FROM pessoas as p";
        
        $sql .= $this -> FilterList($nIdRelacionamento);

        $sql .= " ORDER BY dataCadastroPessoa DESC";
        $sql .= " LIMIT $nComecarPor, $nItensPorPagina";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultPessoa($id){

        $sql = "SELECT * FROM pessoas WHERE idPessoa = " . $id . ";" ;
        
        $this -> resultado = $this -> Conn -> query($sql);

    }

    public function InsertPessoa($arrayPessoa){

        $sql = "INSERT INTO pessoas(
                `nomePessoa`,
                `identidadePessoa`,
                `emailPessoa`,
                `tipoPessoa`,
                `usuarioPessoa`,
                `senhaPessoa`,
                `dataNascimentoPessoa`,
                `telefone1Pessoa`,
                `enderecoLogradouroPessoa`,
                `enderecoNumeroPessoa`,
                `enderecoBairroPessoa`,
                `enderecoMunicipioPessoa`,
                `enderecoUFPessoa`,
                `enderecoCEPPessoa`,
                `enderecoIBGEPessoa`,
                `enderecoSIAFIPessoa`,
                `enderecoGIAPessoa`, 
                `presencaPessoa` ) 
                VALUE(
                    '" . $arrayPessoa["nomePessoa"] . "', 
                    '" . $arrayPessoa['identidadePessoa'] . "', 
                    '" . $arrayPessoa['emailPessoa'] . "', 
                    '" . $arrayPessoa['tipoPessoa'] . "', 
                    '" . $arrayPessoa['usuarioPessoa'] . "', 
                    '" . $arrayPessoa['senhaPessoa'] . "', 
                    '" . $arrayPessoa['dataNascimentoPessoa'] . "',
                    '" . $arrayPessoa['telefone1Pessoa'] . "', 
                    '" . $arrayPessoa['enderecoLogradouroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoNumeroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoBairroPessoa'] . "', 
                    '" . $arrayPessoa['enderecoMunicipioPessoa'] . "', 
                    '" . $arrayPessoa['enderecoUFPessoa'] . "', 
                    '" . $arrayPessoa['enderecoCEPPessoa'] . "', 
                    '" . $arrayPessoa['enderecoIBGEPessoa'] . "', 
                    '" . $arrayPessoa['enderecoSIAFIPessoa'] . "', 
                    '" . $arrayPessoa['enderecoGIAPessoa'] . "',
                    '" . $arrayPessoa['presencaPessoa'] . "'
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

        echo $this -> Conn -> error.' / ';
        echo $sql;

    }

    public function UpdatePessoa($arrayPessoa){

        $sql = "UPDATE pessoas 
            SET nomePessoa='" . $arrayPessoa['nomePessoa'] . "'
                ,emailPessoa='" . $arrayPessoa['emailPessoa'] . "'
                ,identidadePessoa='" . $arrayPessoa['identidadePessoa'] . "'
                ,dataNascimentoPessoa='" . $arrayPessoa['dataNascimentoPessoa'] . "' 
                ,telefone1Pessoa='" . $arrayPessoa['telefone1Pessoa'] . "' 
                ,enderecoLogradouroPessoa='" . $arrayPessoa['enderecoLogradouroPessoa'] . "' 
                ,enderecoNumeroPessoa='" . $arrayPessoa['enderecoNumeroPessoa'] . "' 
                ,enderecoBairroPessoa='" . $arrayPessoa['enderecoBairroPessoa'] . "' 
                ,enderecoMunicipioPessoa='" . $arrayPessoa['enderecoMunicipioPessoa'] . "' 
                ,enderecoCEPPessoa='" . $arrayPessoa['enderecoCEPPessoa'] . "' 
                ,enderecoIBGEPessoa='" . $arrayPessoa['enderecoIBGEPessoa'] . "' 
                ,enderecoSIAFIPessoa='" . $arrayPessoa['enderecoSIAFIPessoa'] . "' 
                ,enderecoGIAPessoa='" . $arrayPessoa['enderecoGIAPessoa'] . "' 
                ,presencaPessoa='" . $arrayPessoa['presencaPessoa'] . "' 
            WHERE idPessoa=" . $arrayPessoa['idPessoa'] . ";" ;

        $this -> resultado = $this -> Conn -> query($sql);
        
        echo $this -> Conn -> error.' / ';
        echo $sql;

    }

    public function DeletePessoa($id){

        $sql = "DELETE FROM pessoas WHERE idPessoa='" . $id . "';" ;

        $this -> resultado = $this -> Conn-> query($sql);

        echo $this -> Conn -> error.' / ';
        echo $sql;

    }

    public function GetConsult(){
        return $this -> resultado;
    }

    public function consultUsuario( $username ){

        $sql = "SELECT * FROM pessoas 
                WHERE usuarioPessoa='$username'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }

    public function ConsultUsuarioEmail( $emailPessoa ){
        
        $sql = "SELECT * FROM pessoas 
                WHERE emailPessoa='$emailPessoa'";
        $this -> resultado = $this -> Conn -> query( $sql );

    }

}

?>