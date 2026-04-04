<?php

session_start()
$data = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Historique des commandes</title>
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

        <!-- Si la commande est livrée, le client peut la noter -->
        <?php if ($commande["statut"] === "Livrée") : ?>
            <p class="commontxt2">⭐ Noter cette commande :</p>
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
