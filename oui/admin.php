<?php
require 'auth_admin.php';

$fichier      = 'data/utilisateurs.json';
$utilisateurs = json_decode(file_get_contents($fichier), true);

$message = '';
$erreur  = '';

// ============================================================
// TRAITEMENT DES ACTIONS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';

    $index = null;
    foreach ($utilisateurs as $i => $u) {
        if ($u['pseudo'] === $pseudo) {
            $index = $i;
            break;
        }
    }

    if ($pseudo === $_SESSION['pseudo']) {
        $erreur = "Vous ne pouvez pas modifier votre propre compte.";

    } elseif ($index === null) {
        $erreur = "Utilisateur introuvable.";

    } else {

        // Modifier les infos de base
        if ($action === 'modifier') {
            $nouveau_nom   = trim($_POST['nouveau_nom']   ?? '');
            $nouveau_email = trim($_POST['nouveau_email'] ?? '');
            $nouveau_role  = $_POST['nouveau_role']       ?? '';

            if (empty($nouveau_nom) || empty($nouveau_email)) {
                $erreur = "Le nom et l'email sont obligatoires.";
            } elseif (!filter_var($nouveau_email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "Email invalide.";
            } elseif (!in_array($nouveau_role, ['client', 'restaurateur', 'livreur', 'admin'])) {
                $erreur = "Rôle invalide.";
            } else {
                $utilisateurs[$index]['nom']   = $nouveau_nom;
                $utilisateurs[$index]['email'] = $nouveau_email;
                $utilisateurs[$index]['role']  = $nouveau_role;
                $message = "Informations de « {$pseudo} » mises à jour.";
            }
        }

        // Activer le VIP
        if ($action === 'vip_activer') {
            $reduction = intval($_POST['reduction_vip'] ?? 10);
            $reduction = max(1, min(100, $reduction));
            $utilisateurs[$index]['vip']           = true;
            $utilisateurs[$index]['reduction_vip'] = $reduction;
            $message = "« {$pseudo} » est maintenant VIP avec {$reduction}% de réduction.";
        }

        // Retirer le VIP
        if ($action === 'vip_retirer') {
            $utilisateurs[$index]['vip']           = false;
            $utilisateurs[$index]['reduction_vip'] = 0;
            $message = "Le statut VIP de « {$pseudo} » a été retiré.";
        }

        // Suspension temporaire
        if ($action === 'suspendre_temp') {
            $raison = trim($_POST['raison'] ?? '');
            if (empty($raison)) {
                $erreur = "La raison de la suspension est obligatoire.";
            } else {
                $utilisateurs[$index]['statut']            = 'suspendu_temp';
                $utilisateurs[$index]['raison_suspension'] = $raison;
                $message = "« {$pseudo} » a été suspendu temporairement.";
            }
        }

        // Suspension définitive
        if ($action === 'suspendre_def') {
            $raison = trim($_POST['raison'] ?? '');
            if (empty($raison)) {
                $erreur = "La raison du bannissement est obligatoire.";
            } else {
                $utilisateurs[$index]['statut']            = 'suspendu_def';
                $utilisateurs[$index]['raison_suspension'] = $raison;
                $message = "« {$pseudo} » a été banni définitivement.";
            }
        }

        // Réactiver
        if ($action === 'reactiver') {
            $utilisateurs[$index]['statut']            = 'actif';
            $utilisateurs[$index]['raison_suspension'] = '';
            $message = "Le compte de « {$pseudo} » a été réactivé.";
        }

        // Supprimer
        if ($action === 'supprimer') {
            array_splice($utilisateurs, $index, 1);
            $message = "Le compte « {$pseudo} » a été supprimé définitivement.";
        }
    }

    if (empty($erreur)) {
        file_put_contents(
            $fichier,
            json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    $param = $message ? '?msg=' . urlencode($message) : '?err=' . urlencode($erreur);
    header('Location: admin.php' . $param);
    exit;
}

// Messages depuis l'URL
if (isset($_GET['msg'])) $message = $_GET['msg'];
if (isset($_GET['err'])) $erreur  = $_GET['err'];

// Filtre de recherche
$filtre = trim($_GET['filtre'] ?? '');
$utilisateurs_affiches = $utilisateurs;
if ($filtre !== '') {
    $utilisateurs_affiches = array_filter($utilisateurs, function($u) use ($filtre) {
        return stripos($u['pseudo'], $filtre) !== false
            || stripos($u['nom'],    $filtre) !== false
            || stripos($u['email'],  $filtre) !== false
            || stripos($u['role'],   $filtre) !== false;
    });
}

// Statistiques
$total_users     = count($utilisateurs);
$total_actifs    = count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'actif'));
$total_vip       = count(array_filter($utilisateurs, fn($u) => $u['vip'] === true));
$total_suspendus = count(array_filter($utilisateurs, fn($u) => in_array($u['statut'], ['suspendu_temp','suspendu_def'])));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>

