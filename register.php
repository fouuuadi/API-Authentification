<?php
require_once('bdd_authentification.php');
$pdo = bdd();
//ligne de debug
ini_set('display_errors', 1);
//Connexion a la base de donnée
$methode = filter_input(INPUT_SERVER, "REQUEST_METHOD");
//recuperer les donnees json a l'aide
$donneesjson = file_get_contents("php://input");
$donnees = json_decode($donneesjson, true);
if($methode === "POST" && strlen($donnees["identifiant"]) > 0 && strlen($donnees["motdepasse"]) > 0){
    $id = $donnees ["identifiant"];
    $mdp = $donnees ["motdepasse"];
    $requeteSecure = $pdo->prepare(" SELECT * FROM users WHERE identifiant = :identifiant");
    $requeteSecure->execute([
        ":identifiant" => $id
    ]);
    $resultat = $requeteSecure->fetchAll(PDO::FETCH_ASSOC);

    if(count($resultat) == 0)

    {   $requete = $pdo->prepare("INSERT INTO users (identifiant, motdepasse, membrestaff) VALUES(:identifiant, :motdepasse, :membrestaff)");

        $requete->execute([
            ":identifiant" => $id,
            ":motdepasse" => password_hash($mdp, PASSWORD_DEFAULT),
            ":membrestaff" => "ADMIN"
            ]);
            $reponse = [
                "status" => "succes",
                "message" => "Vous etes maintenant inscrit"
            ];
            header('Content-type: application/json; charset=UFT-8');
            http_response_code(200);
            echo json_encode($reponse, JSON_PRETTY_PRINT);
    } else {
        $reponse = [
            "status" => "Erreur",
            "message" => "Cet utilisateur existe deja"
        ];
        header('Content-type: application/json; charset=UFT-8');
        http_response_code(200);
        echo json_encode($reponse, JSON_PRETTY_PRINT);        
    } 
}
else {
    $reponse = [
        "statut" => "erreur",
        "message" => "Le methode n'est pas POST"
    ];
    header('Content-type: application/json; charset=UTF-8');
    http_response_code(200);
    //http_response_code(400);
    echo json_encode($reponse, JSON_PRETTY_PRINT);
}


