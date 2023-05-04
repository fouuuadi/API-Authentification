<?php

//ligne de debug
ini_set('display_errors', 1);

//Connexion a la base de donnée
$methode = filter_input(INPUT_SERVER, "REQUEST_METHOD");

if ($methode === "GET") {

require_once('bdd_authentification.php');
$pdo = bdd();

    $requete = $pdo->prepare("DELETE FROM jeton WHERE jeton = :token");

    $donneesjson = file_get_contents("php://input");
    if(strlen($donneesjson) > 0) {
        $donnees = json_decode($donneesjson,true);

    if (!(json_last_error() == JSON_ERROR_NONE) && (is_array($donnees))) {
        $reponse = [
            "status" => "Erreur",
            "message" => "JSON incorrect"
        ];
        header('Content-type: application/json; charset=UTF-8');
        http_response_code(400);
        echo json_encode($reponse, JSON_PRETTY_PRINT);
    }
        elseif (strlen($donnees["jeton"]) > 0 ) {
            $jeton = $donnees["jeton"];
            $requetesecure = $pdo->prepare("SELECT * FROM jeton WHERE jeton = :token ");
            $requetesecure->execute ([
                ":token" => $jeton
            ]);
            $resultat = $requetesecure->fetch(PDO::FETCH_ASSOC);

            if(!$resultat){
                $reponse = [
                    "status" => "Erreur",
                    "message" => "Jeton incorrect"
                ];
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
            }
            else {
                $requete->execute ([
                    ":token" => $jeton
                ]);
                $reponse = [
                    "status" => "Succes",
                    "message" => "Deconnexion effectuee"
                ];
                header('Content-type: application/json; charset=UTF-8');
                http_response_code(200);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
                session_start();
                session_destroy();
            }
        } else {
            $reponse = [
                "status" => "Erreur",
                "message" => "Aucune donnee envoyee"
            ];
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(401);
            echo json_encode($reponse, JSON_PRETTY_PRINT);
            }
        }
} else {
    $reponse = [
        "status" => "Erreur",
        "message" => "La methode doit être GET"
    ];
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode($reponse, JSON_PRETTY_PRINT);
}