<header>
    <nav>
        <a href="admin.php">Panel Admin</a>
        <a href="commande.php">Voir le site</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Panel Administrateur</h1>

<div class="admin-wrapper">

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-nombre"><?= $total_users ?></div>
            <div class="stat-label">Utilisateurs total</div>
        </div>
        <div class="stat-card">
            <div class="stat-nombre" style="color:#16a34a"><?= $total_actifs ?></div>
            <div class="stat-label">Comptes actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-nombre" style="color:#d97706"><?= $total_vip ?></div>
            <div class="stat-label">Membres VIP</div>
        </div>
        <div class="stat-card">
            <div class="stat-nombre" style="color:#dc2626"><?= $total_suspendus ?></div>
            <div class="stat-label">Comptes suspendus</div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="msg-succes-global"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="msg-erreur-global"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- Barre de recherche -->
    <form method="GET" action="" class="barre-admin">
        <input type="text" name="filtre"
               placeholder="Rechercher par pseudo, nom, email ou rôle..."
               value="<?= htmlspecialchars($filtre) ?>">
        <button type="submit">Rechercher</button>
        <?php if ($filtre): ?>
            <a href="admin.php">Tout afficher</a>
        <?php endif; ?>
    </form>

    <!-- Liste des utilisateurs -->
    <?php if (empty($utilisateurs_affiches)): ?>
        <p class="aucun-resultat">Aucun utilisateur trouvé.</p>

    <?php else: ?>

        <?php foreach ($utilisateurs_affiches as $u):
            $est_moi = ($u['pseudo'] === $_SESSION['pseudo']);
        ?>

        <div class="user-card">

            <!-- En-tête -->
            <div class="user-card-header <?= htmlspecialchars($u['statut']) ?>">
                <div>
                    <div class="user-identite"><?= htmlspecialchars($u['nom']) ?></div>
                    <div class="user-pseudo">@<?= htmlspecialchars($u['pseudo']) ?></div>
                </div>
                <div class="badges">

                    <span class="badge badge-role-<?= $u['role'] ?>">
                        <?= ucfirst($u['role']) ?>
                    </span>

                    <?php if ($u['statut'] === 'actif'): ?>
                        <span class="badge badge-actif">Actif</span>
                    <?php elseif ($u['statut'] === 'suspendu_temp'): ?>
                        <span class="badge badge-suspendu-temp">Suspendu (temp.)</span>
                    <?php elseif ($u['statut'] === 'suspendu_def'): ?>
                        <span class="badge badge-suspendu-def">Banni</span>
                    <?php endif; ?>

                    <?php if ($u['vip']): ?>
                        <span class="badge badge-vip">VIP -<?= $u['reduction_vip'] ?>%</span>
                    <?php endif; ?>

                    <?php if ($est_moi): ?>
                        <span class="badge badge-moi">Vous</span>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Corps -->
            <div class="user-card-body">

                <!-- Infos de base -->
                <div class="user-infos">
                    <div class="user-info-item">
                        <span>Email</span>
                        <?= htmlspecialchars($u['email']) ?>
                    </div>
                    <div class="user-info-item">
                        <span>Rôle</span>
                        <?= ucfirst($u['role']) ?>
                    </div>
                    <div class="user-info-item">
                        <span>Réduction VIP</span>
                        <?= $u['vip'] ? $u['reduction_vip'] . '%' : '—' ?>
                    </div>
                </div>

                <!-- Raison de suspension -->
                <?php if (!empty($u['raison_suspension'])): ?>
                    <div class="raison-suspension <?= $u['statut'] === 'suspendu_def' ? 'def' : '' ?>">
                        <strong>
                            <?= $u['statut'] === 'suspendu_def' ? 'Raison du bannissement' : 'Raison de la suspension' ?> :
                        </strong>
                        <?= htmlspecialchars($u['raison_suspension']) ?>
                    </div>
                <?php endif; ?>

                <!-- Pas d'actions sur son propre compte -->
                <?php if ($est_moi): ?>
                    <p class="non-modifiable">
                        Vous ne pouvez pas modifier votre propre compte depuis ce panel.
                    </p>

                <?php else: ?>

                    <div class="actions-titre-fixe">Gérer cet utilisateur</div>

                    <!-- 1. Modifier les infos -->
                    <div class="action-section">
                        <h3>Modifier les informations</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="modifier">
                            <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">

                            <label class="inline-label">Nom complet</label>
                            <input type="text" name="nouveau_nom"
                                   value="<?= htmlspecialchars($u['nom']) ?>">

                            <label class="inline-label">Email</label>
                            <input type="email" name="nouveau_email"
                                   value="<?= htmlspecialchars($u['email']) ?>">

                            <label class="inline-label">Rôle</label>
                            <select name="nouveau_role">
                                <option value="client"       <?= $u['role']==='client'       ?'selected':'' ?>>Client</option>
                                <option value="restaurateur" <?= $u['role']==='restaurateur' ?'selected':'' ?>>Restaurateur</option>
                                <option value="livreur"      <?= $u['role']==='livreur'      ?'selected':'' ?>>Livreur</option>
                                <option value="admin"        <?= $u['role']==='admin'        ?'selected':'' ?>>Admin</option>
                            </select>

                            <button type="submit" class="btn-action btn-modifier">
                                Enregistrer les modifications
                            </button>
                        </form>
                    </div>

                    <!-- 2. Statut VIP -->
                    <div class="action-section">
                        <h3>Statut VIP</h3>

                        <?php if ($u['vip']): ?>
                            <p>Actuellement VIP avec <?= $u['reduction_vip'] ?>% de réduction.</p>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="vip_retirer">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <button type="submit" class="btn-action btn-retirer-vip">
                                    Retirer le VIP
                                </button>
                            </form>

                        <?php else: ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="vip_activer">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <label class="inline-label">Réduction VIP (%)</label>
                                <input type="number" name="reduction_vip"
                                       value="10" min="1" max="100"
                                       style="width:120px; display:inline-block;">
                                <button type="submit" class="btn-action btn-vip">
                                    Activer le VIP
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- 3. Suspension -->
                    <div class="action-section">
                        <h3>Suspension du compte</h3>

                        <?php if ($u['statut'] !== 'actif'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="reactiver">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <button type="submit" class="btn-action btn-reactivate">
                                    Réactiver le compte
                                </button>
                            </form>

                        <?php else: ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="suspendre_temp">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <label class="inline-label">Raison de la suspension temporaire *</label>
                                <textarea name="raison"
                                          placeholder="Ex : comportement inapproprié, signalement client..."></textarea>
                                <button type="submit" class="btn-action btn-suspendre-temp">
                                    Suspendre temporairement
                                </button>
                            </form>

                            <hr class="separateur-action">

                            <form method="POST" action="">
                                <input type="hidden" name="action" value="suspendre_def">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <label class="inline-label">Raison du bannissement définitif *</label>
                                <textarea name="raison"
                                          placeholder="Ex : fraude avérée, violation grave des CGU..."></textarea>
                                <button type="submit" class="btn-action btn-suspendre-def">
                                    Bannir définitivement
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- 4. Zone dangereuse -->
                    <div class="action-section zone-danger">
                        <h3>Zone dangereuse</h3>
                        <p class="non-modifiable">
                            Cette action est irréversible. Le compte sera supprimé définitivement.
                        </p>
                        <form method="POST" action=""
                              onsubmit="return confirm('Supprimer définitivement le compte de <?= htmlspecialchars($u['nom'], ENT_QUOTES) ?> ?')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                            <button type="submit" class="btn-action btn-supprimer">
                                Supprimer ce compte
                            </button>
                        </form>
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
