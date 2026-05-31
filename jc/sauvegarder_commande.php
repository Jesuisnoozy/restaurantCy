<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: EspaceClient.php');
    exit;
}

$fichier='data/PMC.json';
$data= json_decode(file_get_contents($fichier), true);
$numero= intval($_POST['numero'] ?? 0);

//trouver la commande
$index =null;
foreach ($data["commandes"] as $i=> $cmd){
    if ($cmd["numero"]=== $numero){
        $index= $i;
        break;
    }
}

if ($index===null) {
    header('Location: EspaceClient.php?erreur=Commande introuvable.');
    exit;
}

$commande= $data["commandes"][$index];

//ancien prix
$prix_ancien= 0;
foreach ($data["plats"] as $plat) {
    $prix= floatval(str_replace(['€', ' ', ','], ['', '', '.'], $plat["prix"]));
    if (preg_match('/(\d+)x ' . preg_quote($plat["nom"], '/') . '/', $commande["plats"], $match)) {
        $prix_ancien+= intval($match[1]) * $prix;
    }
}

//Récup nouveaux plats
$plats_choisis=[];
$prix_nouveau= 0;
foreach ($data["plats"] as $plat){
    $nom_champ= 'plat_' . str_replace(' ', '_', $plat["nom"]);
    $quantite= intval($_POST[$nom_champ] ?? 0);
    if ($quantite > 0){
        $plats_choisis[]= $quantite . 'x ' . $plat["nom"];
        $prix_nouveau+= $quantite * floatval(str_replace(['€', ' ', ','], ['', '', '.'], $plat["prix"]));
    }
}

if (empty($plats_choisis)) {
    header('Location: CommandesModification.php?numero=' . $numero . '&erreur=Vous devez choisir au moins un plat.');
    exit;
}

// MAJ
$data["commandes"][$index]["plats"] = implode(' + ', $plats_choisis);

// Si -chere -> genere un ticket de réduction
$difference= $prix_nouveau- $prix_ancien;
$message='Commande n°' . $numero . ' modifiée avec succès !';

if ($difference< 0){
    // ajout ticket dans le JSON
    $ticket=[
        "client"=> $_SESSION['pseudo'],
        "montant"=> abs($difference),
        "utilise"=> false
    ];
    $data["tickets"][] = $ticket;
    $message .=' Ticket de réduction de ' . number_format(abs($difference), 2) . '€ généré !';
}

//sauvegarde
file_put_contents(
    $fichier,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

header('Location: EspaceClient.php?message=' . urlencode($message));
exit;
?>
