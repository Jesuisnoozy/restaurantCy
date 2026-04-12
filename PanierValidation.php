<?php
session_start();
 
if (!isset($_SESSION['pseudo'])) {
    header('Location: connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panier.php');
    exit;
}

$fichier = 'data/PMC.json';
$data    = json_decode(file_get_contents($fichier), true);
 
$plats_choisis = [];
 
foreach ($data["plats"] as $plat) {
    $nom_champ = 'plat_' . $plat["nom"];
    $quantite  = intval($_POST[$nom_champ] ?? 0);
    if ($quantite > 0) {
        $plats_choisis[] = $quantite . 'x ' . $plat["nom"];
    }
}
 
foreach ($data["menus"] as $menu) {
    $nom_champ = 'menu_' . $menu["nom"];
    if (isset($_POST[$nom_champ])) {
        $plats_choisis[] = '1x ' . $menu["nom"];
    }
}
 
if (empty($plats_choisis)) {
    header('Location: panier.php?erreur=Vous devez choisir au moins un plat.');
    exit;
}
 
$type    = $_POST['type']    ?? 'sur_place';
$adresse = trim($_POST['adresse'] ?? '');
 
if ($type === 'livraison' && empty($adresse)) {
    header('Location: panier.php?erreur=Veuillez entrer une adresse de livraison.');
    exit;
}
 
if ($type === 'sur_place') {
    $adresse = 'Le Goupix, 3 rue de la Paix, Paris';
}
 

$quand          = $_POST['quand'] ?? 'maintenant';
$date_souhaitee = $_POST['date_souhaitee'] ?? '';
 
if ($quand === 'maintenant') {
    $date  = date('Y-m-d');
    $heure = date('H:i');
} else {
    if (empty($date_souhaitee)) {
        header('Location: panier.php?erreur=Veuillez choisir une date et heure.');
        exit;
    }
    $date  = date('Y-m-d', strtotime($date_souhaitee));
    $heure = date('H:i',   strtotime($date_souhaitee));
}
 
// Créer le numéro de commande 
$dernier_numero = 0;
foreach ($data["commandes"] as $commande) {
    if ($commande["numero"] > $dernier_numero) {
        $dernier_numero = $commande["numero"];
    }
}
$nouveau_numero = $dernier_numero + 1;
 

$nouvelle_commande = [
    "numero"   => $nouveau_numero,
    "client"   => $_SESSION['pseudo'],
    "type"     => $type,
    "adresse"  => $adresse,
    "date"     => $date,
    "heure"    => $heure,
    "plats"    => implode(' + ', $plats_choisis),
    "statut"   => "En attente",
    "livreur"  => "Non attribué",
    "paiement" => "Non payé"  
];
 

$data["commandes"][] = $nouvelle_commande;
 
file_put_contents(
    $fichier,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
 
header('Location: client.php?message=Commande n°' . $nouveau_numero . ' passée avec succès !');
exit;
?>
 
