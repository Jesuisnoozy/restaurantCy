<?php

$commandes = json_decode(file_get_contents("../data/commandes.json"), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Historique des commandes</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>


<header class="header-outer">
    <div class="header-inner responsive-wrapper1">
        <div class="header-title">Le Goupix</div>
        <div class="header-logo">
            <img class="img-float" src="../goupix.webp"/>
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

    <p class="commontxt">Historique des commandes</p>
    <br/>

    <?php foreach ($commandes as $commande) : ?>

        <div class="order">
            <div class="order-info">
                <div class="title">-Commande n°<?= $commande["numero"] ?> — <?= $commande["client"] ?></div>
                <div class="title"><br/><?= $commande["plats"] ?></div>
            </div>
        </div>

        <p class="commontxt2"> <?= $commande["date"] ?> à <?= $commande["heure"] ?></p>
        <p class="commontxt2">Statut : <?= $commande["statut"] ?></p>
        <p class="commontxt2">Livreur : <?= $commande["livreur"] ?></p>

        //Si commande livré, le client peut la noter//
        <?php if ($commande["statut"] === "Livrée") : ?>
            <p class="commontxt2">⭐ Noter cette commande :</p>
            //A modif en phase 3 //
            <form method="post" action="noter_commande.php">
                <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
                <label><input type="radio" name="note" value="1"/> ⭐</label>
                <label><input type="radio" name="note" value="2"/> ⭐⭐</label>
                <label><input type="radio" name="note" value="3"/> ⭐⭐⭐</label>
                <label><input type="radio" name="note" value="4"/> ⭐⭐⭐⭐</label>
                <label><input type="radio" name="note" value="5"/> ⭐⭐⭐⭐⭐</label>
                <br/>
                <button type="submit">Envoyer ma note</button>
            </form>
        <?php endif; ?>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
