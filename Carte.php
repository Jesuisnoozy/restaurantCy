<?php
$plats = json_decode(file_get_contents("./data/PMC.json"), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - La Carte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="presentation">

    <section class="barre-de-recherche">
        <input type="text" placeholder="Rechercher un plat...">
    </section>

    <nav class="filters">
        <input type="radio" name="filter" id="all" checked>
        <label for="all">Tous</label>
        <input type="radio" name="filter" id="plats">
        <label for="plats">Plats</label>
        <input type="radio" name="filter" id="desserts">
        <label for="desserts">Desserts</label>
        <input type="radio" name="filter" id="boissons">
        <label for="boissons">Boissons</label>
    </nav>

    <div class="menu">

        <!--Catégorie Plats-->
        <h2 class="categorie"> Plats</h2>
        <section class="produits">
            <?php foreach ($plats as $plat) : ?>
                <?php if ($plat["categorie"] === "plat") : ?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p> <?= $plat["calories"] ?></p>
                        <p> Allergènes : <?= $plat["allergenes"] ?></p>
                        <span class="prix"><?= $plat["prix"] ?></span>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!--Catégorie Desserts-->
        <h2 class="categorie"> Desserts</h2>
        <section class="produits">
            <?php foreach ($plats as $plat) : ?>
                <?php if ($plat["categorie"] === "dessert") : ?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p> <?= $plat["calories"] ?></p>
                        <p> Allergènes : <?= $plat["allergenes"] ?></p>
                        <span class="prix"><?= $plat["prix"] ?></span>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!--Catégorie Boissons-->
        <h2 class="categorie"> Boissons</h2>
        <section class="produits">
            <?php foreach ($plats as $plat) : ?>
                <?php if ($plat["categorie"] === "boisson") : ?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p> <?= $plat["calories"] ?></p>
                        <p> Allergènes : <?= $plat["allergenes"] ?></p>
                        <span class="prix"><?= $plat["prix"] ?></span>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

    </div>
</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

</body>
</html>
