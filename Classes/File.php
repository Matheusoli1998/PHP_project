<?php
    class FileUpload{

        private $destAddr;
        private $srcFile;
        private $sizeCap;

        function __construct($srcFile,$sizeCap,$destAddr)
        {
            $this->destAddr = $destAddr;
            $this->srcFile = $srcFile;
            $this->sizeCap = $sizeCap;
        }

        function fileSize(){
            if($this->srcFile['size'] > $this->sizeCap){
                throw new Exception("File size larger than $this->sizeCap",413);
            }
        }

        function extension_check(){
            $contType = substr($this->srcFile['type'],0,strpos($this->srcFile['type'],"/"));
            $extArray = null;
            switch ($contType){
                case 'image':
                    $extArray = ['jpg','jpeg','png','bmp','webp'];
                    break;
                case 'application':
                    $extArray = ['json'];
                    break;
                default:
                    throw new Exception('Invalid file type',403);
                }
                
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realExtension = basename($finfo->file($this->srcFile['tmp_name']));

            if(!(false === array_search($realExtension,$extArray))){
                return true;
            }
            throw new Exception('Invalid file type',403);
        }

        function uploadFile(){
            $this->fileSize($this->srcFile,$this->sizeCap);
            $this->extension_check($this->srcFile);

            if(!is_dir('./'.BACKEND_PICTURES_PATH)){
                mkdir('./'.BACKEND_PICTURES_PATH);
            }

            $destAddr = $this->destAddr.'/'.$this->srcFile['name'];
            if(!move_uploaded_file($this->srcFile['tmp_name'],$destAddr)){
                throw new Exception("Failed to upload file",500);
            }

            $completeAddr = $_SERVER['REQUEST_SCHEME']
                ."://"
                .$_SERVER['SERVER_ADDR']
                .substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],"paths.php"))
                .BACKEND_PICTURES_PATH."/"
                .$this->srcFile['name'];

            return $completeAddr;

        }
    }

    class File{
        private $src_addr; // Base directory for file operations
        private $fileAddr;// Full path to the current file
        function __construct($src_addr)// Constructor to set base directory
        {
            $this->src_addr = $src_addr;
        }
        function readFile($fileName){ // Read data from a file
            $this->fileAddr = $this->src_addr."/$fileName";
            if(file_exists($this->fileAddr)){
                $file = fopen($this->fileAddr,"r");
                $data = fread($file,filesize($this->fileAddr));
                fclose($file);
                return $data;
            }else{
                return false;
            }

        }

        // Write data to a file, with an option for hard overwrite
        function writeFile($fileName,$data,$hardWrite = false){
            $this->fileAddr = $this->src_addr."/$fileName";
            $writeFlag = "w";
            if(file_exists($this->fileAddr) && !$hardWrite){
                $extension = explode(".",$fileName)[1];
                    switch(strtolower($extension)){
                        case "txt":
                            $writeFlag = "a";
                        break;
                        case "json":
                            $this->writeJSON($fileName,$data);
                            return 0;
                        break;
                    }
            }
                $file = fopen($this->fileAddr,$writeFlag);
                fwrite($file,(is_array($data))?json_encode([$data]):$data);
                fclose($file);
        }

            // Special method to handle JSON data append operation
        private function writeJSON($fileName,$data){
            $prevData = json_decode($this->readFile($fileName));
            array_push($prevData,$data);
            $this->writeFile($fileName,json_encode($prevData),true);
        }


    }
?>