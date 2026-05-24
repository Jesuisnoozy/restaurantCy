<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données POST
    $data_pmc = json_decode(file_get_contents("data/PMC.json"), true);
    $data_users = json_decode(file_get_contents("data/utilisateurs.json"), true);

    $plats = $data_pmc["plats"];
    $menus = $data_pmc["menus"];
    
    $total_brut = 0;

    foreach ($plats as $plat) {
        $nom_input = 'plat_' . str_replace(' ', '_', $plat['nom']);
        if (isset($_POST[$nom_input])) {
            $quantite = intval($_POST[$nom_input]);
            $total_brut += ($plat['prix'] * $quantite);
        }
    }

    foreach ($menus as $menu) {
        $nom_input = 'menu_' . str_replace(' ', '_', $menu['nom']);
        if (isset($_POST[$nom_input])) {
            $total_brut += $menu['prix'];
        }
    }

    $reduction_montant = 0;
    $pseudo_actuel = $_SESSION['pseudo'] ?? '';

    foreach ($data_users as $user) {
        if ($user['pseudo'] === $pseudo_actuel) {
            // Réduction VIP : les riches paient moins, c'est la vie
            if (isset($user['vip']) && $user['vip'] === true) {
                $pourcentage = floatval($user['reduction_vip']); // ex: 10 pour 10%
                $reduction_montant = $total_brut * ($pourcentage / 100);
            }
            break;
        }
    }

    $total_final = $total_brut - $reduction_montant;

    if ($total_final <= 0) {
        header('Location: panier.php');
        exit;
    }

    $_SESSION['montant_a_payer'] = $total_final;
    $_SESSION['reduction_appliquee'] = $reduction_montant; // Stocké en session pour l'afficher sur la page de paiement

    header('Location: payement.php');
    exit;

} else {
    header('Location: panier.php');
    exit;
}