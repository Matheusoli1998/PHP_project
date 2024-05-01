<?php
    class User {
        private $username;
        private $email;
        private $type;
        private $id;
        private $cart;
        private $favorite;

        function __construct($username, $email)
        {
            $this->email = $email;
        }

        static public function login(){
            session_start();
            print_r(json_encode([
                "sid"=>session_id(),
                "username"=> "user",
                "type"=> "admin",
                "email"=>"admin@gmail.com",
                "id"=>1
            ]));
        }
    }
?>