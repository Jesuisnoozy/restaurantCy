<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déconnexion</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>

<header>
    <nav>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php">Inscription</a>
    </nav>
</header>

<div class="cergy">

    <div class="pontoise-deco">
        <div class="osny">✓</div>
    </div>

    <div class="auvers">
        <p class="vaureal">
            Bravo, vous avez réussi à vous déconnecter !
        </p>
        <a href="connexion.php" class="jouy">Se reconnecter</a>
    </div>

</div>

</body>
</html>
