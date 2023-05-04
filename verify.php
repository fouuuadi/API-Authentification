<?php

//ligne de debug
ini_set('display_errors', 1);

//Connexion a la base de donnée
$methode = filter_input(INPUT_SERVER, "REQUEST_METHOD");

if($methode === "POST"){
    require_once('bdd_authentification.php');
    $pdo = bdd();
    

    //requete pour joindre la table users et jeton et cree un GROUP BY que permet de lier les 2donnees sur une ligne d'une table
    $requete = $pdo->prepare("SELECT tokens.identifiant FROM jeton AS tokens INNER JOIN users WHERE users.identifiant = tokens.identifiant AND tokens.jeton = :token GROUP BY users.identifiant;");
    //recupere les donnees json a l'aide
    $donneesjson = file_get_contents("php://input");

    if(strlen($donneesjson) > 0 );{
    $donnees = json_decode($donneesjson, true);

    if(!(json_last_error() == JSON_ERROR_NONE) && (!is_array($donnees))){
        $reponse = [
            "status" => "Erreur",
            "message" => "JSON incorrect"
        ];
        header('Content-type: application/json; charset=UTF-8');
        http_response_code(400);
        echo json_encode($reponse, JSON_PRETTY_PRINT);
    }
        if(strlen($donnees["jeton"]) > 0 ){
            $jeton = $donnees["jeton"];
            $requetesecure = $pdo->prepare("SELECT * FROM jeton where jeton = :token");
            $requetesecure->execute([
                ":token" => $jeton
            ]);
            $resultat= $requetesecure->fetch(PDO::FETCH_ASSOC);

            if(!$resultat){
                $reponse = [
                    "status" => "Erreur",
                    "message" => "Le Jeton n'est pas correct, veuillez rentrer le Jeton correct"
                ];
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
                
            } else {
                $requete->execute ([
                    ":token" => $jeton
                ]);
                $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
                $reponse = [
                    "status" => "Succes",
                    "message" => "Token super",
                    "utilisateur" => [
                        "identifiant" => $resultats[0]['identifiant']
                    ]
                    ];
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(200);
                echo json_encode($reponse, JSON_PRETTY_PRINT);  
            }
        } else {
            $reponse = [
                "status"=> "Erreur",
                "message" => "Ça sert à rien de contourner l'attribut required, veuillez remplir les champs de formulaires"
            ];
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(400);
            echo json_encode($reponse, JSON_PRETTY_PRINT);
            
        }
    }
}else {
    $reponse = [
        "status"=> "Erreur",
        "message" => "La methode doit etre POST"
    ];
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode($reponse, JSON_PRETTY_PRINT);
    
}


?>