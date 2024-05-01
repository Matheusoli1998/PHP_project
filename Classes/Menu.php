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
            $this->menuName = $obj->menuName;
            $this->menuDescription = $obj->menuDescription;
            $this->menuPrice = $obj->menuPrice;
            $this->menuCategory = $obj->menuCategory;
            $this->menuImage = $obj->menuImage;
        }

        function addMenu(){

        }

        function editMenu(){

        }

        function removeMenu(){

        }

        static public function getMenuList(){
            $menuAddr = './json/menu.json';
            $file = fopen($menuAddr,"r");
            $data = fread($file,filesize($menuAddr));
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Credentials: true');
            // sendHttp_Code('Data sent successfully',200);
            http_response_code(200);
            print_r($data);
        }
    }


?>