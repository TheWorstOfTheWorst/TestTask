<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <ul>
    <?php
         $config = require 'config.php';

         $conn =  new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
        
         if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }
          else {
            echo "Database Connected successfully";
        }
          $result = $conn->query("SELECT `pid`, `id`, `name`, `alias`, `spaces`  FROM `categories`");

          function printData($myRow){
            foreach ($myRow as $row) {
                if ($row['spaces'] == 0){
                    echo "<li>".$row["name"]."</li>";
                } else {
                    for ($i = 0; $i < $row['spaces']; $i++){
                        echo "<ul>";
                    }
                    echo "<li>".$row["name"]."</li>"  ;
                    for ($i = 0; $i < $row['spaces']; $i++){
                        echo "</ul>";
                    }
                }
                    
            }  
        }

        printData($result);
           
    ?>
    </ul>
</body>
</html>