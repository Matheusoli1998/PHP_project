<?php

    function check_key($keys,$sourceData){
        foreach($keys as $key){
            if(!array_key_exists($key,$sourceData)){
                throw new Exception("Missing information",400);
            }
        }
    }

    function sendHttp_Code($msg,$code,$die_flag = false){
        // send http code and message as response
        http_response_code($code);
        if($die_flag){
            die($msg);
        }else{
            echo($msg);
        }
    }

    function getDataType($data){
        $dataType = "";

        foreach($data as $value){
            $dataType = $dataType.gettype($value)[0];
        };

        return $dataType;
    }

    function Session_Hanlder($sid){
        session_id($sid);
        session_start();
        if(isset($_SESSION["time_out"]) && $_SESSION["time_out"] > time()){
            $_SESSION["time_out"] = time() + TIME_OUT;
        }else{
            session_unset();
            session_destroy();
            // throw new Exception("Session timed out/does not exist. Login again",401);
            throw new Exception("Your session has expired. Please login again",401);
        }
    }

    function getUserCredentials($request){
        if(isset($request["sid"])){
            Session_Hanlder($request["sid"]);
        }else{
            throw new Exception("You need to login to perform this action", 401);
        }
        $user = $_SESSION["login_user"];
        return $user->getType();

    }

    function generateAudit($eventType,$outcome,$desc,$userEmail=null){
        
    }
?>