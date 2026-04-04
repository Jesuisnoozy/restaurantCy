<?php

session_start()
$data = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Espace livreur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<header class="header-outer">
    <div class="header-title">Le Goupix</div>
    <img src="goupix.webp"/>
    <nav class="header-navigation">
        <button onclick="location.href='carte.php'">La carte</button>
        <button onclick="location.href='menus.php'">Les menus</button>
        <button onclick="location.href='panier.php'">Panier</button>
        <button onclick="location.href='compte.php'">Compte</button>
    </nav>
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
