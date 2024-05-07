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

    // Parse the audit log from a given date, returning it in JSON format
    function Audit_parseJson($date){
        $fileName = "Audit $date.txt"; 
        $file = new File("./data/audit");
        $auditTxt = $file->readFile($fileName);
        // Split the text into lines
        $lines = explode("\n", $auditTxt);// Split the file content into lines
        $audits=[];

        // Parse each line into an array of values
        foreach($lines as $line){
            $line = explode(" ", $line);
            $date = isset($line[0])?$line[0] : '';
            $time = isset($line[1])?$line[1] : '';
            $REMOTE_ADDR = isset($line[2]) ? $line[2] : '';
            $user_email = isset($line[3]) ? $line[3] : '';
            $type = isset($line[4]) ? $line[4] : '';
            $outcome = isset($line[5]) ? $line[5] : '';
            $desc = array_slice($line, 6); // Get the rest of the line as the description

            $desc = implode(" ", array_slice($line, 6)); /// Combine the description array back into a string


            array_push($audits,['Date'=> $date, 'time'=> $time,'Remote_Addr'=>$REMOTE_ADDR, 'User_Email'=>$user_email, 'Type'=>$type, 'Outcome'=>$outcome, 'Desc'=>$desc]);
        }

        // // Convert the audits to JSON
        $auditJson = json_encode($audits);
        // $auditJson = json_decode($auditJson);

        return  $auditJson;/// Convert the parsed audit data into JSON format and return it
    }

        // List all files in a given directory
        function listFolderFiles($fileRoute){
            $files = scandir($fileRoute); // Scan the directory
            $files = array_diff($files, array('..', '.'));// Filter out '.' and '..' entries
            $files = array_values($files); // Reindex array
            $files = implode("\n", $files);// Convert file list into a newline-separated string


            return $files; // Return the list of files
        }
?>