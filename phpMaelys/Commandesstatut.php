<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD']!== 'POST') {
    header('Location:Commandes.php');
    exit;
}

$fichier= 'data/PMC.json';
$data=json_decode(file_get_contents($fichier), true);
$numero= intval($_POST['numero'] ?? 0);
$statut= $_POST['statut']??'';
$livreur= $_POST['livreur']?? '';

//trouver la commande et MAJ
foreach ($data["commandes"]as $i=> $cmd){
    if ($cmd["numero"]=== $numero){
        if (!empty($statut)){
            $data["commandes"][$i]["statut"]= $statut;
        }
        if (!empty($livreur)){
            $data["commandes"][$i]["livreur"]=$livreur;
        }
        break;
    }
}

file_put_contents(
    $fichier,
    json_encode($data, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE)
);

header('Location:Commandes.php?message=' . urlencode('Commande n°' . $numero . ' mise à jour !'));
exit;
?>
