<?php
function bdd(){
    $idBDD = "root";
    $mdpBDD = "root";
    $pdo = new PDO("mysql:host=localhost:3306;dbname=exempleprojet",$idBDD,$mdpBDD);
    return $pdo;
}