<?php
    class User {
        private $username;
        private $email;
        private $type;
        private $id;

        private $cart;
        private $favorite;

        function __construct($email){
            $this->email = $email;
        }

        public function getType()
        {
            return $this->type;
        }

        function login($pass){
            $db_connection= new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $dbCon=$db_connection->connect();
            $data = $dbCon->prepare("SELECT * FROM users_tb WHERE email = ?");
            $data->bind_param("s",$this->email);
            $data->execute();

            $result = $data->get_result();

            if($result->num_rows > 0){
                $row = $result->fetch_assoc(); // user data

                if(password_verify($pass,$row['pass'])){ //check pwd
                    $this->setupUserSession($row);
                    echo json_encode(
                        [
                            "sid" => session_id(),
                            "username" => $this->username,
                            "type" => $this->type,
                            "email" => $this->email,
                            "id" => $this->id,
                            "type" => $this->type,
                        ]
                        );

                }else{
                    throw new Exception("Invalid credentials provided.", 400);
                }

            }else{
                throw new Exception("No user found with that email address.", 404);
            }

            $dbCon->close();
        }

        private function setupUserSession($row) {
            $this->email = $row['email'];
            $this->username = $row['username'];
            $this->type = $row['type'];
            $this->id = $row['id'];
            session_start();
            $_SESSION["login_user"] = $this;
            $_SESSION["time_out"] = time() + TIME_OUT; 
        }

    }
?>