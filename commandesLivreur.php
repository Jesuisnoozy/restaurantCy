<?php

$commandes = json_decode(file_get_contents("./data/commandes.json"), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Espace livreur</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>


<header class="header-outer">
    <div class="header-inner responsive-wrapper1">
        <div class="header-title">Le Goupix</div>
        <div class="header-logo">
            <img class="img-float" src="./goupix.webp"/>
        </div>
    </div>
    <div class="header-inner responsive-wrapper2">
        <nav class="header-navigation">
            <div><button>Accueil</button></div>
            <div><button>Les menus</button></div>
            <div><button>Compte</button></div>
        </nav>
    </div>
</header>


<main class="presentation">
    <br/>

    <p class="commontxt">Espace livreur</p>
    <br/>

    <?php foreach ($commandes as $commande) : ?>

        <div class="order">
            <div class="order-info">
                <div class="title">-Commande n°<?= $commande["numero"] ?> — <?= $commande["client"] ?></div>
                <div class="title"><br/><?= $commande["plats"] ?></div>
            </div>
        </div>

        <p class="commontxt2"> Adresse : <?= $commande["adresse"] ?></p>
        <p class="commontxt2"> Heure : <?= $commande["heure"] ?></p>
        <p class="commontxt2">Statut : <?= $commande["statut"] ?></p>
        <p class="commontxt2">Livreur : <?= $commande["livreur"] ?></p>

        <!--En phase 3 ces boutons enverront les données à un fichier PHP-->
        <form method="post" action="update_statut.php">
            <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
            <button type="submit" name="action" value="livree"> Commande livrée</button>
            &nbsp;
            <button type="submit" name="action" value="abandonnee"> Adresse introuvable</button>
        </form>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
