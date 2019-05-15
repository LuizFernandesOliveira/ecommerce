<?php


namespace Hcode\Model;


use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Model\User;

class Cart extends Model
{
    const SESSION = "Cart";
    const SESSION_ERROR = 'CartError';

    public static function getFromSession()
    {
        $cart = new Cart();

        if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        } else {
            if (!(int)$cart->getidcart() > 0) {
                $data = [
                    'dessessionid' => session_id()
                ];
                if (User::checkLogin(false)) {
                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();
                }

                $cart->setData($data);
                $cart->save();
                $cart->setToSession();
            }
        }
        return $cart;
    }

    public function setToSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function getFromSessionID()
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ":dessessionid" => session_id()
        ]);
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function get(int $idcart)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ":idcart" => $idcart
        ]);
        if (count($results) > 0) {
            $this->setData($results[0]);
        }
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
                ":nrdays" => $this->getnrdays()
            ));
        $this->setData($results[0]);
    }


    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("insert into tb_cartsproducts (idcart, idproduct) values (:idcart, :idproduct)", [
            ':idcart' => $this->getidcart(),
            ':idproduct' => $product->getidproduct()
        ]);

        $this->getCalculateTotal();
    }

    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();
        if ($all) {
            $sql->query("update tb_cartsproducts set dtremoved = NOW() where idcart = :idcart and idproduct = :idproduct and dtremoved is null", [
                ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ]);
        } else {
            $sql->query("update tb_cartsproducts set dtremoved = NOW() where idcart = :idcart and idproduct = :idproduct and dtremoved is null limit 1", [
                ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ]);
        }

        $this->getCalculateTotal();
    }

    public function getProducts()
    {
        $sql = new Sql();
        $rows = $sql->select("
            select b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, count(*) as nrqtd, sum(b.vlprice) as vltotal
            from tb_cartsproducts a 
            inner join tb_products b on a.idproduct = b.idproduct 
            where a.idcart = :idcart and a.dtremoved is null 
            group by b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
            order by b.desproduct
        ", [
            ":idcart" => $this->getidcart()
        ]);
        return Product::checkList($rows);
    }

    public function getProductsTotals()
    {
        $sql = new Sql();
        $results = $sql->select("
            select sum(vlprice) as vlprice, sum(vlwidth) as vlwidth,sum(vlheight) as vlheight,sum(vllength) as vllength, sum(vlweight) as vlweight, count(*) as nrqtd
            from tb_products a 
            inner join tb_cartsproducts b on a.idproduct = b.idproduct
            where b.idcart = :idcart and dtremoved is null;
        ", [
            ':idcart'=>$this->getidcart()
        ]);

        if(count($results) > 0){
            return $results[0];
        }else{
            return [];
        }
    }

    public function setFreight($nrzipcode){
        $nrzipcode = str_replace('-', '', $nrzipcode);
        $totals = $this->getProductsTotals();
        if($totals['nrqtd'] > 0){

            if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            if($totals['vllength'] < 16) $totals['vllength'] = 16;
            $qs = http_build_query([
                'nCdEmpresa'=>'',
                'sDsSenha'=>'',
                'nCdServico'=>'40010',
                'sCepOrigem'=>'09853120',
                'sCepDestino'=>$nrzipcode,
                'nVlPeso'=>$totals['vlweight'],
                'nCdFormato'=>'1',
                'nVlComprimento'=>$totals['vllength'],
                'nVlAltura'=>$totals['vlheight'],
                'nVlLargura'=>$totals['vlwidth'],
                'nVlDiametro'=>'0',
                'sCdMaoPropria'=>'S',
                'nVlValorDeclarado'=>$totals['vlprice'],
                'sCdAvisoRecebimento'=>'S',

            ]);
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
            $result = $xml->Servicos->cServico;

            if($result->MsgErro != ''){
                Cart::setMsgErro($result->MsgErro);
            }else{
                Cart::clearMsgErro();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);
            $this->save();
            return $result;
        }else{

        }

    }

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    public static function setMsgErro($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgErro()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR]: "";
        Cart::clearMsgErro();
        return $msg;
    }

    public static function clearMsgErro()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    public function updateFreight(){
        if($this->getdeszipcode() != ''){
            $this->setFreight($this->getdeszipcode());
        }
    }
    public function getValues(){
        $this->getCalculateTotal();
        return parent::getValues();
    }
    public function getCalculateTotal(){
        $totals = $this->getProductsTotals();
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
        $this->updateFreight();
    }



}
