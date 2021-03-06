<?php
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;

function formatPrice(float $vlprice)
{
    return number_format($vlprice, 2, ",", ".");
}

function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

function getUserName()
{
    $user = User::getFromSession();
    return $user->getdesperson();
}

$app = new Slim();

$app->config('debug', true);

// ================ rotas do site =======================
$app->get('/', function () {
    $product = Product::listAll();
    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($product)
    ]);
});

$app->get('/categories/:idcategory', function ($idcategory) {
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $category = new Category();
    $category->get((int)$idcategory);
    $pagination = $category->getProductPage($page);
    $pages = [];
    for ($i = 1; $i <= $pagination['pages']; $i++) {
        array_push($pages, [
            'link' => '/categories/' . $category->getidcategory() . '?page=' . $i,
            'page' => $i
        ]);
    }
    $page = new Page();
    $page->setTpl("category", [
        "category" => $category->getValues(),
        "products" => $pagination["data"],
        "pages" => $pages
    ]);
});

// ================ rotas do Administrador =======================

$app->get('/admin', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
});

$app->get('/admin/login', function () {
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("login");
});

$app->post('/admin/login', function () {
    User::login($_POST['login'], $_POST['password']);
    header("Location: /admin");
    exit();
});

$app->get('/admin/logout', function () {
    User::logout();
    header("Location: /admin/login");
    exit();
});

$app->get('/admin/users', function () {
    User::verifyLogin();
    $users = User::listAll();
    $page = new PageAdmin();
    $page->setTpl("users", array(
        "users" => $users
    ));
});

$app->get('/admin/users/create', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("users-create");
});

$app->get('/admin/users/:iduser/delete', function ($iduser) {
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $user->delete();
    header("Location: /admin/users");
    exit;
});


$app->get('/admin/users/:iduser', function ($iduser) {
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user" => $user->getValues()
    ));
});

$app->post('/admin/users/create', function () {
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $user->setData($_POST);
    $user->save();
    header("Location: /admin/users");
    exit();
});

$app->post('/admin/users/:iduser', function ($iduser) {
    User::verifyLogin();
    $user = new User();
    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();
    header("Location: /admin/users");
    exit();
});

$app->get('/admin/forgot', function () {
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot");
});

$app->post('/admin/forgot', function () {

    $user = User::getForgot($_POST['email']);

    header("Location: /admin/forgot/sent");
    exit;
});

$app->get('/admin/forgot/sent', function () {
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-sent");
});


$app->get('/admin/forgot/reset', function () {
    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));
});

$app->post('/admin/forgot/reset', function () {
    $forgot = User::validForgotDecrypt($_POST["code"]);
    User::setForgotDecrypt($forgot["idrecovery"]);
    $user = new User();
    $user->get((int)$forgot["iduser"]);
    $user->setPassword($_POST["password"]);
    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset-success");
});

// =================== Rotas de Categorias =======================

$app->get('/admin/categories', function () {
    User::verifyLogin();
    $categories = Category::listAll();
    $page = new PageAdmin();
    $page->setTpl("categories", array(
        "categories" => $categories
    ));
});

$app->get('/admin/categories/create', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("categories-create");
});

$app->post('/admin/categories/create', function () {
    User::verifyLogin();
    $category = new Category();
    $category->setData($_POST);
    $category->save();
    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory/delete', function ($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->delete();
    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory', function ($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new PageAdmin();
    $page->setTpl("categories-update", [
        "category" => $category->getValues()
    ]);
});

$app->post('/admin/categories/:idcategory', function ($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();
    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory/products', function ($idcategory) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $page = new PageAdmin();
    $page->setTpl("categories-products", [
        "category" => $category->getValues(),
        "productsRelated" => $category->getProducts(),
        "productsNotRelated" => $category->getProducts(false)
    ]);
});

$app->get('/admin/categories/:idcategory/products/:idproduct/add', function ($idcategory, $idproduct) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->addProduct($product);
    header("Location: /admin/categories/" . $idcategory . "/products");
    exit;
});
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function ($idcategory, $idproduct) {
    User::verifyLogin();
    $category = new Category();
    $category->get((int)$idcategory);
    $product = new Product();
    $product->get((int)$idproduct);
    $category->removeProduct($product);
    header("Location: /admin/categories/" . $idcategory . "/products");
    exit;
});

// ================== Rotas de Produtos =======================

$app->get('/admin/products', function () {
    User::verifyLogin();
    $products = Product::listAll();
    $page = new PageAdmin();
    $page->setTpl("products", [
        "products" => $products
    ]);
});

$app->get('/admin/products/create', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("products-create");
});

$app->post('/admin/products/create', function () {
    User::verifyLogin();
    $product = new Product();
    $product->setData($_POST);
    $product->save();
    header("Location: /admin/products");
    exit;
});

