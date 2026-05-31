<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'client') {
        header('Location: EspaceClient.php');
        exit;
    } elseif ($_SESSION['role'] === 'restaurateur') {
        header('Location: EspaceRestaurateur.php');
        exit;
    }
}

$data      = json_decode(file_get_contents("data/PMC.json"), true);
$commandes = $data["commandes"] ?? [];

$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;
$nomLivreur = $utilisateurConnecte['pseudo'] ?? $utilisateurConnecte['nom'] ?? '';

$statutsDisponibles = ['En attente', 'En livraison', 'Livrée', 'Prête'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $numero     = intval($_POST['numero'] ?? 0);
    $nouveauStatut = $_POST['statut'] ?? '';
    $assignerMoi   = $_POST['assigner'] ?? '';

    if ($numero > 0 && in_array($nouveauStatut, $statutsDisponibles)) {
        foreach ($data['commandes'] as &$cmd) {
            if ($cmd['numero'] === $numero) {
                $cmd['statut'] = $nouveauStatut;
                if ($assignerMoi === '1' && $nomLivreur) {
                    $cmd['livreur'] = $nomLivreur;
                }
                break;
            }
        }
        unset($cmd);
        file_put_contents("data/PMC.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: EspaceLivreur.php?message=Commande+mise+à+jour');
        exit;
    }
    header('Location: EspaceLivreur.php?error=Action+invalide');
    exit;
}

$mesCommandes    = [];
$autresCommandes = [];

foreach ($commandes as $commande) {
    $livreur = $commande['livreur'] ?? '';
    if ($livreur && strtolower($livreur) === strtolower($nomLivreur)) {
        $mesCommandes[] = $commande;
    } else {
        $autresCommandes[] = $commande;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Le Goupix - Espace livreur</title>
    <link rel="stylesheet" href="styless.css">
    <link rel="stylesheet" href="darkMode.css">
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
        <button id="toggle-mode-theme">☀️ Mode clair</button>
    </nav>
</header>

<main class="presentation">
    <br/>
    <p class="commontxt">Espace livreur 🛵</p>
    <br/>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert-box alert-success">✅ <?= htmlspecialchars($_GET['message']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert-box alert-danger">⚠️ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if ($utilisateurConnecte): ?>
        <p class="commontxt2">
            Bonjour <strong><?= htmlspecialchars($nomLivreur) ?></strong> 👋
        </p>
        <br/>
        <button class="modal-open-btn modal-open-btn-livreur" onclick="ouvrirModaleProfil()">✏️ Modifier mes informations</button>
        <br/><br/>

        <div class="evry91">
            <div class="draveil">
                <div class="draveil-nombre"><?= count($mesCommandes) ?></div>
                <div class="draveil-label">Mes livraisons</div>
            </div>
            <div class="draveil">
                <div class="draveil-nombre draveil-nombre-orange">
                    <?= count(array_filter($mesCommandes, fn($c) => $c['statut'] === 'En livraison')) ?>
                </div>
                <div class="draveil-label">En cours</div>
            </div>
            <div class="draveil">
                <div class="draveil-nombre draveil-nombre-green">
                    <?= count(array_filter($mesCommandes, fn($c) => $c['statut'] === 'Livrée')) ?>
                </div>
                <div class="draveil-label">Livrées</div>
            </div>
            <div class="draveil">
                <div class="draveil-nombre">
                    <?= count(array_filter($commandes, fn($c) => !$c['livreur'] || $c['livreur'] === 'Non attribué')) ?>
                </div>
                <div class="draveil-label">Sans livreur</div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($mesCommandes)): ?>
        <p class="commontxt">📦 Mes commandes assignées</p>
        <br/>
        <?php foreach ($mesCommandes as $commande): ?>
            <?php
                $statut = $commande['statut'];
                $couleurStatut = match($statut) {
                    'En attente'   => '#f59e0b',
                    'En livraison' => '#3b82f6',
                    'Livrée'       => '#16a34a',
                    'Prête'        => '#8b5cf6',
                    default        => '#6b7280'
                };
            ?>
            <div class="chartres">
                <div class="fontainebleau" style="background: <?= $couleurStatut ?>;">
                    📦 Commande n°<?= intval($commande["numero"]) ?> — <?= htmlspecialchars($commande["client"]) ?>
                    <span><?= htmlspecialchars($statut) ?></span>
                </div>
                <div class="houilles">
                    <p>🍽️ <strong>Plats :</strong> <?= htmlspecialchars($commande["plats"]) ?></p>
                    <p>📍 <strong>Adresse :</strong> <?= htmlspecialchars($commande["adresse"]) ?></p>
                    <p>🕐 <strong>Heure :</strong> <?= htmlspecialchars($commande["heure"]) ?> — <?= htmlspecialchars($commande["date"]) ?></p>
                    <p><strong>Type :</strong> <?= ucfirst(str_replace('_', ' ', $commande["type"])) ?></p>
                    <p><strong>Paiement :</strong> <?= htmlspecialchars($commande["paiement"] ?? 'Non renseigné') ?></p>

                    <?php if ($statut !== 'Livrée'): ?>
                    <form method="post" class="clichy">
                        <input type="hidden" name="action" value="update"/>
                        <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
                        <select name="statut">
                            <?php foreach ($statutsDisponibles as $s): ?>
                                <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Mettre à jour</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <br/>
        <?php endforeach; ?>
    <?php endif; ?>

    <p class="commontxt">📋 Toutes les commandes</p>
    <br/>

    <?php if (empty($commandes)): ?>
        <p class="commontxt2">Aucune commande pour le moment.</p>
    <?php else: ?>
        <?php foreach ($commandes as $commande): ?>
            <?php
                $statut = $commande['statut'];
                $livreur = $commande['livreur'] ?? '';
                $sansLivreur = !$livreur || $livreur === 'Non attribué';
                $couleurStatut = match($statut) {
                    'En attente'   => '#f59e0b',
                    'En livraison' => '#3b82f6',
                    'Livrée'       => '#16a34a',
                    'Prête'        => '#8b5cf6',
                    default        => '#6b7280'
                };
            ?>
            <div class="chartres">
                <div class="fontainebleau" style="background: <?= $couleurStatut ?>;">
                    📦 Commande n°<?= intval($commande["numero"]) ?> — <strong><?= htmlspecialchars($commande["client"]) ?></strong>
                    <span><?= htmlspecialchars($statut) ?></span>
                </div>
                <div class="houilles">
                    <p>🍽️ <strong>Plats :</strong> <?= htmlspecialchars($commande["plats"]) ?></p>
                    <p>📍 <strong>Adresse :</strong> <?= htmlspecialchars($commande["adresse"]) ?></p>
                    <p>🕐 <strong>Heure :</strong> <?= htmlspecialchars($commande["heure"]) ?> — <?= htmlspecialchars($commande["date"]) ?></p>
                    <p><strong>Type :</strong> <?= ucfirst(str_replace('_', ' ', $commande["type"])) ?></p>
                    <p><strong>Livreur :</strong> <?= htmlspecialchars($livreur ?: 'Non assigné') ?></p>

                    <?php if ($statut !== 'Livrée'): ?>
                    <form method="post" class="clichy">
                        <input type="hidden" name="action" value="update"/>
                        <input type="hidden" name="numero" value="<?= $commande["numero"] ?>"/>
                        <?php if ($sansLivreur && $nomLivreur): ?>
                            <input type="hidden" name="assigner" value="1"/>
                        <?php endif; ?>
                        <select name="statut">
                            <?php foreach ($statutsDisponibles as $s): ?>
                                <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">
                            <?= $sansLivreur && $nomLivreur ? "M'assigner + Mettre à jour" : "Mettre à jour" ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <br/>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

<footer>
    <p>2026 - Le Goupix</p>
</footer>

<div id="modal-profil-livreur" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="titreProfil-l">
    <div class="modal-box">
        <button class="modal-close" onclick="fermerModaleProfil()" aria-label="Fermer">×</button>
        <p class="modal-title" id="titreProfil-l">✏️ Modifier mes informations</p>

        <div id="modal-feedback-livreur" class="modal-feedback"></div>

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
                <option value="client"       <?= (($utilisateurConnecte['role'] ?? '') === 'client')       ? 'selected' : '' ?>>Client</option>
                <option value="livreur"      <?= (($utilisateurConnecte['role'] ?? '') === 'livreur')      ? 'selected' : '' ?>>Livreur</option>
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
    }

    function validerProfil() {
        const feedback = document.getElementById('modal-feedback-livreur');
        const spinner  = document.getElementById('spinner-livreur');

        const nom    = document.getElementById('champ-nom-l').value;
        const pseudo = document.getElementById('champ-pseudo-l').value;
        const email  = document.getElementById('champ-email-l').value;
        const role   = document.getElementById('champ-role-l').value;

        feedback.className = 'modal-feedback';
        spinner.style.display = 'inline-block';

        fetch('update_profil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, pseudo, email, role })
        })
        .then(res => res.json())
        .then(data => {
            spinner.style.display = 'none';
            if (data.success) {
                feedback.textContent = "Profil mis à jour ! Redirection...";
                feedback.className = 'modal-feedback success';
                setTimeout(() => { location.reload(); }, 1200);
            } else {
                feedback.textContent = data.message || "Une erreur est survenue.";
                feedback.className = 'modal-feedback error';
            }
        })
        .catch(() => {
            spinner.style.display = 'none';
            feedback.textContent = "Erreur réseau ou serveur.";
            feedback.className = 'modal-feedback error';
        });
    }

    document.getElementById('modal-profil-livreur').addEventListener('click', function(e) {
        if (e.target === this) fermerModaleProfil();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fermerModaleProfil();
    });
</script>
<script src="darkMode.js"></script>
</body>
</html>
