<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Category extends Model
{

    protected $fields = [
        "iduser", "idperson", "deslogin",
        "despassword", "inadmin", "dtergister",
        "desperson", "nrphone", "desemail",
        "idcategory", "descategory", "idproduct"
    ];


    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("select * from tb_categories order BY descategory");

    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
            array(
                ":idcategory" => $this->getidcategory(),
                ":descategory" => $this->getdescategory()
            ));

        $this->setData($results[0]);
        Category::updateFile();
    }

    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory" => $idcategory
        ]);

        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory" => $this->getidcategory()
        ]);

        Category::updateFile();
    }

    public function updateFile()
    {
        $categories = Category::listAll();
        $html = [];
        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }

    public function getProducts($related = true)
    {
        $sql = new Sql();
        if ($related === true) {
            return $sql->select("select * from tb_products where idproduct IN(
                                        select a.idproduct
                                        from tb_products a 
                                        inner join tb_productscategories b on a.idproduct = b.idproduct 
                                        where b.idcategory = :idcategory);
                                    ", [
                ":idcategory"=>$this->getidcategory()
            ]);
        } else {
            return $sql->select("select * from tb_products where idproduct NOT IN(
                                        select a.idproduct
                                        from tb_products a 
                                        inner join tb_productscategories b on a.idproduct = b.idproduct 
                                        where b.idcategory = :idcategory);
                                    ", [
                ":idcategory"=>$this->getidcategory()
            ]);
        }
    }

    public function getProductPage($page = 1, $itensPerPage = 3)
    {

        $start = ($page - 1) * $itensPerPage;

        $sql = new Sql();

        $results = $sql->select("
            select SQL_CALC_FOUND_ROWS *
            from tb_products a
            inner join tb_productscategories b on a.idproduct = b.idproduct
            inner join tb_categories c on c.idcategory = b.idcategory
            where c.idcategory = :idcategory
            limit $start, $itensPerPage;
        ", [
            ':idcategory'=>$this->getidcategory()
        ]);

        $resultTotal = $sql->select("select FOUND_ROWS() as nrtotal;");
        
        return [
            'data'=>Product::checkList($results),
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"]/$itensPerPage)
        ];
    }

    public function addProduct(Product $product){
        $sql = new Sql();
        $sql->query("insert into tb_productscategories (idcategory, idproduct) values (:idcategory, :idproduct)", [
            ":idcategory"=>$this->getidcategory(),
            ":idproduct"=>$product->getidproduct()
        ]);
    }

    public function removeProduct(Product $product){
        $sql = new Sql();
        $sql->query("delete from tb_productscategories where idcategory = :idcategory and idproduct = :idproduct", [
            ":idcategory"=>$this->getidcategory(),
            ":idproduct"=>$product->getidproduct()
        ]);
    }
}

?>