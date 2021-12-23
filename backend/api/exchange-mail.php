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
elseif(!isset($data->user_id) 
    || !isset($data->contact_id)
    || empty(trim($data->user_id))
    || empty(trim($data->contact_id))
    ):

    $returnData = msg(0,422,'Contatto mancante');
    
else:
    $user_id = trim($data->user_id);
    $contact_id = trim($data->contact_id);

    try{

        $fetch_contact_by_id = "SELECT * FROM `users` WHERE `id`=:contact_id";
        $query_stmt = $conn->prepare($fetch_contact_by_id);
        $query_stmt->bindValue(':contact_id', $contact_id,PDO::PARAM_STR);
        $query_stmt->execute();

        //exchange
        if($query_stmt->rowCount()):
            $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
    
            $insert_query = "INSERT INTO `exchanges`(`user_1`, `user_2`) 
                            SELECT * FROM (SELECT :contact_id , :user_id) AS tmp 
                            WHERE NOT EXISTS (
                            SELECT * FROM `exchanges` 
                            WHERE :contact_id IN (`user_1`,`user_2`) 
                            AND :user_id IN (`user_1`,`user_2`))";
            
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bindValue(':contact_id', $contact_id,PDO::PARAM_STR);
            $insert_stmt->bindValue(':user_id', $user_id,PDO::PARAM_STR);
            $insert_stmt->execute();
            
            $returnData = msg(1,201,"Scambio avvenuto con successo");
           
        else:
            $returnData = msg(1,201, "Codice contatto non valido");
        endif;

    }catch(PDOException $e){
        $returnData = msg(0,500,"Inserisci un id diverso dal tuo");
    }
endif;

echo json_encode($returnData);
