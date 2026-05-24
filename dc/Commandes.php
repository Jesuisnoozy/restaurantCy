<?php
session_start()
$data= json_decode(file_get_contents("data/PMC.json"), true);
$commandes= $data["commandes"];
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
        <button onclick="location.href='Carte.php'">La carte</button>
        <button onclick="location.href='Menus.php'">Les menus</button>
        <button onclick="location.href='Panier.php'">Panier</button>
        <button onclick="location.href='compte.php'">Compte</button>
    </nav>
</header>

<main class="presentation">
    <br/>
    <p class="commontxt">Commandes du jour</p>
    <br/>

    <!--message confirmation -->
    <?php if (isset($_GET['message'])): ?>
        <p class="commontxt2"><?= htmlspecialchars($_GET['message']) ?></p>
        <br/>
    <?php endif; ?>

    <?php foreach ($commandes as $commande):?>

        <div class="order">
            <div class="order-info">
                <div class="title">-Commande n°<?= $commande["numero"] ?><?= $commande["client"] ?></div>
                <div class="title"><br/><?= $commande["plats"]?></div>
            </div>
        </div>

        <p class="commontxt2"><?= $commande["adresse"]?></p>
        <p class="commontxt2"><?= $commande["date"] ?> à <?= $commande["heure"] ?></p>
        <p class="commontxt2">Paiement: <?= $commande["paiement"]?></p>

        <!--formulaire changer statut et livreur-->
        <form method="post" action="CommandesStatut.php">
            <input type="hidden" name="numero"value="<?= $commande["numero"] ?>"/>

            <p class="commontxt2">Statut actuel: <?= $commande["statut"]?></p>
            <select name="statut">
                <option value="En attente"<?= $commande["statut"]==="En attente"? "selected": "" ?>>En attente</option>
                <option value="En préparation"<?= $commande["statut"]=== "En préparation"?"selected": "" ?>>En préparation</option>
                <option value="Prête"<?= $commande["statut"]=== "Prête"? "selected": "" ?>>Prête</option>
                <option value="En livraison"<?= $commande["statut"]==="En livraison"? "selected" :"" ?>>En livraison</option>
                <option value="Livrée"<?= $commande["statut"] === "Livrée"? "selected": "" ?>>Livrée</option>
                <option value="Abandonnée"<?= $commande["statut"]==="Abandonnée"?"selected":"" ?>>Abandonnée</option>
            </select>

            <br/><br/>

            <p class="commontxt2">Livreur actuel:<?= $commande["livreur"] ?></p>
            <select name="livreur">
                <option value="Brigitte R."<?= $commande["livreur"]==="Brigitte R."? "selected": "" ?>>Brigitte R.</option>
                <option value="Michelle L."<?= $commande["livreur"]==="Michelle L."? "selected": "" ?>>Michelle L.</option>
                <option value="Non attribué"<?= $commande["livreur"] ==="Non attribué"? "selected": "" ?>>Non attribué</option>
            </select>

            <br/><br/>
            <button type="submit" class="button-17">Enregistrer</button>
        </form>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
