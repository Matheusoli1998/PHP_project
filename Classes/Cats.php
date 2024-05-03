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
            $this->catName = $obj['catName'];
            $this->catAge = $obj['cataAge'];
            $this->catBreed = $obj['catBreed'];
            $this->catDescription = $obj['catDescription'];
            $this->adoptionStatus = $obj['adoptionStatus'];
        }

        static public function getCatsList(){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->selectAll('cats_tb');
            $db_connexion->db_close();
            http_response_code(200);
            print_r(json_encode($data));
        }

        function addCatToDataBase($file){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            
            $this->catImage = new File($file,MAX_FILE_SIZE,BACKEND_PICTURES_PATH);
            $this->catImage = $this->catImage->uploadFile();
            $db_connexion->insert(
                'cats_tb',
                [
                    $this->catName,
                    $this->catAge,
                    $this->catBreed,
                    $this->catDescription,
                    $this->catImage,
                    $this->adoptionStatus
                ], 
                ['catName','cataAge','catBreed','catDescription','catImage','adoptionStatus']
            );
            $db_connexion->db_close();
            sendHttp_Code('Cat Added to database Successfully',201);
        }

    }
?>