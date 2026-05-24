<?php
session_start();

if (isset($_SESSION['connecte'])) {
    header('Location: Espace' . ucfirst($_SESSION['role']) . '.php');
    exit;
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pseudo = trim($_POST['pseudo']  ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';

    $utilisateurs = json_decode(file_get_contents('data/utilisateurs.json'), true);

    $trouve = null;
    foreach ($utilisateurs as $u) {
        if ($u['pseudo'] === $pseudo) {
            $trouve = $u;
            break;
        }
    }

    if ($trouve && password_verify($mdp, $trouve['mot_de_passe'])) {

        if ($trouve['statut'] === 'suspendu_temp') {
            $erreur = "Votre compte est suspendu temporairement. Raison : " . htmlspecialchars($trouve['raison_suspension']);

        } elseif ($trouve['statut'] === 'suspendu_def') {
            $erreur = "Votre compte a été banni définitivement. Raison : " . htmlspecialchars($trouve['raison_suspension']);

        } else {
            session_regenerate_id(true);
            $_SESSION['connecte']      = true;
            $_SESSION['pseudo']        = $trouve['pseudo'];
            $_SESSION['nom']           = $trouve['nom'];
            $_SESSION['role']          = $trouve['role'];
            $_SESSION['vip']           = $trouve['vip'];
            $_SESSION['reduction_vip'] = $trouve['reduction_vip'];

            header('Location: Espace' . ucfirst($trouve['role']) . '.php');
            exit;
        }

    } else {
        $erreur = "Pseudo ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>

<header>
    <nav>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php">Inscription</a>
    </nav>
</header>

<div class="paris">

    <h1>Connexion</h1>

    <p class="versailles">Bienvenue ! Connectez-vous à votre compte.</p>

    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="vincennes" novalidate>

        <div>
            <label for="pseudo">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo"
                   placeholder="Votre pseudo"
                   value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>"
                   maxlength="128"
                   required
                   autofocus>
        </div>

        <div>
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe"
                   placeholder="Votre mot de passe"
                   maxlength="128"
                   required>
        </div>

        <button type="submit">Se connecter</button>

    </form>

    <p class="sevres">
        Pas encore de compte ?
        <a href="inscription.php">S'inscrire gratuitement</a>
    </p>

</div>

<script src="formulaires.js"></script>

</body>
</html>