$app->get('/admin/products/:idproduct', function ($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $page = new PageAdmin();
    $page->setTpl("products-update", [
        "product" => $product->getValues()
    ]);
});

$app->post('/admin/products/:idproduct', function ($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $product->setData($_POST);
    $product->save();
    $product->setPhoto($_FILES["file"]);
    header("Location: /admin/products");
    exit;
});
$app->get('/admin/products/:idproduct/delete', function ($idproduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get((int)$idproduct);
    $product->delete();
    header("Location: /admin/products");
    exit;
});

// rotas de produtos sem admin
$app->get('/products/:desurl', function ($desurl) {
    $product = new Product();
    $product->getFromUrl($desurl);

    $page = new Page();
    $page->setTpl("product-detail", [
        "product" => $product->getValues(),
        "categories" => $product->getCategories()
    ]);
});

$app->get('/cart', function () {
    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl("cart", [
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts(),
        'error' => Cart::getMsgErro()
    ]);
});

$app->get('/cart/:idproduct/add', function ($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
    for ($i = 0; $i < $qtd; $i++) {
        $cart->addProduct($product);
    }

    header('Location: /cart');
    exit;
});

$app->get('/cart/:idproduct/minus', function ($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product);
    header('Location: /cart');
    exit;
});

$app->get('/cart/:idproduct/remove', function ($idproduct) {
    $product = new Product();
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);
    header('Location: /cart');
    exit;
});

$app->post('/cart/freight', function () {
    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['zipcode']);
    header('Location: /cart');
    exit;
});
$app->get('/checkout', function () {
    User::verifyLogin(false);
    $cart = Cart::getFromSession();
    $address = new Address();
    $page = new Page();
    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues()
    ]);
});

$app->get('/login', function () {
    $page = new Page();
    $page->setTpl("login", [
        'error' => User::getError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name' => '', 'email' => '', 'phone' => '']
    ]);
});

$app->post('/login', function () {

    try {
        User::login($_POST['login'], $_POST['password']);
    } catch (Exception $e) {
        User::setError($e->getMessage());
    }

    header('Location: /checkout');
    exit;
});

$app->get('/logout', function () {
    User::logout();
    header('Location: /login');
    exit;
});

$app->post('/register', function () {
    $_SESSION['registerValues'] = $_POST;

    foreach ($_SESSION['registerValues'] as $key => $value) {
        if (!isset($key) || $key == '') {
            User::setErrorRegister('Preencha todos os campos');
            header('Location: /login');
            exit;
        }
    }
    if (User::checkLoginExist($_POST['email']) === true) {
        User::setErrorRegister('Este endereço de email já está sendo usado');
        header('Location: /login');
        exit;
    }

    $user = new User();
    $user->setData([
        'inadmin' => 0,
        'deslogin' => $_POST['email'],
        'desperson' => $_POST['name'],
        'desemail' => $_POST['email'],
        'despassword' => $_POST['password'],
        'nrphone' => $_POST['phone']
    ]);
    $user->save();
    User::login($_POST['email'], $_POST['password']);
    header('Location: /profile');
    exit;
});


$app->get('/forgot', function () {
    $page = new Page();
    $page->setTpl("forgot");
});

$app->post('/forgot', function () {

    $user = User::getForgot($_POST['email'], false);

    header("Location: /admin/forgot/sent");
    exit;
});

$app->get('/forgot/sent', function () {
    $page = new Page();
    $page->setTpl("forgot-sent");
});


$app->get('/forgot/reset', function () {
    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new Page();
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));
});

$app->post('/forgot/reset', function () {
    $forgot = User::validForgotDecrypt($_POST["code"]);
    User::setForgotDecrypt($forgot["idrecovery"]);
    $user = new User();
    $user->get((int)$forgot["iduser"]);
    $user->setPassword($_POST["password"]);
    $page = new Page();
    $page->setTpl("forgot-reset-success");
});

$app->get('/profile', function () {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl("profile", array(
        'user'=>$user->getValues(),
        'profileMsg'=>User::getSuccess(),
        'profileError'=>User::getError()
    ));
});

$app->post('/profile', function () {
    User::verifyLogin(false);
    $user = User::getFromSession();
    if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){
        User::setError('Preencha o seu nome');
        header('Location: /profile');
        exit;
    }

    if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){
        User::setError('Preencha o seu email');
        header('Location: /profile');
        exit;
    }

    if($_POST['desemail'] !== $user->getdesemail()){
        if(User::checkLoginExist($_POST['desemail'])){
            User::setError('Este endereço de email ja esta cadastrado');
            header('Location: /profile');
            exit;
        }
    }

    $_POST['inadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];
    $_POST['iduser'] = $user->getiduser();
    $user->setData($_POST);
    $user->update();
    User::setSuccess('Dados modificados com sucesso');
    header('Location: /profile');
    exit;
});

$app->run();

?>