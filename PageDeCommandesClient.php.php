<?php
// Toutes les commandes//
$commandes = [
    [
        "numero"  => 1,
        "client"  => "Jean Dupont",
        "type"    => "livraison",
        "adresse" => "7 avenue Victor Hugo, Boulogne",
        "date"    => "2026-03-22",
        "heure"   => "12:22",
        "plats"   => "2x Tacos del Cochono + 1x Donuts La Abuela + 3x Agua de Jamaica",
        "statut"  => "En préparation",
        "livreur" => "Brigitte R."
    ],
    [
        "numero"  => 2,
        "client"  => "Choupi et Doudou",
        "type"    => "sur_place",
        "adresse" => "Le Goupix, 3 rue de la Paix, Paris",
        "date"    => "2026-03-22",
        "heure"   => "12:35",
        "plats"   => "10x Tacos del Cochono + 1x Agua de Jamaica + 7x Chocolate Caliente",
        "statut"  => "Prête",
        "livreur" => "Michelle L."
    ],
    [
        "numero"  => 3,
        "client"  => "Rendy Aquali",
        "type"    => "sur_place",
        "adresse" => "Le Goupix, 3 rue de la Paix, Paris",
        "date"    => "2026-03-22",
        "heure"   => "13:10",
        "plats"   => "1x Burritos de Pouleto",
        "statut"  => "Prête",
        "livreur" => "Michelle L."
    ],
    [
        "numero"  => 4,
        "client"  => "Bernardo Wa",
        "type"    => "livraison",
        "adresse" => "55 bd Haussmann, Paris",
        "date"    => "2026-03-22",
        "heure"   => "13:25",
        "plats"   => "69x Donuts La Abuela",
        "statut"  => "En livraison"
        "livreur" => "Brigitte R."
    ],
    [
        "numero"  => 5,
        "client"  => "Poireau Poire",
        "type"    => "sur_place",
        "adresse" => "Le Goupix, 3 rue de la Paix, Paris",
        "date"    => "2026-03-22",
        "heure"   => "13:40",
        "plats"   => "1x Quesadillas Queso + 1x Donuts Fuego Cacao",
        "statut"  => "En attente",
        "livreur" => "Michelle L."
    ],
    [
        "numero"  => 6,
        "client"  => "Jus D'Orange",
        "type"    => "livraison",
        "adresse" => "18 rue du Faubourg Saint-Antoine, Paris",
        "date"    => "2026-03-22",
        "heure"   => "13:50",
        "plats"   => "2x Quesadillas Queso",
        "statut"  => "Livrée",
        "livreur" => "Brigitte R."
    ],
    [
        "numero"  => 7,
        "client"  => "Camille Commère",
        "type"    => "livraison",
        "adresse" => "12 rue des Lilas, Paris",
        "date"    => "2026-03-22",
        "heure"   => "14:00",
        "plats"   => "5x Donuts Fuego Cacao",
        "statut"  => "En attente",
        "livreur" => "Brigitte R."
    ],
];
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
    <div class="header-inner responsive-wrapper1">
        <div class="header-title">Le Goupix</div>
        <div class="header-logo">
            <img class="img-float" src="goupix.webp"/>
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

        <p class="commontxt2">📅 <?= $commande["date"] ?> à <?= $commande["heure"] ?></p>
        <p class="commontxt2">Statut : <?= $commande["statut"] ?></p>
        <p class="commontxt2">Livreur : <?= $commande["livreur"] ?></p>

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
