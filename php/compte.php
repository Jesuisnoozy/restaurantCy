<?php
session_start();

// Pas connecté → tu dégages wesh
if (!isset($_SESSION['connecte'])) {
	header('Location: Espace' . ucfirst($trouve['role']) . '.php');
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

            session_destroy();
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
</head>
<body>

<header>
    <nav>
        <a href="<?= $lien_retour ?>">Mon espace</a>
        <a href="compte.php">Mon compte</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Mon compte</h1>

<!-- .argenteuil95 = wrapper page -->
<div class="argenteuil95">

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="rosny"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- ========== CARTE PRINCIPALE ========== -->
    <!-- .sarcelles = carte blanche -->
    <div class="sarcelles">

        <!-- .gonesse = bandeau orange avec avatar -->
        <div class="gonesse">

            <!-- .garges = cercle initiales -->
            <div class="garges">
                <?= strtoupper(substr($mon_compte['nom'], 0, 1)) ?>
            </div>

            <!-- .villiers-le-bel = nom + pseudo -->
            <div class="villiers-le-bel">
                <h2><?= htmlspecialchars($mon_compte['nom']) ?></h2>
                <p>@<?= htmlspecialchars($mon_compte['pseudo']) ?></p>
            </div>

        </div>

        <!-- .ecouen = corps infos -->
        <div class="ecouen">

            <!-- Titre section -->
            <p class="domont">Informations personnelles</p>

            <!-- .persan = grille 2 colonnes -->
            <div class="persan">

                <!-- .beaumont = chaque case info -->
                <div class="beaumont">
                    <span>Nom complet</span>
                    <strong><?= htmlspecialchars($mon_compte['nom']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Pseudo</span>
                    <strong>@<?= htmlspecialchars($mon_compte['pseudo']) ?></strong>
                </div>

                <div class="beaumont">
                    <span>Adresse email</span>
                    <strong><?= htmlspecialchars($mon_compte['email']) ?></strong>
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
                <div class="beaumont" style="margin-bottom: 28px;">
                    <span>Réduction sur toutes vos commandes</span>
                    <strong><?= $mon_compte['reduction_vip'] ?>% de réduction automatique appliquée au panier</strong>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ========== ZONE DÉSACTIVATION ========== -->
    <!-- .enghien = carte zone danger -->
    <div class="enghien">

        <div class="enghien-header">
            Zone de désactivation du compte
        </div>

        <!-- .soisy = corps zone danger -->
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

                <!-- .viarmes = champ confirmation -->
                <input type="text" name="confirmation" class="viarmes"
                       placeholder="Saisissez votre pseudo pour confirmer"
                       autocomplete="off">

                <!-- .boissy = bouton rouge désactiver -->
                <button type="submit" class="boissy">
                    Désactiver mon compte
                </button>

            </form>

        </div>
    </div>

</div><!-- fin argenteuil95 -->

</body>
</html>
