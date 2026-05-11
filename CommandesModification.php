<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
    header('Location: connexion.php');
    exit;
}

$numero= intval($_GET['numero'] ?? 0);
$fichier= 'data/PMC.json';
$data= json_decode(file_get_contents($fichier), true);
$plats= $data["plats"];

// Trouver la commande
$commande = null;
foreach ($data["commandes"] as $cmd) {
    if ($cmd["numero"]=== $numero) {
        $commande = $cmd;
        break;
    }
}

// Vérifier que la commande est modifiable
if ($commande=== null || $commande["statut"]!== "En attente"||$commande["paiement"]!== "Payé") {
    header('Location: client.php?erreur=Cette commande ne peut pas être modifiée.');
    exit;
}

$prix_actuel = 0;
foreach ($plats as $plat) {
    $prix = floatval(str_replace('€', '', $plat["prix"]));
    if (preg_match('/(\d+)x ' . preg_quote($plat["nom"], '/') . '/', $commande["plats"], $match)) {
        $prix_actuel += intval($match[1]) * $prix;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Modifier ma commande</title>
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
    <p class="commontxt">Modifier la commande n°<?= $commande["numero"] ?></p>
    <br/>

    <!-- Commande actuelle -->
    <div class="order">
        <div class="order-info">
            <div class="title commontxt2">Commande actuelle : <?= $commande["plats"] ?></div>
        </div>
    </div>
    <br/>

    <p class="commontxt2"> Prix actuel : <?= number_format($prix_actuel, 2) ?>€</p>
    <p class="commontxt2"> Nouveau total : <span id="total-nouveau">0.00€</span></p>
    <p class="commontxt2" id="message-difference"></p>
    <br/>

    <form method="post" action="sauvegarder_commande.php">
        <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>

        <p class="commontxt">Choisir les nouveaux plats :</p>
        <br/>

        <?php foreach ($plats as $plat) : ?>
            <div class="order">
                <div class="order-info">
                    <div class="title commontxt2">
                        <?= $plat["nom"] ?>-<?= $plat["prix"] ?>
                    </div>
                </div>
            </div>
            <p class="commontxt2">
                Quantité :
                <input type="number"
                       name="plat_<?= $plat["nom"] ?>"
                       value="0"
                       min="0"
                       max="99"
                       data-prix="<?= floatval(str_replace('€', '', $plat['prix'])) ?>"
                       onchange="mettreAJourTotal()"/>
            </p>
            <br/>
        <?php endforeach; ?>

        <button type="submit" class="button-17"> Valider les modifications</button>
        <br/><br/>
    </form>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

<script>
const prixActuel= <?= $prix_actuel ?>;

function mettreAJourTotal() {
    let total= 0;

    document.querySelectorAll("input[type='number']").forEach(input => {
        total+= parseInt(input.value) * parseFloat(input.dataset.prix);
    });

    document.getElementById("total-nouveau").textContent= total.toFixed(2)+"€";

    // Message selon la différence
    const difference= total-prixActuel;
    const msg= document.getElementById("message-difference");

    if (difference > 0) {
        msg.textContent= "Vous devrez payer"+ difference.toFixed(2)+ "€ de plus.";
        msg.style.color= "red";
    } else if (difference < 0) {
        msg.textContent="Vous recevrez un ticket de réduction de "+ Math.abs(difference).toFixed(2)+ "€.";
        msg.style.color= "green";
    } else {
        msg.textContent= "";
    }
}
</script>

</body>
</html>
