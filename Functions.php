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
            // if(gettype($value) == 'string'){
            //     $dataType = $dataType."s";
            // }
            // else if(gettype($value) == 'int'){
            //     $dataType = $dataType."i";
            // }
            // else if(gettype($value) == 'float'){
            //     $dataType = $dataType."d";
            // }
            // else {
            //     $dataType = $dataType."b";
            // }
        };

        return $dataType;
    }

    function generateAudit($eventType,$outcome,$desc,$userEmail=null){
        
    }
?>