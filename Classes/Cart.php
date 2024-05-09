<?php
    class Cart{
        private $uid;

        function __construct($uid)
        {
            $this->uid = $uid;
        }

        public function getCartItems(){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $menuJoins = [
                "INNER JOIN users_tb ON users_tb.id = cart_tb.uid",
                "INNER JOIN menu_tb ON menu_tb.mid = cart_tb.mid",
            ];
            $sponsorJoins = [
                "INNER JOIN users_tb ON users_tb.id = cart_tb.uid",
                "INNER JOIN sponsor_tb ON sponsor_tb.sid = cart_tb.sid",
            ];
            $menu = $db_connexion->selectJoin('cart_tb','users_tb','id',$this->uid,$menuJoins,['cid','menu_tb.mid','menuName','menuPrice','amount']);
            $sponsors = $db_connexion->selectJoin('cart_tb','users_tb','id',$this->uid,$sponsorJoins,['cid','value','amount']);

            $products = [];
            if($menu){
                foreach($menu as $menu){
                    array_push($products,['id'=>$menu['cid'],'mid'=>$menu['mid'],'pname'=>$menu['menuName'], 'price'=>$menu['menuPrice'],'amount'=>$menu['amount']]);
                }
            }

            if($sponsors){
                foreach($sponsors as $sponsor){
                    array_push($products,['id'=>$sponsor['cid'],'sid'=>$sponsor['value'],'pname'=>'Sponsor Cat', 'price'=>$sponsor['value'],'amount'=>$sponsor['amount']]);
                }
            }
            
            if(count($products) > 0){
                http_response_code(200);
                print_r(json_encode($products));
            } else {
                throw new Exception('Nothing found', 404);
            }

            $db_connexion->db_close();
        }

        public function resetCart(){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $db_connexion->delete('cart_tb','uid',$this->uid);
            $db_connexion->db_close();
        }

        public function addCartItem($data){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $dbCon = $db_connexion->connect();

            if(array_key_exists('mid',$data)){
                $query = "SELECT * FROM cart_tb WHERE uid = $this->uid AND mid = ".$data['mid'];
                $result = $dbCon->query($query);

                if($result->num_rows > 0){
                    $result = $result->fetch_assoc();
                    $amount = $result['amount'] + 1;
                    $db_connexion->updateValue('cart_tb',$result['cid'],'cid','amount',$amount);
                    sendHttp_Code("Another ".$data['pname']." added to cart",200);
                } else {
                    $db_connexion->insert('cart_tb',[$this->uid,$data['mid'],$data['amount']],['uid','mid','amount']);
                    sendHttp_Code($data['pname']." added to cart",201);
                }
            } else {
                $sponsor = $db_connexion->select('sponsor_tb','value',$data['value']);
                if($sponsor !== null){
                    $sponsorId = $sponsor['sid'];
                    $query = "SELECT * FROM cart_tb WHERE uid = $this->uid AND sid = $sponsorId";
                    $result = $dbCon->query($query);
                    if($result->num_rows > 0){
                        $result = $result->fetch_assoc();
                        $amount = 0;
                        if($data['type'] === 'increment'){
                            $amount = $result['amount'] + 1;
                        } else {
                            $amount = $data['amount'];
                        }

                        $db_connexion->updateValue('cart_tb',$result['cid'],'cid','amount',$amount );
                        sendHttp_Code("Sponsor of $".$data['value']." updated successfully",200);
                    } else {
                        $db_connexion->insert('cart_tb',[$this->uid,$sponsorId,$data['amount']],['uid','sid','amount']);
                        sendHttp_Code($data['amount']." Sponsor of $".$data['value']." added to cart",201);
                    }
                }else{
                    $db_connexion->db_close();
                    throw new Exception('No sponsor with the value of '.$data['value'].'found',404);
                }

            }

            $db_connexion->db_close();
        }

        public function removeCartItem($cid){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $item = $db_connexion->select('cart_tb','cid',$cid);

            if($item && $item > 0){
                $db_connexion->delete('cart_tb','cid',$cid);
            }else{
                $db_connexion->db_close();
                throw new Exception('Unable to find item to remove',404);
            }

            $db_connexion->db_close();


        }

        public function updateCartItemQuantity($requestObj){
            $db_connexion = new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $db_connexion->connect();
            $item = $db_connexion->select('cart_tb','cid',$requestObj['cid']);
            if($item === null || count($item) === 0){
                $db_connexion->db_close();
                throw new Exception("Item not found",404);
            }

            $db_connexion->updateValue('cart_tb',$requestObj['cid'],'cid','amount',$requestObj['amount']);
            $db_connexion->db_close();
        }
    }

?>