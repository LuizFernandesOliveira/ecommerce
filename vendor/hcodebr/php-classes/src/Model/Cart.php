<?php


namespace Hcode\Model;


use Hcode\DB\Sql;
use Hcode\Model;

class Cart extends Model
{
    const SESSION = "Cart";

    protected $fields = [
        "iduser", "idperson", "deslogin",
        "despassword", "inadmin", "dtergister",
        "desperson", "nrphone", "desemail",
        "idcategory", "descategory", "idproduct",
        "idcart", "dessessionid", "deszipcode","vlfreight",
        "nrdays"
    ];

    public static function getFromSession()
    {


        $cart = new Cart();
        if(isset($_SESSION[Cart::SESSION]) && $_SESSION[Cart::SESSION]['idcart'] > 0){
            $cart->get($_SESSION[Cart::SESSION]['idcart']);
        var_dump($_SESSION[Cart::SESSION]);
        }else{

            $cart->getFromSessionID();
            if(!(int)$cart->getidcart()>0){
                $data = [
                    'dessessionid'=>session_id()
                ];
                if(User::checkLogin(false)){
                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();
                }
                $cart->setData($data);
                $cart->save();
                $cart->setSession();
            }
        }
        return $cart;
    }

    public function setSession(){
        $_SESSION[Cart::SESSION] = $this->getValues();
    }


    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",
            array(
                ":idcart" => $this->getidcart(),
                ":dessessionid" => $this->getdessessionid(),
                ":iduser" => $this->getiduser(),
                ":deszipcode" => $this->getdeszipcode(),
                ":vlfreight" => $this->getvlfreight(),
                ":nrdays" => $this->getnrdays(),
            ));

        $this->setData($results[0]);
    }

    public function get(int $idcart)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ":idcart" => $idcart
        ]);

        if(count($results)>0) {
            $this->setData($results[0]);
        }
    }

    public function getFromSessionID()
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ":dessessionid" => session_id()
        ]);

        if(count($results)>0) {
            $this->setData($results[0]);
        }
    }
}