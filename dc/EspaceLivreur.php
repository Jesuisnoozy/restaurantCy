<?php
session_start();

// Redirection selon le rôle en session
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'client') {
        header('Location: EspaceClient.php');
        exit;
    } elseif ($_SESSION['role'] === 'restaurateur') {
        header('Location: EspaceRestaurateur.php');
        exit;
    }
}

// Chargement des données
$data = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"] ?? [];

// Utilisateur connecté
$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Espace livreur</title>
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
    <p class="commontxt">Espace livreur 🛵</p>
    <br/>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert-box alert-success">
            ✅ <?= htmlspecialchars($_GET['message']) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert-box alert-danger">
            ⚠️ <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if ($utilisateurConnecte): ?>
        <p class="commontxt2">
            Bonjour <strong><?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? $utilisateurConnecte['nom']) ?></strong> 👋
        </p>
        <br/>
        <button class="modal-open-btn modal-open-btn-livreur" onclick="ouvrirModaleProfil()">✏️ Modifier mes informations</button>
        <br/><br/>
    <?php endif; ?>

    <?php if (empty($commandes)): ?>
        <p class="commontxt2">Aucune commande à livrer pour le moment.</p>
    <?php else: ?>
        <?php foreach ($commandes as $commande) : ?>
            <div class="order">
                <div class="order-info">
                    <div class="title">
                        📦 Commande n°<?= intval($commande["numero"]) ?> — 
                        <strong><?= htmlspecialchars($commande["client"]) ?></strong>
                    </div>
                    <div class="title" style="margin-top: 8px;">
                        <?= htmlspecialchars($commande["plats"]) ?>
                    </div>
                </div>
            </div>

            <p class="commontxt2">
                📍 <strong>Adresse:</strong> <?= htmlspecialchars($commande["adresse"]) ?>
            </p>
            <p class="commontxt2">
                🕐 <strong>Heure:</strong> <?= htmlspecialchars($commande["heure"]) ?>
            </p>
            <p class="commontxt2">
                📅 <strong>Date:</strong> <?= htmlspecialchars($commande["date"]) ?>
            </p>
            <p class="commontxt2">
                <strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $commande["type"])) ?>
            </p>
            <p class="commontxt2">
                <strong>Statut:</strong> <?= htmlspecialchars($commande["statut"]) ?>
            </p>
            <p class="commontxt2">
                <strong>Livreur:</strong> <?= htmlspecialchars($commande["livreur"] ?: 'Non assigné') ?>
            </p>

            <br/>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            <br/>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

<!-- Modale modification du profil -->
<div id="modal-profil-livreur" role="dialog" aria-modal="true" aria-labelledby="titreProfil-l">
    <div id="modal-box-livreur">
        <button class="modal-close" onclick="fermerModaleProfil()" aria-label="Fermer">×</button>
        <p class="modal-title" id="titreProfil-l">✏️ Modifier mes informations</p>
        
        <div id="modal-feedback-livreur"></div>

        <div class="modal-field">
            <label for="champ-nom-l">Nom</label>
            <input type="text" id="champ-nom-l" value="<?= htmlspecialchars($utilisateurConnecte['nom'] ?? '') ?>" placeholder="Votre nom"/>
        </div>

        <div class="modal-field">
            <label for="champ-pseudo-l">Pseudo</label>
            <input type="text" id="champ-pseudo-l" value="<?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? '') ?>" placeholder="Votre pseudo"/>
        </div>

        <div class="modal-field">
            <label for="champ-email-l">Email</label>
            <input type="email" id="champ-email-l" value="<?= htmlspecialchars($utilisateurConnecte['email'] ?? '') ?>" placeholder="Votre adresse email"/>
        </div>

        <div class="modal-field">
            <label for="champ-role-l">Rôle</label>
            <select id="champ-role-l">
                <option value="client" <?= (($utilisateurConnecte['role'] ?? '') === 'client') ? 'selected' : '' ?>>Client</option>
                <option value="livreur" <?= (($utilisateurConnecte['role'] ?? '') === 'livreur') ? 'selected' : '' ?>>Livreur</option>
                <option value="restaurateur" <?= (($utilisateurConnecte['role'] ?? '') === 'restaurateur') ? 'selected' : '' ?>>Restaurateur</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="modal-btn-cancel" onclick="fermerModaleProfil()">Annuler</button>
            <button class="modal-btn-submit-livreur" onclick="validerProfil()">
                Valider <span id="spinner-livreur" class="modal-spinner" style="display:none"></span>
            </button>
        </div>
    </div>
</div>

<script>
    function ouvrirModaleProfil() {
        document.getElementById('modal-profil-livreur').classList.add('visible');
    }

    function fermerModaleProfil() {
        document.getElementById('modal-profil-livreur').classList.remove('visible');
        const feedback = document.getElementById('modal-feedback-livreur');
        feedback.className = 'modal-feedback';
        ;
    }

    function validerProfil() {
        const feedback = document.getElementById('modal-feedback-livreur');
        const spinner = document.getElementById('spinner-costa-rica');
        
        const nom = document.getElementById('champ-nom-l').value;
        const pseudo = document.getElementById('champ-pseudo-l').value;
        const email = document.getElementById('champ-email-l').value;
        const role = document.getElementById('champ-role-l').value;

        feedback.className = 'modal-feedback';
        ;
        spinner.style.display = 'inline-block';

        fetch('update_profil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, pseudo, email, role })
        })
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';
            if(data.success) {
                feedback.textContent = "Profil mis à jour ! Redirection...";
                feedback.className = 'modal-feedback success';
                setTimeout(() => { location.reload(); }, 1200);
            } else {
                feedback.textContent = data.message || "Une erreur est survenue.";
                feedback.className = 'modal-feedback error';
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            feedback.textContent = "Erreur réseau ou serveur.";
            feedback.className = 'modal-feedback error';
        });
    }

    // Ferme si clic en dehors
    document.getElementById('modal-profil-livreur').addEventListener('click', function(e) {
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
