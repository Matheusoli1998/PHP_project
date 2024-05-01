<?php
    require('./config.php');
    require('./Classes/DB.php');
    require('./Classes/Cats.php');
    require('./Classes/User.php');
    require('./Classes/Menu.php');
    require('./Functions.php');

    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Methods: PUT, GET, POST");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    
    try {
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                switch ($_SERVER['PATH_INFO']) {
                    case '/getCats':
                        // gets cats information from database
                        Cats::getCatsList();
                        break;
                    case '/getProducts':
                        Menu::getMenuList();
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
                        // throw path doesn't exist
                        break;
                }
                break;
            case "POST":
                switch ($_SERVER['PATH_INFO']) {
                    case '/login':
                        User::login();
                        // print_r($_POST['email']);
                        // login if exist (check email and password)
                        // keys: $email, $pass
                        break;
                    case '/register':
                        // register user (name, hash password, default type customer)
                        // keys: $email, $pass, $username
                        break;
                    case '/addCat':
                        // add new cat to database
                        // keys: $catImage, $catName, $catBreed, $catAge, $catDescription, $adoptionStatus default Available, $favorite(boolean)
                        break;
                    case '/addProduct':
                        // add new product to database
                        // keys: $img, $name, $description, $value
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
                        // throw new Exception('ON PATH',400);
                        $dbObj->importUsersJson();
                        $dbObj->db_close();
                        break;
                    default:
                    // throw path doesn't exist
                }
            break;
            case "PATCH":
                switch ($_SERVER['PATH_INFO']) {
                    case '/editCat':
                        // edit cat in database
                        // keys: cat $newData with cat $id
                        break;
                    case '/editProduct':
                        // edit product in database
                        // keys: product $newData with product $id
                        break;
                    case '/favorite':
                        // edit user favorite whishlist
                        // keys: cat$id user$id
                        break;
                    default:
                        // throw path doesn't exist
                }
            break;
            case "DELETE":
                switch ($_SERVER['PATH_INFO']) {
                    case '/removeCat':
                        // remove cat from database
                        // keys: product $id
                        break;
                    case '/removeProduct':
                        // remove product from database
                        // keys: product $id
                        break;
                    default:
                        // throw path doesn't exist
                }
            break;
            default:
            // throw method not allowed
        }
    } catch(Exception $err){
        sendHttp_Code($err->getMessage(),$err->getCode());
    }
?>