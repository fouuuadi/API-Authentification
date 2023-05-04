<?php
ini_set('display_errors', 1);

$methode = filter_input(INPUT_SERVER, "REQUEST_METHOD");
if ($methode === "POST"){
    require_once('bdd_authentification.php');
    $pdo = bdd();

    $donneesjson = file_get_contents("php://input");

    if (strlen($donneesjson) > 0){
        $donnees = json_decode($donneesjson, true);

        if(!(json_last_error() == JSON_ERROR_NONE) && (!is_array($donnees))){
            $reponse = [
                "status" => "Erreur",
                "message" => "JSON incorrect"
            ];
            header('Content-type: application/json; charset=UTF-8');
            http_response_code(400);
            echo json_encode($reponse, JSON_PRETTY_PRINT);
            exit();
        } 
        if(strlen($donnees["identifiant"]) > 0 && strlen($donnees["motdepasse"]) > 0){
            $id = $donnees ["identifiant"];
            $mdp = $donnees ["motdepasse"];
            $requeteSecure = $pdo->prepare(" SELECT * FROM users WHERE identifiant = :identifiant");
            $requeteSecure->execute([
                ":identifiant" => $id
            ]);
            $resultat = $requeteSecure->fetch(PDO::FETCH_ASSOC);

            if(!$resultat) {
                $reponse = [
                    "status" => "Erreur",
                    "message" => "Cet utilisateur n'existe pas."
                ];
                header('Content-type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
                exit();
            }
            $requeteJeton = $pdo->prepare("SELECT *FROM jeton WHERE identifiant = :identifiant");
            $requeteJeton->execute([
                ":identifiant" => $id
            ]);
            $resultatJeton = $requeteJeton->fetch(PDO::FETCH_ASSOC);

            if($resultatJeton) {
                $reponse = [
                    "status" => "Erreur",
                    "message" => "Cet utilisateur a déja un jeton."
                ];
                header('Content-type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
                exit();
            };

            if(password_verify($mdp, $resultat["motdepasse"])){
                $generatorJeton = random_bytes(15);
                $jeton = strtoupper((bin2hex($generatorJeton)));
                $requete = $pdo->prepare("INSERT INTO jeton (identifiant, jeton) VALUES (:identifiant, :jeton)");
                $requete->execute([
                    ":identifiant" => $id,
                    ":jeton" => $jeton
                ]);
                $resultat = $requete->fetch(PDO::FETCH_ASSOC);
                $reponse = [
                    "status" => "Succes",
                    "message" => $jeton
                ];
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(200);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
            } else {
                $reponse = [
                    "status" => "Erreur",
                    "message" => "Mot de passe incorrect"
                ];
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(401);
                echo json_encode($reponse, JSON_PRETTY_PRINT);
            }
            
        }    
    }
}else {
            $reponse = [
                "status" => "Erreur",
                "message" => "La methode n'est pas post"
            ];
            header('Content-type: application/json; charset=UTF-8');
            http_response_code(400);
            echo json_encode($reponse, JSON_PRETTY_PRINT);
        }