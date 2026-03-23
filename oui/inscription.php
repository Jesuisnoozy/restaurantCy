<?php
session_start();

if (isset($_SESSION['connecte'])) {
    header('Location: ' . $_SESSION['role'] . '.php');
    exit;
}

$erreur = "";
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom    = trim($_POST['nom']          ?? '');
    $email  = trim($_POST['email']        ?? '');
    $pseudo = trim($_POST['pseudo']       ?? '');
    $mdp    = trim($_POST['mot_de_passe'] ?? '');
    $role   = $_POST['role']              ?? '';

    if (empty($nom) || empty($email) || empty($pseudo) || empty($mdp) || empty($role)) {
        $erreur = "Tous les champs sont obligatoires.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";

    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit faire au moins 6 caractères.";

    } elseif (!in_array($role, ['client', 'restaurateur', 'livreur'])) {
        $erreur = "Rôle invalide.";

    } else {

        $fichier      = 'data/utilisateurs.json';
        $utilisateurs = json_decode(file_get_contents($fichier), true);

        foreach ($utilisateurs as $u) {
            if ($u['pseudo'] === $pseudo) {
                $erreur = "Ce pseudo est déjà utilisé.";
                break;
            }
            if ($u['email'] === $email) {
                $erreur = "Cet email est déjà utilisé.";
                break;
            }
        }

        if (empty($erreur)) {

            $utilisateurs[] = [
                'nom'               => $nom,
                'email'             => $email,
                'pseudo'            => $pseudo,
                'mot_de_passe'      => password_hash($mdp, PASSWORD_DEFAULT),
                'role'              => $role,
                'statut'            => 'actif',
                'vip'               => false,
                'reduction_vip'     => 0,
                'raison_suspension' => ''
            ];

            file_put_contents(
                $fichier,
                json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $succes = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>

<header>
    <nav>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php">Inscription</a>
    </nav>
</header>

<h1>Créer un compte</h1>

<?php if ($succes): ?>

    <div class="msg-succes-global">
        Compte créé avec succès ! Vous pouvez maintenant vous connecter.
    </div>
    <div style="text-align:center; margin-top:20px;">
        <a href="connexion.php" class="button-17">Se connecter</a>
    </div>

<?php else: ?>

    <?php if ($erreur): ?>
        <div class="msg-erreur-global"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <div class="crocodile">

        <h2>Rejoindre la plateforme</h2>

        <form method="POST" action="" class="poulpe">

            <div class="termite">
                <div class="fourmi">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom"
                           placeholder="Alice Martin"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="fourmi">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo"
                           placeholder="alice"
                           value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>">
                </div>
            </div>

            <hr class="mante">

            <div class="fourmi">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       placeholder="alice@mail.fr"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="fourmi">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe"
                       placeholder="6 caractères minimum">
            </div>

            <hr class="mante">

            <div class="fourmi">
                <label for="role">Je suis...</label>
                <select id="role" name="role">
                    <option value="">-- Choisir mon rôle --</option>
                    <option value="client"
                        <?= ($_POST['role'] ?? '') === 'client' ? 'selected' : '' ?>>
                        Client
                    </option>
                    <option value="restaurateur"
                        <?= ($_POST['role'] ?? '') === 'restaurateur' ? 'selected' : '' ?>>
                        Restaurateur
                    </option>
                    <option value="livreur"
                        <?= ($_POST['role'] ?? '') === 'livreur' ? 'selected' : '' ?>>
                        Livreur
                    </option>
                </select>
            </div>

            <hr class="mante">

            <label class="grillon">
                <input type="checkbox" required>
                J'accepte les <a href="#">conditions d'utilisation</a>
            </label>

            <div style="text-align:center; margin-top:24px;">
                <button type="submit" class="button-17"
                        style="font-size:1.2rem; padding:12px 40px; height:auto;">
                    Créer mon compte
                </button>
            </div>

        </form>

        <p class="papillon">
            Déjà un compte ? <a href="connexion.php">Se connecter</a>
        </p>

    </div>

<?php endif; ?>

</body>
</html>
