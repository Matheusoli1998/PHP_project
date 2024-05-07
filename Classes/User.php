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
                

                if ($row['attempt'] == 0) { //The attempt field from the row is stored in $attempt.
                    //Audit_generator("login", "failed", "User account locked.", $this->email);
                    //check the associated array data in $row[attempt] if equal 0, than this account locked
                    throw new Exception("There is a problem logging in, please contact the system admin.", 401);
                }

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
                     //If the passwords do not match, the attempt is decremented by 1 and the $loginFlag is set to "pass".
                    $newAttempt = $row['attempt'] - 1;
                    $updateCmd = $dbCon->prepare("UPDATE users_tb SET attempt = ? WHERE email = ?");
                    $updateCmd->bind_param("is",$newAttempt,$this->email);
                    $updateCmd->execute();
                    $updateCmd->close();
                    //AUDIT generator
                    throw new Exception("Username/Password Wrong.", 401);
                }

            }else{

                $loginFlag = "email";
                if($loginFlag != "email"){

                    switch($loginFlag){
                        case "email":
                            //Audit_generator("login", "failed", "User email not found.", $this->email);
                            throw new Exception("Username/Password Wrong.",401);
                        break;
                        case "pass":
                            //Audit_generator("login", "failed", "Password incorrect.", $this->email);
                            throw new Exception("Username/Password Wrong.", 401);
                        break;

                        default:
                            //Audit_generator("login", "failed", "User email not found.", $this->email);
                            throw new Exception("No user found with that email address.", 404);
                        break;
                    }



                    //Audit_generator("login", "failed", "User email not found.", $this->email);
                    throw new Exception("No user found with that email address.", 404);
                }







                throw new Exception("No user found with that email address.", 404);
            }

            $dbCon->close();
            
        }

        function register($email,$username,$pass){
            $db_connection= new DB(DB_SERVER_NAME,DB_USER,DB_PASSWORD,DB_NAME);
            $dbCon=$db_connection->connect();
            $data = $dbCon->prepare("SELECT * FROM users_tb WHERE email = ?");
            $data->bind_param("s",$email);
            $data->execute();
            $result = $data->get_result();


            if($result->num_rows>0){
                $data->close();
                //Audit_generator("registeration","Failed","User eamil already exists!");// Log the event
                throw new Exception("Registration Failed: Email already exists", 406);
            }

            // Hash the password securely
            $hashedPass = password_hash($pass,PASSWORD_BCRYPT,["cost"=>10]);

            $insertCmd = $dbCon->prepare("INSERT INTO users_tb (username,pass,email) VALUES (?,?,?);");
            $insertCmd->bind_param("sss",$_POST['username'],$hashedPass, $_POST['email']);

     
            $insertCmd->execute();

            $insertCmd->close();
            $db_connection->db_close();
            
     



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