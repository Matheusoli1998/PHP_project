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

        static public function searchCat($id){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->select('cats_tb','cid',$id);
            $db_connexion->db_close();

            if($data === null || count($data) === 0){
                throw new Exception('No cat found with id provided', 404);
            } else{
                http_response_code(200);
                print_r(json_encode($data));
            }
        }

        static public function deleteCat($id){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->select('cats_tb','cid',$id);
            
            if($data === null || count($data) === 0){
                throw new Exception('No cat found with id provided', 404);
            }
            
            $db_connexion->delete('cats_tb','cid',$id);
            $db_connexion->db_close();
        }

        function addCatToDataBase($file){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            
            $this->catImage = new FileUpload($file,MAX_FILE_SIZE,BACKEND_PICTURES_PATH);
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

        function editCat($id,$file=null){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            
            $cat = $db_connexion->select('cats_tb','cid',$id);
            if($cat === null || count($cat) === 0){
                throw new Exception("Cat with cid $id not found",404);
            }

            $cols = [
                'catName'=>$this->catName,
                'cataAge'=>$this->catAge,
                'catBreed'=>$this->catBreed,
                'catDescription'=>$this->catDescription,
                'adoptionStatus'=>$this->adoptionStatus

            ];
            
            if($file){
                $this->catImage = new FileUpload($file,MAX_FILE_SIZE,BACKEND_PICTURES_PATH);
                $this->catImage = $this->catImage->uploadFile();
                $cols['catImage'] = $this->catImage;
            }
            
            $db_connexion->updateMultiple('cats_tb', $id,'cid',$cols);

            $db_connexion->db_close();
            sendHttp_Code('Cat Edit Successfully',200);
        }

    }
?>