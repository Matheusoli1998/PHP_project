<?php
    class File{

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
?>