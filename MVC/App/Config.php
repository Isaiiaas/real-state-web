<?php

namespace App;

//Esta classe foi criada especificamente para armazenar os dados do banco de dados, isso foi feito para nao trabalhar em cima do core, pois se vermos na guia anterior tais dados estavam no Model.php existente da pasta core, e como os arquivos da pasta Core devem ser raramente alterados, criamos essa classe aqui.

class Config
{

    //Criamos uma constante que armazena o host do banco de dados
    const DB_HOST = 'localhost';

    //Criamos uma constante que armazena o nome do banco de dados
    const DB_NAME = 'database';

    //Criamos uma constante que armazena o nome de usuario do banco de dados
    const DB_USER = 'user';

    //Criamos uma constante que armazena a senha de acesso do banco de dados
    const DB_PASSWORD = 'pass';

    //Criamos uma constante que armazena o resultado dos erros, tenha em mente que para o codigo de desenvolvimento ela deve estar setada para true e para o codigo de produção ela deverá estar setada para false
    const SHOW_ERRORS = true;

    //Const for langhage default;
    const LANG = "English"; //Nome do arquivo de tradução em App/Language

    //Customized const
    const URL = 'http://localhost/';


}

?>