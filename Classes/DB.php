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

            return $result;
        }

        function select($tableName,$col,$value, $columNames=null){
            $cols = isset($columNames) ? implode(" , ",$columNames) : '*';
            $query = "SELECT $cols FROM $tableName WHERE $col = $value";
            $result = $this->db_connect->query($query);

            return $result;
        }

        function insert($tableName, $data, $columNames){
            $fields = isset($columNames) ? "(".implode(" , ",$columNames).")" : "";
            $dataType = getDataType($data);
            $valuesCount = array_map(function(){ return "?"; },$data);
            $valuesCount = implode(",",$valuesCount);

            $insertCmd = $this->db_connect->prepare("INSERT INTO $tableName $fields VALUES ($valuesCount)");
            $insertCmd->bind_param($dataType, ...$data);

            if($insertCmd->execute() === TRUE){
                // generateAudit("register","success","registration to database");
                return true;
            }else{
                // generateAudit("register","failed","registration to database unsuccessfull");
                throw new Exception("Insert Data Error",500);
            }
        }

        function updateValue($tableName,$id,$idColName,$col,$value){
            $query = "UPDATE $tableName SET $col = $value WHERE $idColName = $id";
            if($this->db_connect->query($query) === TRUE){
                generateAudit("update","success","user update",$_POST["email"]);
                return true;
            }else{
                generateAudit("update","failed","user not updated",$_POST["email"]);
                throw new Exception("Update Data Error",500);
            }
        }

        // function updateMultipleValue($tableName,$id,$idColName,){
        //     $cols = 
        //     $query = "UPDATE $tableName SET $cols WHERE $idColName = $id";
        // }

        function delete(){
            
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
                
                if($rowValue->num_rows === 0){
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
                
                if($rowValue->num_rows === 0){
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
                if($rowValue->num_rows === 0){
                    print_r($row);
                    $hashPass = password_hash($row->pass,PASSWORD_BCRYPT,["cost"=>10]);
                    $type = array_key_exists('type',$row) ? $row->type : 'customer';
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