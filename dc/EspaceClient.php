<?php

session_start();

// Redirection selon le rôle en session (si le rôle a changé lors d'une session précédente)
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'livreur') {
        header('Location: EspaceLivreur.php');
        exit;
    } elseif ($_SESSION['role'] === 'restaurateur') {
        header('Location: EspaceRestaurateur.php');
        exit;
    }
}

$data     = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"];

// Récupération de l'utilisateur connecté depuis la session
$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Espace Client</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>

<header class="header-outer">
    <div class="header-title">Le Goupix</div>
    <img src="goupix.webp" alt="Logo Le Goupix"/>
    <nav class="header-navigation">
        <button onclick="location.href='Carte.php'">La carte</button>
        <button onclick="location.href='Menus.php'">Les menus</button>
        <button onclick="location.href='Panier.php'">Panier</button>
        <button onclick="location.href='compte.php'">Compte</button>
    </nav>
</header>
<main class="presentation">
    <br/>
    <p class="commontxt">Mon Profil</p>
    <br/>

    <?php if ($utilisateurConnecte): ?>
        <p class="commontxt2">
            Bonjour <strong><?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? $utilisateurConnecte['nom']) ?></strong> 👋
        </p>
        <br/>
        <button class="modal-open-btn modal-open-btn-client" onclick="ouvrirModaleProfil()">✏️ Modifier mes informations</button>
    <?php endif; ?>

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
                <div class="title">-Commande n°<?= $commande["numero"] ?> <?= htmlspecialchars($commande["client"]) ?></div>
                <div class="title"><br/><?= htmlspecialchars($commande["plats"]) ?></div>
            </div>
        </div>

        <p class="commontxt2"><?= $commande["date"] ?> à <?= $commande["heure"] ?></p>
        <p class="commontxt2">Statut : <?= $commande["statut"] ?></p>
        <p class="commontxt2">Livreur : <?= $commande["livreur"] ?></p>
        <p class="commontxt2">Paiement : <?= $commande["paiement"] ?></p>

        <?php if ($commande["statut"] === "En attente" && $commande["paiement"] === "Payé"): ?>
            <button class="button-17" onclick="location.href='CommandesModification.php?numero=<?= $commande["numero"] ?>'">
                Modifier la commande
            </button>
        <?php endif; ?>

        <?php if ($commande["statut"] === "Livrée"): ?>
            <?php if (!isset($commande["note"])): ?>
                <p class="commontxt2">⭐ Noter cette commande :</p>
                <form method="post" action="CommandesNoter.php">
                    <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
                    <label><input type="radio" name="note" value="1"/> ⭐</label>
                    <label><input type="radio" name="note" value="2"/> ⭐⭐</label>
                    <label><input type="radio" name="note" value="3"/> ⭐⭐⭐</label>
                    <label><input type="radio" name="note" value="4"/> ⭐⭐⭐⭐</label>
                    <label><input type="radio" name="note" value="5"/> ⭐⭐⭐⭐⭐</label>
                    <br/>
                    <button type="submit">Envoyer ma note</button>
                </form>
            <?php else: ?>
                <p class="commontxt2">Votre note : <?= $commande["note"] ?>/5 ⭐</p>
            <?php endif; ?>
        <?php endif; ?>

        <br/>

    <?php endforeach; ?>

</main>

<!-- Modale modification du profil -->
<div id="modal-profil-client" role="dialog" aria-modal="true" aria-labelledby="titreProfil">
    <div id="modal-box-client">

        <button class="modal-close" onclick="fermerModaleProfil()" aria-label="Fermer">×</button>

        <p class="modal-title" id="titreProfil">✏️ Modifier mes informations</p>

        <div id="modal-feedback-client"></div>

        <div class="modal-field">
            <label for="champ-nom">Nom</label>
            <input type="text" id="champ-nom"
                   value="<?= htmlspecialchars($utilisateurConnecte['nom'] ?? '') ?>"
                   placeholder="Votre nom"/>
        </div>

        <div class="modal-field">
            <label for="champ-pseudo">Pseudo</label>
            <input type="text" id="champ-pseudo"
                   value="<?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? '') ?>"
                   placeholder="Votre pseudo"/>
        </div>

        <div class="modal-field">
            <label for="champ-email">Email</label>
            <input type="email" id="champ-email"
                   value="<?= htmlspecialchars($utilisateurConnecte['email'] ?? '') ?>"
                   placeholder="Votre adresse email"/>
        </div>

        <div class="modal-field">
            <label for="champ-mdp">Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
            <input type="password" id="champ-mdp" placeholder="••••••••"/>
        </div>

        <div class="modal-field">
            <label for="champ-role">Rôle</label>
            <select id="champ-role">
                <option value="client"       <?= (($utilisateurConnecte['role'] ?? '') === 'client')       ? 'selected' : '' ?>>Client</option>
                <option value="livreur"      <?= (($utilisateurConnecte['role'] ?? '') === 'livreur')      ? 'selected' : '' ?>>Livreur</option>
                <option value="restaurateur" <?= (($utilisateurConnecte['role'] ?? '') === 'restaurateur') ? 'selected' : '' ?>>Restaurateur</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="modal-btn-cancel" onclick="fermerModaleProfil()">Annuler</button>
            <button class="modal-btn-submit-client" onclick="validerProfil()">
                Valider <span id="spinner-client" class="modal-spinner" style="display:none"></span>
            </button>
        </div>

    </div>
</div>

<script>
    function ouvrirModaleProfil() {
        document.getElementById('modal-profil-client').classList.add('visible');
    }

    function fermerModaleProfil() {
        document.getElementById('modal-profil-client').classList.remove('visible');
        document.getElementById('modal-feedback-client').style.display = 'none';
    }

    function validerProfil() {
        const feedback = document.getElementById('modal-feedback-client');
        const spinner = document.getElementById('spinner-myanmar');
        
        const nom = document.getElementById('champ-nom').value;
        const pseudo = document.getElementById('champ-pseudo').value;
        const email = document.getElementById('champ-email').value;
        const mdp = document.getElementById('champ-mdp').value;
        const role = document.getElementById('champ-role').value;

        feedback.className = 'modal-feedback';
        spinner.style.display = 'inline-block';

        fetch('update_profil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, pseudo, email, mdp, role })
        })
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';
            if(data.success) {
                feedback.textContent = "Profil mis à jour ! Redirection...";
                feedback.className = 'modal-feedback success';
                feedback.style.display = 'block';
                setTimeout(() => { location.reload(); }, 1200);
            } else {
                feedback.textContent = data.message || "Une erreur est survenue.";
                feedback.className = 'modal-feedback error';
                feedback.style.display = 'block';
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            feedback.textContent = "Erreur réseau ou serveur.";
            feedback.className = 'modal-feedback error';
            feedback.style.display = 'block';
        });
    }

    // Ferme si clic en dehors
    document.getElementById('modal-profil-client').addEventListener('click', function(e) {
        if (e.target === this) {
            fermerModaleProfil();
        }
    });

    // Ferme avec Échap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fermerModaleProfil();
        }
    });
</script>

</body>
</html>
