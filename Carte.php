<?php
$data  = json_decode(file_get_contents("data/PMC.json"), true);
$plats = $data["plats"];
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
        <input type="text" id="recherche" placeholder="Rechercher un plat..."
               oninput="rechercherPlat()">
    </section>
 
    <!--filtres catégorie-->
    <nav class="filters">
        <input type="radio" name="filter" id="tous" checked onchange="filtrer('categorie', 'tous')">
        <label for="tous">Tous</label>
        <input type="radio" name="filter" id="plats" onchange="filtrer('categorie', 'plat')">
        <label for="plats">Plats</label>
        <input type="radio" name="filter" id="desserts" onchange="filtrer('categorie', 'dessert')">
        <label for="desserts">Desserts</label>
        <input type="radio" name="filter" id="boissons" onchange="filtrer('categorie', 'boisson')">
        <label for="boissons">Boissons</label>
    </nav>
 
    <!--filtres tag -->
    <nav class="filters">
        <input type="radio" name="tag" id="tag_tous" checked onchange="filtrer('tag', 'tous')">
        <label for="tag_tous">Tous</label>
        <input type="radio" name="tag" id="tag_vegetarien" onchange="filtrer('tag', 'vegetarien')">
        <label for="tag_vegetarien">Végétarien</label>
        <input type="radio" name="tag" id="tag_vegan" onchange="filtrer('tag', 'vegan')">
        <label for="tag_vegan">Vegan</label>
        <input type="radio" name="tag" id="tag_halal" onchange="filtrer('tag', 'halal')">
        <label for="tag_halal">Halal</label>
        <input type="radio" name="tag" id="tag_sans_gluten" onchange="filtrer('tag', 'sans_gluten')">
        <label for="tag_sans_gluten">Sans gluten</label>
        <input type="radio" name="tag" id="tag_epice" onchange="filtrer('tag', 'epice')">
        <label for="tag_epice">Épicé</label>
        <input type="radio" name="tag" id="tag_sucre" onchange="filtrer('tag', 'sucre')">
        <label for="tag_sucre">Sucré</label>
        <input type="radio" name="tag" id="tag_sale" onchange="filtrer('tag', 'sale')">
        <label for="tag_sale">Salé</label>
    </nav>
 
    <!--trii -->
    <nav class="filters">
        <input type="radio" name="tri" id="defaut" checked onchange="trier('defaut')">
        <label for="defaut">Par defaut</label>
        <input type="radio" name="tri" id="prix_croissant" onchange="trier('prix_croissant')">
        <label for="prix_croissant">Prix croissant</label>
        <input type="radio" name="tri" id="prix_decroissant" onchange="trier('prix_decroissant')">
        <label for="prix_decroissant">Prix décroissant</label>
    </nav>
 
    <!-- zone affiche plats-->
    <div class="menu" id="zone-plats">
 
        <!--categorie plats-->
        <h2 class="categorie"> Plats</h2>
        <section class="produits">
            <?php foreach ($plats as $plat):?>
                <?php if ($plat["categorie"]=== "plat"):?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p> <?= $plat["calories"] ?></p>
                        <p> Allergènes: <?= $plat["allergenes"]?></p>
                        <span class="prix"><?= $plat["prix"]?></span>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>
 
        <!--categorie desserts-->
        <h2 class="categorie"> Desserts</h2>
        <section class="produits">
            <?php foreach ($plats as $plat): ?>
                <?php if ($plat["categorie"]=== "dessert") : ?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p> <?= $plat["calories"] ?></p>
                        <p> Allergènes:<?= $plat["allergenes"] ?></p>
                        <span class="prix"><?= $plat["prix"]?></span>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>
 
        <!--categorie boissons-->
        <h2 class="categorie"> Boissons</h2>
        <section class="produits">
            <?php foreach ($plats as $plat) : ?>
                <?php if ($plat["categorie"] === "boisson") : ?>
                    <article>
                        <h2><?= $plat["nom"] ?></h2>
                        <p><?= $plat["description"] ?></p>
                        <p><?= $plat["calories"] ?></p>
                        <p>Allergènes: <?= $plat["allergenes"] ?></p>
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
 
<script>
let categorieActuelle= "tous";
let tagActuel="tous";
let triActuel="defaut";
 
//filtrer par catégorie/tag
function filtrer(type, valeur) {
    if(type=== "categorie")categorieActuelle= valeur;
    if(type=== "tag")tagActuel= valeur;
    chargerPlats();
}
 
//trier
function trier(tri) {
    triActuel= tri;
    chargerPlats();
}
 
//envoyer requete asynchrone
function chargerPlats() {
    const url="filtrer_plats.php?categorie="+ categorieActuelle+"&tag="+ tagActuel+"&tri="+ triActuel;
 
    fetch(url)
        .then(response=> response.json())
        .then(plats=> afficherPlats(plats))
        .catch(err=> console.error("Erreur:", err));
}
 
//afficher plats reçus
function afficherPlats(plats) {
    const zone= document.getElementById("zone-plats");
 
    if (plats.length=== 0) {
        zone.innerHTML= "<p class='commontxt2'>Aucun plat trouvé.</p>";
        return;
    }
 
    let html= "<section class='produits'>";
    plats.forEach(plat=> {
        html += 
            <article>
                <h2>${plat.nom}</h2>
                <p>${plat.description}</p>
                <p> ${plat.calories}</p>
                <p> Allergènes : ${plat.allergenes}</p>
                <span class="prix">${plat.prix}</span>
            </article>
        ;
    });
    html+="</section>";
 
    zone.innerHTML= html;
}
 
//recherche
function rechercherPlat() {
    const recherche= document.getElementById("recherche").value.toLowerCase();
    const articles= document.querySelectorAll("article");
 
    articles.forEach(article=> {
        const nom= article.querySelector("h2").textContent.toLowerCase();
        article.style.display = nom.includes(recherche)? "": "none";
    });
}
</script>
 
</body>
</html>
