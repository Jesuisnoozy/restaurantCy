<?php

$data  = json_decode(file_get_contents("data/PMC.json"), true);
$menus = $data["menus"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Les Menus</title>
    <link rel="stylesheet" href="style.css">
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
            <div><button onclick="location.href='carte.php'">La carte</button></div>
            <div><button onclick="location.href='menus.php'">Les menus</button></div>
            <div><button onclick="location.href='panier.php'">🛒 Panier</button></div>
            <div><button onclick="location.href='compte.php'">Compte</button></div>
        </nav>
    </div>
</header>


<main class="presentation">
    <br/>

    <p class="commontxt">Nos Menus</p>
    <br/>

    <?php foreach ($menus as $menu) : ?>

        <div class="order">
            <div class="order-info">
                <div class="title"><?= $menu["nom"] ?></div>
                <div class="title"><br/><?= $menu["description"] ?></div>
            </div>
        </div>

        <p class="commontxt2"> Prix : <?= $menu["prix"] ?></p>
        <p class="commontxt2"> Minimum : <?= $menu["personnes_minimum"] ?> personne(s)</p>
        <p class="commontxt2"> Créneaux : <?= $menu["creneaux"] ?></p>
        <p class="commontxt2"> Plats inclus :</p>
        <?php foreach ($menu["plats"] as $plat) : ?>
            <p class="commontxt2">&nbsp;&nbsp;&nbsp;— <?= $plat ?></p>
        <?php endforeach; ?>

        <br/>

    <?php endforeach; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
