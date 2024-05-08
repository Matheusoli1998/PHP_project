<?php
    class Menu {
        private $menuName;
        private $menuDescription;
        private $menuPrice;
        private $menuCategory;
        private $menuImage;
        private $id;

        function __construct($obj)
        {
            $this->menuName = $obj['menuName'];
            $this->menuDescription = $obj['menuDescription'];
            $this->menuPrice = floatval( $obj['menuPrice']);
            $this->menuCategory = $obj['menuCategory'];
        }

        static public function getMenuList(){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->selectAll('menu_tb');
            $db_connexion->db_close();
            http_response_code(200);
            print_r(json_encode($data));
        }

        static public function searchMenu($id){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->select('menu_tb','mid',$id);
            $db_connexion->db_close();

            if($data === null || count($data) === 0){
                throw new Exception('No product found with id provided', 404);
            } else{
                http_response_code(200);
                print_r(json_encode($data));
            }
        }

        static public function deleteProduct($id){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->select('menu_tb','mid',$id);
            
            if($data === null || count($data) === 0){
                throw new Exception('No product found with id provided', 404);
            }
            
            $db_connexion->delete('menu_tb','mid',$id);
            $db_connexion->db_close();
        }

        function addProductToDataBase($file){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            
            $this->menuImage = new FileUpload($file,MAX_FILE_SIZE,BACKEND_PICTURES_PATH);
            $this->menuImage = $this->menuImage->uploadFile();
            
            $db_connexion->insert(
                'menu_tb',
                [
                    $this->menuName,
                    $this->menuDescription,
                    $this->menuPrice,
                    $this->menuCategory,
                    $this->menuImage
                ], 
                ['menuName','menuDescription','menuPrice','menuCategory','menuImage']
            );
            $db_connexion->db_close();
            sendHttp_Code('Product Added to Database Successfully',201);
        }

        function editProduct($id,$file=null){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            
            $product = $db_connexion->select('menu_tb','mid',$id);
            if($product === null || count($product) === 0){
                throw new Exception("Product with mid $id not found",404);
            }

            $cols = [
                'menuName'=>$this->menuName,
                'menuDescription'=>$this->menuDescription,
                'menuPrice'=>$this->menuPrice,
                'menuCategory'=>$this->menuCategory
            ];
            
            if($file){
                $this->menuImage = new FileUpload($file,MAX_FILE_SIZE,BACKEND_PICTURES_PATH);
                $this->menuImage = $this->menuImage->uploadFile();
                $cols['menuImage'] = $this->menuImage;
            }
            
            $db_connexion->updateMultiple('menu_tb', $id,'mid',$cols);

            $db_connexion->db_close();
            sendHttp_Code('Product Edit Successfully',200);
        }

        
    }


?>