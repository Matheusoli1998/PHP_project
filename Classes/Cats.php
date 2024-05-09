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
        private $uid;

        function __construct($obj)
        {
            $this->catName = $obj['catName'];
            $this->catAge = $obj['cataAge'];
            $this->catBreed = $obj['catBreed'];
            $this->catDescription = $obj['catDescription'];
            $this->adoptionStatus = $obj['adoptionStatus'];
            $this->uid = $obj['uid'];
        }

        static public function getCatsList(){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $data = $db_connexion->selectAll('cats_tb');
            $db_connexion->db_close();
            http_response_code(200);
            print_r(json_encode($data));
        }


        static public function getWishListItems($uid){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $wishlistJoins = [
                "INNER JOIN users_tb ON users_tb.id =wishlist_tb.uid",
                "INNER JOIN cats_tb ON cats_tb.cid = wishlist_tb.cid",
            ];

            // $wishlistData = ["  
            //                     SELECT cats_tb.cid, wid, uid, catName, cataAge,catBreed, catDescription,catImage, adoptionStatus 
            //                     FROM cats_tb 
            //                     INNER Join wishlist_tb on cats_tb.cid = wishlist_tb.cid 
            //                     INNER Join users_tb on wishlist_tb.uid = users_tb.id 
            //                     WHERE users_tb.id = wishlist_tb.uid
            //                     "];
      
            $wishlist = $db_connexion->selectJoin('wishlist_tb','users_tb','id',$uid,$wishlistJoins,['wid','cats_tb.cid as cid','users_tb.id as uid']);
            //throw new Exception(count($wishlist), 404);

            // $getwishlistData = $db_connexion->selectJoin('cats_tb','wishlist_tb','cid',$uid,$wishlistData,['cid','wid','uid','catName','cataAge','catBreed','catDescription','catImage','adoptionStatus']);



            $products = [];
            // $getwishlistDetails = [];
      
      
            if($wishlist){
                foreach($wishlist as $list){
                    array_push($products,['wid'=>$list['wid'],'uid'=>$list['uid'],'cid'=>$list['cid']]);
                }
            }

            // if($getwishlistData){
            //     foreach($getwishlistData as $list){
            //         array_push($getwishlistDetails,['wid'=>$list['wid'],'uid'=>$list['uid'],'cid'=>$list['cid'],'catName'=>$list['catName'],'cataAge'=>$list['cataAge'],'catBreed'=>$list['catBreed'],'catDescription'=>$list['catDescription'],'catImage'=>$list['catImage'],'adoptionStatus'=>$list['adoptionStatus']]);
            //     }
            // }

            //throw new Exception(json_encode($wishlist), 404);
            if(count($products) > 0){
                http_response_code(200);
                print_r(json_encode($products));
            } else {

               // throw new Exception('Nothing found', 404);
            }


            // if(count($getwishlistDetails) > 0){
            //     http_response_code(200);
            //     print_r(json_encode($products));
            // } else {

            //    throw new Exception('Nothing found', 404);
            // }

            $db_connexion->db_close();
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

        static public function updateWishListItems($post){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
         
            $dbCon=$db_connexion->connect();
          
            $query = "SELECT * FROM wishlist_tb WHERE uid = ".$post['uid']." AND cid = ".$post['cid'];
            $result = $dbCon->query($query);
            if($result === null || $result->num_rows === 0){


                $db_connexion->insert('wishlist_tb',[$post['uid'],$post['cid']],['uid','cid']);
                
                 sendHttp_Code('Cat Added to wishlist',200);
                
                
                //throw new Exception('No item found with id provided', 404);
            }else{
                $db_connexion->delete('wishlist_tb','wid',$post['wid']);
                sendHttp_Code('Cat remove from wishlist',200);
            }

            $db_connexion->db_close();
            // sendHttp_Code('Cat Added to database Successfully',200);


        }

        static public function deleteCat($id){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $dbCon = $db_connexion->connect();
            $data = $db_connexion->select('cats_tb','cid',$id);
            
            if($data === null || count($data) === 0){
                throw new Exception('No cat found with id provided', 404);
            }

            // check if any wishlist has the cat to be removed
            $query = "SELECT * FROM wishlist_tb WHERE cid = $id";
            $wishlist = $dbCon->query($query);
            
            if($wishlist && $wishlist->num_rows > 0){
                $items = $wishlist->fetch_all(MYSQLI_ASSOC);
                
                // remove wishlist entry that have the product
                foreach ($items as $item) {
                    $db_connexion->delete('wishlist_tb','cid',$item['cid']);
                }
            }

            // remove cat
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