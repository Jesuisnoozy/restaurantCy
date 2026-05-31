<?php
/**
 * updateProfil.php
 * Endpoint AJAX (fetch) — mise à jour du profil utilisateur.
 * Reçoit un JSON POST, met à jour utilisateurs.json et la session.
 */

session_start();
header("Content-Type: application/json; charset=UTF-8");

/* Utilitaire : encode et envoie une réponse JSON puis coupe court */
function repondre(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* Pas connecté → on ne touche à rien */
if (!isset($_SESSION['utilisateur'])) {
    repondre(["succes" => false, "erreur" => "Vous devez être connecté pour modifier votre profil."]);
}

/* Lecture du corps de la requête */
$corps = file_get_contents("php://input");
$donnees = json_decode($corps, true);

if (!$donnees) {
    repondre(["succes" => false, "erreur" => "Données invalides."]);
}

/* Nettoyage des données reçues */
$nom    = trim($donnees["nom"]           ?? "");
$pseudo = trim($donnees["pseudo"]        ?? "");
$email  = trim($donnees["email"]         ?? "");
$mdp    = $donnees["mot_de_passe"]       ?? "";   // en clair ici, hashé avant écriture
$role   = trim($donnees["role"]          ?? "client");

$rolesAutorises = ["client", "livreur", "restaurateur"];

/* Validation — le client peut mentir, le serveur non */
if (empty($nom)) {
    repondre(["succes" => false, "erreur" => "Le nom ne peut pas être vide."]);
}
if (empty($pseudo)) {
    repondre(["succes" => false, "erreur" => "Le pseudo ne peut pas être vide."]);
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(["succes" => false, "erreur" => "L'adresse email est invalide."]);
}
if (!in_array($role, $rolesAutorises, true)) {
    repondre(["succes" => false, "erreur" => "Rôle non reconnu."]);
}
if (!empty($mdp) && strlen($mdp) < 6) {
    repondre(["succes" => false, "erreur" => "Le mot de passe doit contenir au moins 6 caractères."]);
}

/* Lecture du JSON des utilisateurs */
$cheminFichier = "data/utilisateurs.json";

if (!file_exists($cheminFichier)) {
    repondre(["succes" => false, "erreur" => "Fichier utilisateurs introuvable."]);
}

$contenu = file_get_contents($cheminFichier);
$utilisateurs = json_decode($contenu, true);

if (!is_array($utilisateurs)) {
    repondre(["succes" => false, "erreur" => "Erreur de lecture du fichier utilisateurs."]);
}

/* Identification par email de session — l'email ne change pas en cours de requête */
$emailSession = $_SESSION['utilisateur']['email'] ?? "";
$indexTrouve  = null;

foreach ($utilisateurs as $index => $u) {
    if (isset($u['email']) && $u['email'] === $emailSession) {
        $indexTrouve = $index;
        break;
    }
}

if ($indexTrouve === null) {
    repondre(["succes" => false, "erreur" => "Utilisateur introuvable dans la base."]);
}

/* Unicité email — on évite les doublons silencieux */
if ($email !== $emailSession) {
    foreach ($utilisateurs as $index => $u) {
        if ($index !== $indexTrouve && isset($u['email']) && $u['email'] === $email) {
            repondre(["succes" => false, "erreur" => "Cet email est déjà utilisé par un autre compte."]);
        }
    }
}

/* Unicité pseudo — deux "alice" c'est le chaos */
$pseudoSession = $_SESSION['utilisateur']['pseudo'] ?? "";
if ($pseudo !== $pseudoSession) {
    foreach ($utilisateurs as $index => $u) {
        if ($index !== $indexTrouve && isset($u['pseudo']) && $u['pseudo'] === $pseudo) {
            repondre(["succes" => false, "erreur" => "Ce pseudo est déjà utilisé par un autre compte."]);
        }
    }
}

/* Mise à jour des champs */
$utilisateurs[$indexTrouve]['nom']    = $nom;
$utilisateurs[$indexTrouve]['pseudo'] = $pseudo;
$utilisateurs[$indexTrouve]['email']  = $email;
$utilisateurs[$indexTrouve]['role']   = $role;

if (!empty($mdp)) {
    $utilisateurs[$indexTrouve]['mot_de_passe'] = password_hash($mdp, PASSWORD_BCRYPT);
}

/* Sauvegarde du JSON */
$jsonMisAJour = json_encode($utilisateurs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($cheminFichier, $jsonMisAJour) === false) {
    repondre(["succes" => false, "erreur" => "Impossible d'enregistrer les modifications."]);
}

/* Sync session — sinon la page affiche encore les vieilles infos */
$_SESSION['utilisateur']['nom']    = $nom;
$_SESSION['utilisateur']['pseudo'] = $pseudo;
$_SESSION['utilisateur']['email']  = $email;
$_SESSION['utilisateur']['role']   = $role;

/* Tout s'est bien passé — miracle */
repondre([
    "succes"      => true,
    "message"     => "Vos informations ont bien été mises à jour.",
    "nouveauRole" => $role,
]);
