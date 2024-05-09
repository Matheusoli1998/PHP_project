<?php
    class DB{
        private $db_hostname;
        private $db_username;
        private $db_password;
        private $db_name;
        private $db_connect;

        function __construct($db_hostname,$db_username,$db_password,$db_name)
        {
            $this->db_hostname = $db_hostname;
            $this->db_username = $db_username;
            $this->db_password = $db_password;
            $this->db_name = $db_name;
        }
        
        function connect(){
            $db_conexion = new mysqli(
                $this->db_hostname, 
                $this->db_username,
                $this->db_password,
                $this->db_name
            );
            if($db_conexion->connect_error){
                throw new Exception("Connection failed.",500);
            }
            $this->db_connect = $db_conexion;
            return $db_conexion;
        }

        function db_close(){
            $this->db_connect->close();
        }

        function selectAll($tableName,$colNames=null){
            $cols = isset($colNames) ? implode(" , ",$colNames) : '*';
            $query = "SELECT $cols FROM $tableName";
            $result = $this->db_connect->query($query);
            if($result){
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                throw new Exception('Unable to get data',500);
            }
        }

        function select($tableName,$col,$value, $columNames=null){

            $cols = isset($columNames) ? implode(" , ",$columNames) : '*';
            $query = "SELECT $cols FROM $tableName WHERE $col = $value";
            throw new Exception($query,500);
            $result = $this->db_connect->query($query);

            if($result){
                return $result->fetch_assoc();
            } else {
                throw new Exception('Unable to get data',500);
            }
        }

        function selectJoin($left_table,$joinTable, $joinCol, $value, $joins, $columNames=null){
            $cols = isset($columNames) ? implode(" , ",$columNames) : '*';
            $joins = implode(" ", $joins);

            $query = "SELECT $cols FROM $left_table $joins WHERE $joinTable.$joinCol = $value";

            //throw new Exception( $query,500);
            
            $result = $this->db_connect->query($query);
            
            if(!$result || $result->num_rows === 0){
                // throw new Exception('Nothing found',404);
                return null;
            }

            return $result->fetch_all(MYSQLI_ASSOC);       
        }

        function insert($tableName, $data, $columNames){
            $fields = isset($columNames) ? "(".implode(" , ",$columNames).")" : "";
            $dataType = getDataType($data);

            $valuesCount = array_map(function(){ return "?"; },$data);
            $valuesCount = implode(",",$valuesCount);

            $insertCmd = $this->db_connect->prepare("INSERT INTO $tableName $fields VALUES ($valuesCount)");
            if($insertCmd === false){
                throw new Exception("Query Error",500);
            }
            
            $insertCmd->bind_param($dataType, ...$data);

            if($insertCmd->execute() === TRUE){
                return true;
            }else{
                throw new Exception("Insert Data Error",500);
            }
        }

        function updateValue($tableName,$id,$idColName,$col,$value){
            $dataType = getDataType([$value]);

            $insertCmd = $this->db_connect->prepare("UPDATE $tableName SET $col = ? WHERE $idColName = $id");
            if($insertCmd === false){
                throw new Exception("Query Error",500);
            }

            $insertCmd->bind_param($dataType, $value);

            if($insertCmd->execute() === TRUE){
                return true;
            }else{
                throw new Exception("Update Data Error",500);
            }
        }

        function updateMultiple($tableName,$id,$idColName,$cols){
            $colsValues = [];
            $values = [];

            foreach($cols as $colName => $value){
                array_push($values,$value);
                array_push($colsValues, "$colName = ?");
            }
            $colsValues = implode(" , ",$colsValues);
            $dataType = getDataType($values);

            $insertCmd = $this->db_connect->prepare("UPDATE $tableName SET $colsValues WHERE $idColName = $id");
            $insertCmd->bind_param($dataType, ...$values);

            if($insertCmd->execute() === TRUE){
                return true;
            }else{
                throw new Exception("Update Data Error",500);
            }
        }

        function delete($tableName,$idColName,$id){
            $query = "DELETE FROM $tableName WHERE $idColName = $id";
            if($this->db_connect->query($query)){
                return true;
            }else{
                throw new Exception("Unable to Delete Data",500);
            }

        }

        function importCatsJson(){
            $json = fopen('./json/cats.json','r');
            $data = fread($json,filesize('./json/cats.json'));
            fclose($json);
            
            $data = json_decode($data);
            foreach($data as $row){
                $rowValue = $this->select(
                    'cats_tb',
                    'cid',
                    "'".$row->cid."'"
                );
                
                if($rowValue && count($rowValue) === 0){
                    $this->insert(
                        'cats_tb',
                        [$row->cid,$row->catName,$row->cataAge,$row->catBreed,$row->catDescription,$row->adoptionStatus,$row->catImage],
                        ['cid','catName','cataAge','catBreed','catDescription','adoptionStatus','catImage']
                    );
                }
            }
        }

        function importMenuJson(){
            $json = fopen('./json/menu.json','r');
            $data = fread($json,filesize('./json/menu.json'));
            fclose($json);

            $data = json_decode($data);
            foreach($data as $row){
                $rowValue = $this->select(
                    'menu_tb',
                    'mid',
                    "'".$row->mid."'"
                );
                
                if($rowValue && count($rowValue) === 0){
                    $this->insert(
                        'menu_tb',
                        [$row->mid,$row->menuName,$row->menuDescription,$row->menuPrice,$row->menuCategory,$row->menuImage],
                        ['mid','menuName','menuDescription','menuPrice','menuCategory','menuImage']
                    );
                }
            }
        }

        function importUsersJson(){
            $json = fopen('./json/customers.json','r');
            $data = fread($json,filesize('./json/customers.json'));
            fclose($json);

            $data = json_decode($data);
            foreach($data as $row){
                $rowValue = $this->select(
                    'users_tb',
                    'id',
                    "'".$row->id."'"
                );
                if($rowValue && count($rowValue) === 0){
                    $hashPass = password_hash($row->pass,PASSWORD_BCRYPT,["cost"=>10]);
                    $type = $row->type ? $row->type : 'customer';
                    $this->insert(
                        'users_tb',
                        [$row->id,$row->username,$hashPass,$row->email,$type],
                        ['id','username','pass','email','type']
                    );
                }
            }
        }
    }
?>