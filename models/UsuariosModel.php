<?php

class UsuariosModel{

    var $resultado;
    var $Conn;

    function __construct(){
        
        require_once("database/ConexaoClass.php");
        $oConexao = new ConexaoClass();
        $oConexao -> openConnect();
        $this -> Conn = $oConexao -> getConnect();        

    }

    public function consultaUsuario( $userName ){

        $sql = "SELECT * FROM pessoas as p WHERE usuarioPessoa = '$userName' ";

        $this -> resultado = $this -> Conn -> query( $sql );

    }
    
    public function consultaUsuarioEmpresas( $userName ){

        // Seleciona empresas em que o usuário é administrador ou monitor
        $sql = "SELECT * FROM pessoas_tem_empresas as etp 
            inner join pessoas as p 
                on etp.idPessoa = p.idPessoa and p.usuarioPessoa = '$userName' 
            inner join empresas as e 
                on e.idEmpresa = etp.idEmpresa 
            ";//where etp.tipoPessoa = 'A' ";

        $this -> resultado = $this -> Conn -> query( $sql );

    }
    
    public function consultaUsuarioId( $userId ){

        $sql = "SELECT * FROM pessoas as p
                WHERE idPessoa='$userId'";

        $this -> resultado = $this -> Conn -> query( $sql );

    }
    
    public function getConsult(){

        return $this -> resultado;

    }
    
    public function AtualizarSenhaUsuario($arrayUsers){

        $sql = "UPDATE pessoas 
                SET senhaPessoa='" . md5( $arrayUsers['senhaPessoa'] ) . "', 
                WHERE idPessoa=" . $arrayUsers['idPessoa'] ;
        $this -> resultado = $this -> Conn -> query($sql);
        
    }

    public function InsertUsuario($arrayUsers){

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
                `enderecoGIAPessoa` ) 
                VALUE(
                    '" . $arrayUsers["nomePessoa"] . "', 
                    '" . $arrayUsers['identidadePessoa'] . "', 
                    '" . $arrayUsers['emailPessoa'] . "', 
                    '" . $arrayUsers['tipoPessoa'] . "', 
                    '" . $arrayUsers['usuarioPessoa'] . "', 
                    '" . $arrayUsers['senhaPessoa'] . "', 
                    '" . $arrayUsers['dataNascimentoPessoa'] . "',
                    '" . $arrayUsers['telefone1Pessoa'] . "', 
                    '" . $arrayUsers['enderecoLogradouroPessoa'] . "', 
                    '" . $arrayUsers['enderecoNumeroPessoa'] . "', 
                    '" . $arrayUsers['enderecoBairroPessoa'] . "', 
                    '" . $arrayUsers['enderecoMunicipioPessoa'] . "', 
                    '" . $arrayUsers['enderecoUFPessoa'] . "', 
                    '" . $arrayUsers['enderecoCEPPessoa'] . "', 
                    '" . $arrayUsers['enderecoIBGEPessoa'] . "', 
                    '" . $arrayUsers['enderecoSIAFIPessoa'] . "', 
                    '" . $arrayUsers['enderecoGIAPessoa'] . "'
                    );";

        $this -> Conn -> query($sql);
        $this -> resultado = $this -> Conn -> insert_id;

    }

    public function InsertEmpresaUsuario($arrayempresas){
        
        $sql = "INSERT INTO empresas( `nomeEmpresa`
                                    ,`identidadeEmpresa`) 
                VALUE('" . $arrayempresas['nomeEmpresa'] . "'
                    ,'" . $arrayempresas['identidadeEmpresa'] . "'
                    );";
                    
        $this -> Conn -> query($sql);
        $idEmpresa = $this -> Conn -> insert_id;
        
        if( $idEmpresa > 0 ){
            
            $sql = "INSERT INTO pessoas_tem_empresas( `idEmpresa`
                                        ,`idPessoa`
                                        ,`tipoPessoa`
                                        ) 
                    VALUE(" . $idEmpresa . "
                        ," . $arrayempresas['idPessoa'] . "
                        ,'" . $arrayempresas['tipoPessoa'] . "'
                        );";

            if( ! $this -> Conn -> query($sql) ){
                //$this -> resultado = 0;
            }

        }

    }

    public function SalvarIdRecuperacaoAcessoUsuario( $id, $idRecuperacaoAcesso ){

        $sql = "UPDATE pessoas 
                SET idRecuperacaoAcesso = '$idRecuperacaoAcesso'
                , validadeRecuperacaoAcesso = 
                '".
                (new DateTime) -> add( new DateInterval('PT10M') ) -> format('Y-m-d H:i:s')
                ."' 
                WHERE idPessoa = $id" ;
                
        $this -> resultado = $this -> Conn -> query($sql);
        
        echo $this -> Conn -> error.' / ';
        echo $sql;

    }

    public function ConsultaUsuarioEmail( $email ){

        $sql = "SELECT * FROM pessoas as p
                WHERE p.emailPessoa='$email'";

        $this -> resultado = $this -> Conn -> query( $sql );

    }

/*     public function FilterList($else_sql_where){

        $cUserTipo = $_SESSION['usuarioTipo'];
        $cUserId = $_SESSION['usuarioId'];
        $sql = "";

        if( $cUserTipo == 'A' ){
            // Seleciona empresas em que o usuário é administrador ou monitor
            $sqlEmpresaUsuario = "SELECT * FROM pessoas_tem_empresas";
            $sqlEmpresaUsuario .= " WHERE idPessoa = $cUserId and tipoPessoa ='A'"; 
            $aEmpresas = array();
            $resultado = $this -> Conn -> query( $sqlEmpresaUsuario );
            if( $resultado != false ){
                if( $resultado -> num_rows > 0 ){ 
                    while( $line = $resultado -> fetch_assoc() ) {
                        array_push( $aEmpresas, $line );
                    }
                    $nIdEmpresa = $aEmpresas[0]["idEmpresa"];
                }
            }
            // filtra passageiros da empresa X
            $sql .= " inner join pessoas_tem_empresas as ep";
            $sql .= " on ep.idPessoa = p.idPessoa";
            $sql .= " and ep.idEmpresa = $nIdEmpresa" ;
            $sql .= " and ep.tipoPessoa = 'P'";
        }else{
            $sql = $else_sql_where;
        }

        return $sql;
    }
 */
}