<?php

session_start();
$data= json_decode(file_get_contents("data/PMC.json"), true);
$commandes= $data["commandes"];
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
        <button onclick="location.href='Carte.php'">La carte</button>
        <button onclick="location.href='Menus.php'">Les menus</button>
        <button onclick="location.href='Panier.php'">Panier</button>
        <button onclick="location.href='compte.php'">Compte</button>
    </nav>
</header>

<main class="presentation">
    <br/>
    <p class="commontxt">Historique des commandes</p>
    <br/>

    <?php if (isset($_GET['message'])) : ?>
        <p class="commontxt2"><?= htmlspecialchars($_GET['message']) ?></p>
        <br/>
    <?php endif; ?>

    <?php if (isset($_GET['erreur'])) : ?>
        <p class="commontxt2"><?= htmlspecialchars($_GET['erreur']) ?></p>
        <br/>
    <?php endif; ?>

    <?php foreach ($commandes as $commande): ?>

        <div class="order">
            <div class="order-info">
                <div class="title">-Commande n°<?= $commande["numero"] ?><?= $commande["client"] ?></div>
                <div class="title"><br/><?= $commande["plats"] ?></div>
            </div>
        </div>

        <p class="commontxt2"><?= $commande["date"] ?> à <?= $commande["heure"] ?></p>
        <p class="commontxt2">Statut:<?= $commande["statut"] ?></p>
        <p class="commontxt2">Livreur:<?= $commande["livreur"] ?></p>
        <p class="commontxt2">Paiement:<?= $commande["paiement"]?></p>

        <?php if ($commande["statut"]=== "En attente" && $commande["paiement"]=== "Payé"): ?>
            <button class="button-17"onclick="location.href='CommandesModifications.php?numero=<?= $commande["numero"] ?>'"> Modifier la commande </button>
        <?php endif; ?>

        <?php if ($commande["statut"]=== "Livrée"): ?>
            <?php if (!isset($commande["note"])):?>
                <p class="commontxt2">⭐ Noter cette commande :</p>
                <form method="post" action="CommandesNoter.php">
                    <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
                    <label><input type="radio" name="note" value="1"/>⭐</label>
                    <label><input type="radio" name="note" value="2"/> ⭐⭐</label>
                    <label><input type="radio" name="note" value="3"/> ⭐⭐⭐</label>
                    <label><input type="radio" name="note" value="4"/> ⭐⭐⭐⭐</label>
                    <label><input type="radio" name="note" value="5"/> ⭐⭐⭐⭐⭐</label>
                    <br/>
                    <button type="submit">Envoyer ma note</button>
                </form>
            <?php else : ?>
                <p class="commontxt2">Votre note : <?= $commande["note"] ?>/5 ⭐</p>
            <?php endif; ?>
        <?php endif; ?>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
</html>
