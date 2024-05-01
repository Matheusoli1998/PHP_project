<?php

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
            if(gettype($value) == 'string'){
                $dataType = $dataType."s";
            }
            else if(gettype($value) == 'int'){
                $dataType = $dataType."i";
            }
            else if(gettype($value) == 'float'){
                $dataType = $dataType."d";
            }
            else {
                $dataType = $dataType."b";
            }
        };

        return $dataType;
    }

    function generateAudit($eventType,$outcome,$desc,$userEmail=null){
        
    }
?>