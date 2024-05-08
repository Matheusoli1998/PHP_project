<?php
    require('./config.php');
    require('./Classes/DB.php');
    require('./Classes/Cats.php');
    require('./Classes/User.php');
    require('./Classes/Menu.php');
    require('./Classes/File.php');
    require('./Classes/Cart.php');
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
                        check_key(['uid'], $_GET);
                        getUserCredentials($_GET);
                        $cart = new Cart($_GET['uid']);
                        $cart->getCartItems();
                        break;
                    default:
                        throw new Exception("No path found", 404);
                }
            
                break;
            case "POST":
                switch ($_SERVER['PATH_INFO']) {
                    case '/login':
                        // login if exist (check email and password)
                        // keys: $email, $pass
                        check_key(["email", "pass"],$_POST);
                        $userObj = new User($_POST["email"]);
                        // keys: $email, $pass
                        echo $userObj->login($_POST["pass"]);
                        
                        break;
                    case '/register':
                        // register user (name, hash password, default type customer)
                        check_key(["username","email", "pass"],$_POST);
                        
                        //Validate user input//
                        // username validation
                        if(strlen($_POST["username"]) < 3 || strlen($_POST["username"]) > 20){
                            throw new Exception("Username must be between 3 and 20 characters",400);
                        }

                        // email validation check the user email input in email format
                        // if the email is not in email format, throw an exception
                        if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)){
                            //print_r("Invalid email format");
                            throw new Exception("Invalid email format",400);
                        }

                        // password validation
                        // check if the password is less than 8 characters
                        if(strlen($_POST["pass"]) < 6){
                            throw new Exception("Password must be at least 6 characters",400);
                        }

                        $userObj = new User($_POST["email"]); 
                        echo $userObj->register($_POST["email"],$_POST["username"],$_POST["pass"]);
                        // keys: $email, $pass, $username

                        break;
                    case '/audit':
                        // get audit log
                        // keys: date
                        if (session_status() === PHP_SESSION_NONE) throw new Exception("Forbiden request.", 401);
                        //header("Content-Type: application/json");
        
                        $fileRoute = "./audit";
                        // If a specific date is requested, return the audit log for that date
                        if (isset($_POST["date"])){
                            $date = $_POST["date"];
                            print_r( Audit_parseJson($date));
                        }else{
                            // Otherwise, list all available audit files
                            $allFiles =listFolderFiles($fileRoute);
        
                            print_r($allFiles);
                        }
    
                        
                        
                        
                        break;
                    case '/addCat':
                        $userCredentials = getUserCredentials($_POST);
                            if($userCredentials === null || $userCredentials !== 'admin'){
                                throw new Exception("Unauthorized",401);

                            } else {
                            // check if all the information to create a cats object was sent
                            check_key(['catName','cataAge','catBreed','catDescription','adoptionStatus'],$_POST);
                            check_key(['catImage'],$_FILES);

                            // creates Cats object with all the keys sent on request
                            $cat = new Cats($_POST);

                            // send cats object to database
                            $cat->addCatToDataBase($_FILES['catImage']);
                        }
                        break;
                    case '/addProduct':
                        $userCredentials = getUserCredentials($_POST);
                        if($userCredentials === null || $userCredentials !== 'admin'){
                            throw new Exception("Unauthorized",401);

                        } else {
                            // check if all the information to create a menu object was sent
                            check_key(['menuName','menuPrice','menuCategory','menuDescription'],$_POST);
                            check_Key(['menuImage'],$_FILES);
                            
                            // creates menu object with all the keys sent on request
                            $product = new Menu($_POST);

                            // send menu object to database
                            $product->addProductToDataBase($_FILES['menuImage']);
                        }
                        break;
                    case '/addCartItem':
                        check_key(['uid'], $_POST);
                        getUserCredentials($_POST);
                        $cart = new Cart($_POST['uid']);
                        $cart->addCartItem($_POST);
                        break;
                    case '/editProduct':
                        $userCredentials = getUserCredentials($_POST);
                        if($userCredentials === null || $userCredentials !== 'admin'){
                            throw new Exception("Unauthorized",401);

                        } else {
                            // check if all the information to update object was sent
                            check_key(['mid','menuName','menuPrice','menuCategory','menuDescription'],$_POST);

                            // creates menu object with all the keys sent on request
                            $product = new Menu($_POST);
                            
                            // check if file was sent on the request and if the file data is empty
                            $file = $_FILES['menuImage'];
                            $file = $file['name'] === '' && $file['size'] === 0 ? null : $file;

                            // send edit request to database
                            $product->editProduct($_POST['mid'], $file);
                        }
                        break;
                    case '/editCat':
                        $userCredentials = getUserCredentials($_POST);
                        if($userCredentials === null || $userCredentials !== 'admin'){
                            throw new Exception("Unauthorized",401);

                        } else {
                            // check if all the information to update object was sent
                            check_key(['cid','catName','cataAge','catBreed','catDescription','adoptionStatus'],$_POST);
    
                            // creates menu object with all the keys sent on request
                            $cat = new Cats($_POST);
                            
                            // check if file was sent on the request and if the file data is empty
                            $file = $_FILES['catImage'];
                            $file = $file['name'] === '' && $file['size'] === 0 ? null : $file;
    
                            // send edit request to database
                            $cat->editCat($_POST['cid'], $file);
                        }
                        break;
                    case '/changeQuantity':

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
                        $userCredentials = getUserCredentials($_REQUEST);
                        if($userCredentials === null || $userCredentials !== 'admin'){
                            throw new Exception("Unauthorized",401);

                        } else {
                            check_key(['id'],$_REQUEST);
                            Cats::deleteCat($_REQUEST['id']);
                            sendHttp_Code('Cat Deleted Successfully',200);
                        }

                        break;
                    case '/removeProduct':
                        $userCredentials = getUserCredentials($_REQUEST);
                        if($userCredentials === null || $userCredentials !== 'admin'){
                            throw new Exception("Unauthorized",401);
                            
                        } else {
                            check_key(['id'],$_REQUEST);
                            Menu::deleteProduct($_REQUEST['id']);
                            sendHttp_Code('Product Deleted Successfully',200);
                        }
                        
                        break;
                    case '/resetCart':
                        getUserCredentials($_REQUEST);
                        check_key(['uid'],$_REQUEST);
                        $cart = new Cart($_REQUEST['uid']);
                        $cart->resetCart();
                        sendHttp_Code('Cart reset successfull',200);
                        break;
                    case '/removeCartItem':
                        getUserCredentials($_REQUEST);
                        check_key(['uid','cid'],$_REQUEST);
                        $cart = new Cart($_REQUEST['uid']);
                        $cart->removeCartItem($_REQUEST['cid']);
                        sendHttp_Code('Item removed successfully',200);
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