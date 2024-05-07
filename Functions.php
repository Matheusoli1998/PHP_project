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
            throw new Exception("Session timed out/does not exist. Login again",440);
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
        // Compose audit log entry with timestamp, IP, port, user email, event type, outcome, and description
        $aduit = date("Y-m-d H:i:s ",$_SERVER["REQUEST_TIME"]).$_SERVER["REMOTE_ADDR"].":".$_SERVER["REMOTE_PORT"]." $userEmail $eventType $outcome $desc \n";
        $file = new File("./data/audit");
        $file->writeFile("Audit ".date("Ymd").".txt",$aduit); // Write the audit entry to a file
    }
?>