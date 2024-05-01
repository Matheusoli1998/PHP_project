<?php
    class Cats {
        private $catName;
        private $catAge;
        private $catBreed;
        private $catDescription;
        private $adoptionStatus;
        private $catImage;
        private $favorite;
        private $id;

        function __construct($obj)
        {
            $this->catName = $obj->catName;
            $this->catAge = $obj->catAge;
            $this->catBreed = $obj->catBreed;
            $this->catDescription = $obj->catDescription;
            $this->catImage = $obj->catImage;
            $this->adoptionStatus = "Available";
        }

        static public function getCatsList(){
            $catsAddr = './json/cats.json';
            $file = fopen($catsAddr,"r");
            $data = fread($file,filesize($catsAddr));
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Credentials: true');
            // sendHttp_Code('Data sent successfully',200);
            http_response_code(200);
            print_r($data);
        }

    }
?>