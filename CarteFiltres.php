<?php
//ici on recoit les filtres& on renvoie les plats correspondants en JSON
 
$data= json_decode(file_get_contents("data/PMC.json"), true);
$plats= $data["plats"];
 
//on recup les filtres
$categorie= $_GET["categorie"] ?? "tous";
$tag= $_GET["tag"] ?? "tous";
$tri= $_GET["tri"] ?? "defaut";
 
//on filtre
$plats_filtres = [];
foreach ($plats as $plat) {
    //filtre/ categorie
    if ($categorie!== "tous" && $plat["categorie"]!== $categorie) {
        continue;
    }
    //filtre/ tag
    if ($tag!== "tous"&& !in_array($tag, $plat["tags"])) {
        continue;
    }
    $plats_filtres[]=$plat;
}
 
//triage
if ($tri=== "prix_croissant") {
    usort($plats_filtres, function($a, $b) {
        return floatval(str_replace("€", "", $a["prix"])) - floatval(str_replace("€", "", $b["prix"]));
    });
}
if ($tri === "prix_decroissant") {
    usort($plats_filtres, function($a, $b) {
        return floatval(str_replace("€", "", $b["prix"])) - floatval(str_replace("€", "", $a["prix"]));
    });
}
 
//renvoie les plats en JSON
header("Content-Type: application/json");
echo json_encode($plats_filtres, JSON_UNESCAPED_UNICODE);
?>
