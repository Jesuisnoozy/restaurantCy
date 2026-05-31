<?php
/**
 * modifyProfile.php
 * Endpoint pour traiter les modifications de profil en asynchrone
 * Requête: POST JSON depuis modificationProfil.js
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

// Vérifier authentification
if (!isset($_SESSION['pseudo'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ]);
    exit;
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// Données reçues
$pseudo_cible = trim($data['pseudo'] ?? '');
$nouveau_nom = trim($data['nouveau_nom'] ?? '');
$nouveau_email = trim($data['nouveau_email'] ?? '');
$nouveau_telephone = trim($data['nouveau_telephone'] ?? '');
$nouvelle_adresse = trim($data['nouvelle_adresse'] ?? '');
$nouveau_role = trim($data['nouveau_role'] ?? '');

// Sécurité: l'utilisateur ne peut modifier que son propre profil
if ($pseudo_cible !== $_SESSION['pseudo']) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Vous ne pouvez modifier que votre propre profil'
    ]);
    exit;
}

// Validation des champs
$erreurs = [];

if (empty($nouveau_nom)) {
    $erreurs[] = 'Le nom est obligatoire';
} elseif (strlen($nouveau_nom) < 2) {
    $erreurs[] = 'Le nom doit contenir au moins 2 caractères';
}

if (empty($nouveau_email)) {
    $erreurs[] = 'L\'email est obligatoire';
} elseif (!filter_var($nouveau_email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'Email invalide';
}

if (!empty($nouveau_telephone) && !preg_match('/^[0-9\s\-\+\.()]+$/', $nouveau_telephone)) {
    $erreurs[] = 'Numéro de téléphone invalide';
}

// Valider le rôle
$roles_valides = ['client', 'restaurateur', 'livreur'];
if (empty($nouveau_role) || !in_array($nouveau_role, $roles_valides)) {
    $erreurs[] = 'Rôle invalide';
}

if (!empty($erreurs)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreurs de validation',
        'erreurs' => $erreurs
    ]);
    exit;
}

try {
    // Charger le fichier JSON
    $fichier = 'data/utilisateurs.json';
    
    if (!file_exists($fichier)) {
        throw new Exception('Fichier utilisateurs introuvable');
    }

    $utilisateurs = json_decode(file_get_contents($fichier), true);
    
    if (!is_array($utilisateurs)) {
        throw new Exception('Erreur de lecture du fichier');
    }

    // Trouver l'utilisateur
    $index = null;
    foreach ($utilisateurs as $i => $u) {
        if ($u['pseudo'] === $pseudo_cible) {
            $index = $i;
            break;
        }
    }

    if ($index === null) {
        throw new Exception('Utilisateur introuvable');
    }

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $email_existe = false;
    foreach ($utilisateurs as $i => $u) {
        if ($i !== $index && $u['email'] === $nouveau_email) {
            $email_existe = true;
            break;
        }
    }

    if ($email_existe) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé'
        ]);
        exit;
    }

    // Mettre à jour les informations
    $utilisateurs[$index]['nom'] = $nouveau_nom;
    $utilisateurs[$index]['email'] = $nouveau_email;
    $utilisateurs[$index]['role'] = $nouveau_role;
    
    if (!empty($nouveau_telephone)) {
        $utilisateurs[$index]['telephone'] = $nouveau_telephone;
    }
    
    if (!empty($nouvelle_adresse)) {
        $utilisateurs[$index]['adresse'] = $nouvelle_adresse;
    }

    // Ajouter date de modification
    $utilisateurs[$index]['date_modification'] = date('Y-m-d H:i:s');

    // Sauvegarder le JSON
    file_put_contents($fichier, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Mettre à jour la session
    $_SESSION['nom'] = $nouveau_nom;
    $_SESSION['email'] = $nouveau_email;
    $_SESSION['role'] = $nouveau_role;

    echo json_encode([
        'success' => true,
        'message' => 'Profil mis à jour avec succès ✓',
        'user' => [
            'nom' => $nouveau_nom,
            'email' => $nouveau_email,
            'telephone' => $nouveau_telephone ?: '—',
            'adresse' => $nouvelle_adresse ?: '—',
            'role' => $nouveau_role
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>