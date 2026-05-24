<?php
session_start();

// Pas connecté → tu dégages wesh
if (!isset($_SESSION['connecte'])) {
	header('Location: Espace' . ucfirst($_SESSION['role']) . '.php');
    exit;
}

$fichier      = 'data/utilisateurs.json';
$utilisateurs = json_decode(file_get_contents($fichier), true);

$index    = null;
$mon_compte = null;
foreach ($utilisateurs as $i => $u) {
    if ($u['pseudo'] === $_SESSION['pseudo']) {
        $index      = $i;
        $mon_compte = $u;
        break;
    }
}

// Sécurité pas comme dans le code de maelys
if ($mon_compte === null) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Désactivation du compte — action irréversible, dernier chance avant le grand saut
if ($action === 'desactiver') {
        $confirmation = trim($_POST['confirmation'] ?? '');

        if ($confirmation !== $_SESSION['pseudo']) {
            $erreur = "Le pseudo saisi ne correspond pas. Désactivation annulée.";
        } else {
            $utilisateurs[$index]['statut'] = 'suspendu_temp';
            $utilisateurs[$index]['raison_suspension'] = "Compte désactivé par l'utilisateur lui-même.";

            file_put_contents(
                $fichier,
                json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            session_destroy(); // Poof — la session disparaît comme les commits non poussés
            header('Location: deconnexion.php');
            exit;
        }
    }

    if (!empty($erreur)) {
        // Recharger les données fraîches après erreur
        $mon_compte = json_decode(file_get_contents($fichier), true)[$index];
    }
}

$labels_role = [
    'client'       => 'Client',
    'restaurateur' => 'Restaurateur',
    'livreur'      => 'Livreur',
    'admin'        => 'Administrateur',
];

$labels_statut = [
    'actif'         => 'Actif',
    'suspendu_temp' => 'Suspendu temporairement',
    'suspendu_def'  => 'Banni définitivement',
];

$lien_retour = 'Espace' . ucfirst($mon_compte['role']) . '.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon compte</title>
    <link rel="stylesheet" href="styless.css">
    <link rel="stylesheet" href="darkMode.css">
</head>
<body class="mode-sombre">

<header>
    <nav>
        <a href="<?= $lien_retour ?>">Mon espace</a>
        <a href="compte.php">Mon compte</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Mon compte</h1>

