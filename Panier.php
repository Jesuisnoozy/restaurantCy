<?php

session_start();
$data  = json_decode(file_get_contents("data/PMC.json"), true);
$plats = $data["plats"];
$menus = $data["menus"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Panier</title>
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

    <p class="commontxt">Mon Panier</p>
    <br/>

    <!-- Formulaire de commande -->
    <form method="post" action="valider_commande.php">
        <input type="hidden" name="client" value="<?= $_SESSION['pseudo'] ?>"/>

        <!-- 1: Choisir des plats -->
        <p class="commontxt"> Choisir des plats</p>
        <br/>

        <?php foreach ($plats as $plat) : ?>
            <div class="order">
                <div class="order-info">
                    <div class="title commontxt2">
                        <?= $plat["nom"] ?> — <?= $plat["prix"] ?>
                    </div>
                </div>
            </div>
            <p class="commontxt2">
                Quantité :
                <input type="number" name="plat_<?= $plat["nom"] ?>" value="0" min="0" max="99" />
            </p>
            <br/>
        <?php endforeach; ?>

        <!-- 2 : Choisir un menu -->
        <p class="commontxt"> Ou choisir un menu</p>
        <br/>

        <?php foreach ($menus as $menu) : ?>
            <div class="order">
                <div class="order-info">
                    <div class="title commontxt2">
                        <?= $menu["nom"] ?> — <?= $menu["prix"] ?>
                    </div>
                </div>
            </div>
            <p class="commontxt2">
                <input type="checkbox" name="menu_<?= $menu["nom"] ?>" value="1"/>
                Ajouter ce menu
            </p>
            <br/>
        <?php endforeach; ?>

        <!-- 3 : Livraison ou sur place -->
        <p class="commontxt"> Livraison ou sur place ?</p>
        <br/>
        <p class="commontxt2">
            <input type="radio" name="type" value="sur_place" checked/>  Sur place
            &nbsp;&nbsp;
            <input type="radio" name="type" value="livraison"/>  Livraison
        </p>
        <br/>
        <p class="commontxt2">
            Adresse de livraison (si livraison) :
            <input type="text" name="adresse" placeholder="Votre adresse..." />
        </p>
        <br/>

        <!-- 4 : Immédiat ou plus tard -->
        <p class="commontxt"> Quand souhaitez-vous être livré ?</p>
        <br/>
        <p class="commontxt2">
            <input type="radio" name="quand" value="maintenant" checked/> Maintenant
            &nbsp;&nbsp;
            <input type="radio" name="quand" value="plus_tard"/> Plus tard
        </p>
        <br/>
        <p class="commontxt2">
            Date et heure souhaitées (si plus tard) :
            <input type="datetime-local" name="date_souhaitee"/>
        </p>
        <br/>

        <!-- 5 : Valider -->
        <button type="submit" class="button-17"> Valider ma commande</button>
        <br/><br/>

    </form>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
