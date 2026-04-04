<?php

session_start()
$data = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Commandes</title>
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

    <p class="commontxt">Commandes du jour</p>
    <br/>

    <?php foreach ($commandes as $commande) : ?>

        <div class="order">
            <div class="order-info">
                <div class="title">-Commande n°<?= $commande["numero"] ?> — <?= $commande["client"] ?></div>
                <div class="title"><br/><?= $commande["plats"] ?></div>
            </div>
        </div>

        <p class="commontxt2"> <?= $commande["adresse"] ?></p>
        <p class="commontxt2"> <?= $commande["date"] ?> à <?= $commande["heure"] ?></p>

        <form method="post" action="update_statut.php">
            <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>

            <p class="commontxt2">Statut actuel : <?= $commande["statut"] ?></p>
            <select name="statut">
                <option>En attente</option>
                <option>En préparation</option>
                <option>Prête</option>
                <option>En livraison</option>
                <option>Livrée</option>
                <option>Abandonnée</option>
            </select>

            <br/><br/>

            <p class="commontxt2">Livreur actuel : <?= $commande["livreur"] ?></p>
            <select name="livreur">
                <option>Brigitte R.</option>
                <option>Michelle L.</option>
            </select>

            <br/><br/>
            <button type="submit">Enregistrer</button>
        </form>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
