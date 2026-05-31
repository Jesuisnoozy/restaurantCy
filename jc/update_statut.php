<?php
session_start();

// 1. Vérification de la session
if (!isset($_SESSION['utilisateur'])) {
    header('Location: connexion.php');
    exit;
}

// 2. Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: EspaceLivreur.php?error=Methode_invalide');
    exit;
}

// 3. Récupération et validation des données
$numero = isset($_POST['numero']) ? intval($_POST['numero']) : 0;
$action = $_POST['action'] ?? '';

if ($numero === 0) {
    header('Location: EspaceLivreur.php?error=Numero_invalide');
    exit;
}

if (empty($action) || !in_array($action, ['livree', 'abandonnee'])) {
    header('Location: EspaceLivreur.php?error=Action_invalide');
    exit;
}

// 4. Charger le JSON
$fichier = 'data/PMC.json';

if (!file_exists($fichier)) {
    header('Location: EspaceLivreur.php?error=Fichier_PMC_introuvable');
    exit;
}

$contenu = file_get_contents($fichier);
if ($contenu === false) {
    header('Location: EspaceLivreur.php?error=Erreur_lecture_fichier');
    exit;
}

$data = json_decode($contenu, true);

if (!is_array($data) || !isset($data["commandes"]) || !is_array($data["commandes"])) {
    header('Location: EspaceLivreur.php?error=Structure_JSON_invalide');
    exit;
}

// 5. Déterminer le nouveau statut
$nouveauStatut = '';
if ($action === 'livree') {
    $nouveauStatut = 'Livrée';
} elseif ($action === 'abandonnee') {
    $nouveauStatut = 'Abandonnée';
}

// 6. Chercher et modifier la commande
$trouve = false;

foreach ($data["commandes"] as $i => $commande) {
    // Comparer les numéros (gérer les cas où numero peut être string ou int)
    if ((int)$commande["numero"] === $numero) {
        $data["commandes"][$i]["statut"] = $nouveauStatut;
        $trouve = true;
        break;
    }
}

// 7. Si trouvé, sauvegarder le JSON
if ($trouve) {
    $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($fichier, $json_string, LOCK_EX) !== false) {
        // Succès
        header('Location: EspaceLivreur.php?message=Commande_' . $numero . '_mise_a_jour_en_' . urlencode($nouveauStatut));
        exit;
    } else {
        header('Location: EspaceLivreur.php?error=Erreur_sauvegarde_fichier');
        exit;
    }
} else {
    // Commande non trouvée
    header('Location: EspaceLivreur.php?error=Commande_' . $numero . '_introuvable');
    exit;
}
?>
