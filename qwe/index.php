<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    echo "<pre>"; 

    

 
    file_put_contents('type_a.txt', '');
    file_put_contents('type_b.txt', '');

    function getDataAndWrite ($arr) {
        static $spaces = 0;
        static $dataArray = array();
    

        $handle = fopen(__DIR__ . "/type_a.txt", "a+");
        
        foreach ($arr as $el){
    
            if (is_object($el)){
                for ($i = 0; $i < $spaces; $i++){
                    fwrite($handle, " ");
                }
                fwrite($handle, $el->name." ".$el->alias.PHP_EOL);
    
                if (isset($el->childrens[0]->id)){
                    $dataArrayChildrens = array();
                    foreach ($el->childrens as $val) {

                        $dataArrayChildrens[] = $val->id;
                    }

                    $dataArray[] = array('id' => $el->id, 'name' => $el->name, 'childrens' => $dataArrayChildrens, 'spaces' => $spaces);
                    
                    $spaces+=1;
                    getDataAndWrite($el->childrens);
            
                } else {
                    $dataArray[] = array ('id' => $el->id, 'name' => $el->name, 'childrens' => [], 'spaces' => $spaces);
          
                }
            }
        }
      
        $spaces = 0;
        fclose($handle);

        return $dataArray;
       
    }

    function writeDataFirstLevel ($arr) {

        $handle = fopen(__DIR__ ."/type_b.txt", "a+");
        
        foreach ($arr as $el){
           
            if (is_object($el)){
                fwrite($handle, $el->name." ".$el->alias.PHP_EOL);
                if (isset($el->childrens[0]->id)){
                    for ($i = 0; $i < count( $el->childrens); $i++){
                        fwrite($handle, " ");
                        fwrite($handle, $el->childrens[$i]->name." ".$el->childrens[$i]->alias.PHP_EOL);
                    }
                }
            }
        }
        fclose($handle);
    }

    function writeToDB($dataToWrite){

        $config = require 'config.php';

        $conn =  new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
    
        if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        } else {
            echo "Database Connected successfully";
        }
        

        $result = $conn->query("CREATE TABLE IF NOT EXISTS `categories` (
            `pid` int(32) NOT NULL AUTO_INCREMENT,
            `id` text NOT NULL,
            `name` varchar(64) CHARACTER SET utf8 NOT NULL,
            `alias` varchar(64) CHARACTER SET utf8 NOT NULL,
            `childrens` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`childrens`)),
            `spaces` int(32) NOT NULL,
            PRIMARY KEY (`pid`)
          ) ENGINE=InnoDB AUTO_INCREMENT=386 DEFAULT CHARSET=utf8mb4");

        $result = $conn->query("DELETE FROM `categories` ");

        foreach ($dataToWrite as $value) {
            $children = array();
            $dataWithoutChildren =  json_decode(json_encode($value));

            if ( count($value['childrens']) > 0 ){
                foreach($value['childrens'] as $child){
                    $children[] =  $child;
                }
                $jsonDataToDB = json_encode($children);
         
                $result = $conn->query("INSERT INTO `categories`(`id`, `name`, `alias`, `childrens`, `spaces`) VALUES ('$dataWithoutChildren->id','$dataWithoutChildren->name','1', '$jsonDataToDB', $dataWithoutChildren->spaces)");
            } else {
                $result = $conn->query("INSERT INTO `categories`(`id`, `name`, `alias`, `childrens`, `spaces`) VALUES ('$dataWithoutChildren->id','$dataWithoutChildren->name','1', 'null',$dataWithoutChildren->spaces )");
            }
        }
    }

    $jsonDataFromFile = file_get_contents(__DIR__ ."/categories.json");
    $dencodedJson = json_decode($jsonDataFromFile);

    
    writeToDB(getDataAndWrite($dencodedJson));
    writeDataFirstLevel($dencodedJson);
    echo PHP_EOL."Files created";
    echo PHP_EOL."Database updated";


    ?>
</body>
</html>