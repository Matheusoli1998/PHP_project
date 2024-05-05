<?php
    require('./config.php');
    require('./Classes/DB.php');
    require('./Classes/Cats.php');
    require('./Classes/User.php');
    require('./Classes/Menu.php');
    require('./Classes/File.php');
    require('./Functions.php');

    header("Access-Control-Allow-Origin: http://localhost:3000");
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    
    try {
        if(!isset($_SERVER['PATH_INFO'])){
            throw new Exception("No path found", 404);
        } 
        
        switch($_SERVER["REQUEST_METHOD"]){
            case "OPTIONS":
                http_response_code(204);
            break;
            case "GET":
                switch ($_SERVER['PATH_INFO']) {
                    case '/getCats':
                        // get all cats from cats_tb
                        Cats::getCatsList();
                        break;
                    case '/getProducts':
                        // get all products from menu_tb
                        Menu::getMenuList();
                        break;
                    case '/searchProduct':
                        check_key(['id'],$_GET);
                        Menu::searchMenu($_GET['id']);
                        break;
                    case '/searchCat':
                        check_key(['id'],$_GET);
                        Cats::searchCat($_GET['id']);
                        break;
                    case '/whishlist':
                        // gets user wishlist
                        // keys: user id
                        break;
                    case '/cart':
                        // gets user cart
                        // keys: user id
                        break;
                    default:
                        throw new Exception("No path found", 404);
                }
            
                break;
            case "POST":
                switch ($_SERVER['PATH_INFO']) {
                    case '/login':
                        // User::login();
                        // print_r($_POST['email']);
                        // login if exist (check email and password)
                        // keys: $email, $pass
                        check_key(["email", "pass"],$_POST);
                        $userObj = new User($_POST["email"]);
                        // keys: $email, $pass
                        echo $userObj->login($_POST["pass"]);
                        
                        break;
                    case '/register':
                        // register user (name, hash password, default type customer)
                        // keys: $email, $pass, $username
                        break;
                    case '/addCat':
                        // check if all the information to create a cats object was sent
                        check_key(['catName','cataAge','catBreed','catDescription','adoptionStatus'],$_POST);
                        check_key(['catImage'],$_FILES);

                        // creates Cats object with all the keys sent on request
                        $cat = new Cats($_POST);

                        // send cats object to database
                        $cat->addCatToDataBase($_FILES['catImage']);
                        break;
                    case '/addProduct':
                         // check if all the information to create a menu object was sent
                         check_key(['menuName','menuPrice','menuCategory','menuDescription'],$_POST);
                         check_Key(['menuImage'],$_FILES);
                        
                         // creates menu object with all the keys sent on request
                        $product = new Menu($_POST);

                        // send menu object to database
                        $product->addProductToDataBase($_FILES['menuImage']);
                        break;
                    case '/editProduct':
                        // check if all the information to update object was sent
                        check_key(['mid','menuName','menuPrice','menuCategory','menuDescription'],$_POST);

                        // creates menu object with all the keys sent on request
                        $product = new Menu($_POST);
                        
                        // check if file was sent on the request and if the file data is empty
                        $file = $_FILES['menuImage'];
                        $file = $file['name'] === '' && $file['size'] === 0 ? null : $file;

                        // send edit request to database
                        $product->editProduct($_POST['mid'], $file);
                        break;
                    case '/editCat':
                        // check if all the information to update object was sent
                        check_key(['cid','catName','cataAge','catBreed','catDescription','adoptionStatus'],$_POST);

                        // creates menu object with all the keys sent on request
                        $cat = new Cats($_POST);
                        
                        // check if file was sent on the request and if the file data is empty
                        $file = $_FILES['catImage'];
                        $file = $file['name'] === '' && $file['size'] === 0 ? null : $file;

                        // send edit request to database
                        $cat->editCat($_POST['cid'], $file);
                        break;
                    case '/saveCart':
                        // save user cart to database
                        // keys: array of products
                        break;
                    case '/importCats':
                        $dbObj = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
                        $dbObj->connect();
                        $dbObj->importCatsJson();
                        $dbObj->db_close();
                        break;
                    case '/importMenu':
                        $dbObj = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
                        $dbObj->connect();
                        $dbObj->importMenuJson();
                        $dbObj->db_close();
                        break;
                    case '/importUsers':
                        $dbObj = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
                        $dbObj->connect();
                        $dbObj->importUsersJson();
                        $dbObj->db_close();
                        break;
                    default:
                        throw new Exception("No path found", 404);
                }
            break;
            case "DELETE":
                switch ($_SERVER['PATH_INFO']) {
                    case '/removeCat':
                        check_key(['id'],$_REQUEST);
                        Cats::deleteCat($_REQUEST['id']);
                        sendHttp_Code('Cat Deleted Successfully',200);
                        break;
                    case '/removeProduct':
                        check_key(['id'],$_REQUEST);
                        Menu::deleteProduct($_REQUEST['id']);
                        sendHttp_Code('Product Deleted Successfully',200);
                    break;
                    default:
                        throw new Exception("No path found", 404);
                }
            break;
            default:
                throw new Exception("Method not allowed", 405);
        }
    } catch(Exception $err){
        sendHttp_Code($err->getMessage(),$err->getCode());
    }
?>