<?php

require __DIR__.'/classes/Database.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

//check method
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

//check parametres exist
elseif(!isset($data->email) 
    || !isset($data->password)
    || empty(trim($data->email))
    || empty(trim($data->password))
    ):

    $fields = ['fields' => ['email','password']];
    $returnData = msg(0,422,'Inserisci tutti i campi',$fields);
    

else:
    $email = trim($data->email);
    $password = trim($data->password);

    // check email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0,422,'Indirizzo email non valido');
    
    // check password
    elseif(strlen($password) < 8):
        $returnData = msg(0,422,'La tua password deve essere lunga almeno 8 caratteri');
    
    //login
    else:
        try{
            
            $fetch_user_by_email = "SELECT * FROM `users` WHERE `email`=:email";
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':email', $email,PDO::PARAM_STR);
            $query_stmt->execute();
            
            if($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);
                                
                if($password == $row['password']):                   
                    
                    $returnData = [
                        'success' => 1,
                        'message' => 'Login effettuato',
                        'name' => $row['name'],
                        'user_id' => $row['id']
                    ];

                else:
                    $returnData = msg(0,422,'Password non valida');
                endif;

            else:
                $returnData = msg(0,422,'Indirizzo email non valido');
            endif;
        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);