<div class="argenteuil95">

    <!-- sombre à moins sombre ahaha (y'a que la page restaurateur qui fonctionne, les autres javais la flemme  -->
    <button id="toggle-mode-theme">☀️ Mode clair</button>

    <?php if ($message): ?>
        <div class="rosny"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <div class="sarcelles">

        <div class="gonesse">

            <div class="gonesse-inner">
                <div class="garges">
                    <?= strtoupper(substr($mon_compte['nom'], 0, 1)) ?>
                </div>

                <div class="villiers-le-bel">
                    <h2><?= htmlspecialchars($mon_compte['nom']) ?></h2>
                    <p>@<?= htmlspecialchars($mon_compte['pseudo']) ?></p>
                </div>
            </div>

            <button class="bouton-modifier" onclick="ouvrirModalModifier()">
                ✏️ Modifier
            </button>

        </div>

        <div class="ecouen">

            <p class="domont">Informations personnelles</p>

            <div class="persan">

                <div class="beaumont">
                    <span>Nom complet</span>
                    <strong id="afficher-nom"><?= htmlspecialchars($mon_compte['nom']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Pseudo</span>
                    <strong>@<?= htmlspecialchars($mon_compte['pseudo']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Adresse email</span>
                    <strong id="afficher-email"><?= htmlspecialchars($mon_compte['email']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Téléphone</span>
                    <strong id="afficher-telephone"><?= htmlspecialchars($mon_compte['telephone'] ?? '—') ?></strong>
                </div>

                <div class="beaumont">
                    <span>Adresse</span>
                    <strong id="afficher-adresse"><?= htmlspecialchars($mon_compte['adresse'] ?? '—') ?></strong>
                </div>

                <div class="beaumont">
                    <span>Rôle</span>
                    <strong><?= $labels_role[$mon_compte['role']] ?? ucfirst($mon_compte['role']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Statut du compte</span>
                    <strong>
                        <span class="taverny-actif"><?= $labels_statut[$mon_compte['statut']] ?? $mon_compte['statut'] ?></span>
                    </strong>
                </div>

                <div class="beaumont">
                    <span>Statut VIP</span>
                    <strong>
                        <?php if ($mon_compte['vip']): ?>
                            <span class="luzarches">VIP — <?= $mon_compte['reduction_vip'] ?>% de réduction</span>
                        <?php else: ?>
                            Non VIP
                        <?php endif; ?>
                    </strong>
                </div>

            </div>

            <?php if ($mon_compte['vip']): ?>
                <p class="domont">Avantage VIP</p>
                <div class="beaumont">
                    <span>Réduction sur toutes vos commandes</span>
                    <strong><?= $mon_compte['reduction_vip'] ?>% de réduction automatique appliquée au panier</strong>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- supprime pas ton compte stttppp -->
    <div class="enghien">

        <div class="enghien-header">
            Zone de désactivation du compte
        </div>

        <div class="soisy">

            <p>
                En désactivant votre compte, vous serez <strong>immédiatement déconnecté</strong>
                et ne pourrez plus vous connecter. Un administrateur pourra réactiver votre compte
                sur demande.
            </p>

            <p>
                Pour confirmer, saisissez votre pseudo
                <strong>« <?= htmlspecialchars($mon_compte['pseudo']) ?> »</strong> ci-dessous :
            </p>

            <form method="POST" action=""
                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver votre compte ?')">
                <input type="hidden" name="action" value="desactiver">

                <input type="text" name="confirmation" class="viarmes"
                       placeholder="Saisissez votre pseudo pour confirmer"
                       autocomplete="off">

                <button type="submit" class="boissy">
                    Désactiver mon compte
                </button>

            </form>

        </div>
    </div>

</div>

<div id="maroc">
    <div class="senegal-compte">

        <div class="benin-compte">
            <h2>Modifier mes informations</h2>
            <button class="fermer-modal" onclick="fermerModalModifier()">✕</button>
        </div>

        <div id="egypte"></div>

        <form id="formulaire-maroc">
            
            <input type="hidden" id="pseudo-cache" value="<?= htmlspecialchars($mon_compte['pseudo']) ?>">

            <div class="gabon">
                <label>Nom complet *</label>
                <input 
                    type="text" 
                    id="nouveau_nom" 
                    value="<?= htmlspecialchars($mon_compte['nom']) ?>"
                    placeholder="Jean Dupont"
                    required
                />
            </div>

            <div class="gabon">
                <label>Adresse email *</label>
                <input 
                    type="email" 
                    id="nouveau_email" 
                    value="<?= htmlspecialchars($mon_compte['email']) ?>"
                    placeholder="jean@example.com"
                    required
                />
            </div>

            <div class="gabon">
                <label>Téléphone</label>
                <input 
                    type="tel" 
                    id="nouveau_telephone" 
                    value="<?= htmlspecialchars($mon_compte['telephone'] ?? '') ?>"
                    placeholder="+33 6 12 34 56 78"
                />
                <small>Format: +33 6 12 34 56 78</small>
            </div>

            <div class="gabon">
                <label>Adresse</label>
                <textarea 
                    id="nouvelle_adresse" 
                    placeholder="123 rue de la Paix, 75000 Paris"
                ><?= htmlspecialchars($mon_compte['adresse'] ?? '') ?></textarea>
            </div>

            <div class="gabon">
                <label>Rôle *</label>
                <select id="nouveau_role" required>
                    <option value="client" <?= ($mon_compte['role'] === 'client') ? 'selected' : '' ?>>Client</option>
                    <option value="restaurateur" <?= ($mon_compte['role'] === 'restaurateur') ? 'selected' : '' ?>>Restaurateur</option>
                    <option value="livreur" <?= ($mon_compte['role'] === 'livreur') ? 'selected' : '' ?>>Livreur</option>
                </select>
            </div>

            <div class="kenya-compte">
                <button 
                    type="button" 
                    class="btn-annuler" 
                    onclick="fermerModalModifier()">
                    Annuler
                </button>
                <button 
                    type="submit" 
                    class="btn-valider">
                    Valider les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script src="modificationProfil.js"></script>
<script src="darkMode.js"></script>

</body>
</html>
