<?php

namespace Hcode\Model;

use Hcode\Mailer;
use \Hcode\Model;
use \Hcode\DB\Sql;

class Category extends Model
{

    protected $fields = [
        "iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister", "desperson", "nrphone", "desemail", "idcategory", "descategory"
    ];


    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("select * from tb_categories order BY descategory");

    }

    public function save(){
        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
            array(
                ":idcategory" => $this->getidcategory(),
                ":descategory" => $this->getdescategory()
            ));

        $this->setData($results[0]);
    }

    public function get($idcategory){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=>$idcategory
        ]);

        $this->setData($results[0]);
    }

    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",[
            ":idcategory"=>$this->getidcategory()
        ]);
    }

    public function update(){

    }
}

?>