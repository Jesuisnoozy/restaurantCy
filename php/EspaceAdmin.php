<?php
require 'auth_admin.php';

$fichier      = 'data/utilisateurs.json';
$utilisateurs = json_decode(file_get_contents($fichier), true);

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';

    $index = null;
    foreach ($utilisateurs as $i => $u) {
        if ($u['pseudo'] === $pseudo) { $index = $i; break; }
    }

    if ($pseudo === $_SESSION['pseudo']) {
        $erreur = "Vous ne pouvez pas modifier votre propre compte. cheh mdrr ";

    } elseif ($index === null) {
        $erreur = "Utilisateur introuvable.";

    } else {

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

        if ($action === 'vip_activer') {
            $reduction = intval($_POST['reduction_vip'] ?? 10);
            $reduction = max(1, min(100, $reduction));
            $utilisateurs[$index]['vip']           = true;
            $utilisateurs[$index]['reduction_vip'] = $reduction;
            $message = "« {$pseudo} » est maintenant VIP avec {$reduction}% de réduction.";
        }

        if ($action === 'vip_retirer') {
            $utilisateurs[$index]['vip']           = false;
            $utilisateurs[$index]['reduction_vip'] = 0;
            $message = "Le statut VIP de « {$pseudo} » a été retiré.";
        }

        if ($action === 'suspendre_temp') {
            $raison = trim($_POST['raison'] ?? '');
            if (empty($raison)) {
                $erreur = "donne une raison on est pas en dictature icic.";
            } else {
                $utilisateurs[$index]['statut']            = 'suspendu_temp';
                $utilisateurs[$index]['raison_suspension'] = $raison;
                $message = "« {$pseudo} » a été suspendu temporairement.";
            }
        }

        if ($action === 'suspendre_def') {
            $raison = trim($_POST['raison'] ?? '');
            if (empty($raison)) {
                $erreur = "on attend toujours une raison oohh.";
            } else {
                $utilisateurs[$index]['statut']            = 'suspendu_def';
                $utilisateurs[$index]['raison_suspension'] = $raison;
                $message = "« {$pseudo} » a été banni définitivement.";
            }
        }

        if ($action === 'reactiver') {
            $utilisateurs[$index]['statut']            = 'actif';
            $utilisateurs[$index]['raison_suspension'] = '';
            $message = "Le compte de « {$pseudo} » a été réactivé.";
        }

        if ($action === 'supprimer') {
            array_splice($utilisateurs, $index, 1);
            $message = "Le compte « {$pseudo} » a été supprimée .";
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

if (isset($_GET['msg'])) $message = $_GET['msg'];
if (isset($_GET['err'])) $erreur  = $_GET['err'];

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
        <a href="EspaceAdmin.php">Panel Admin</a>
        <a href="Carte.php">Voir le site</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Panel Administrateur</h1>


<div class="saint-denis">

    <div class="nanterre">
        <div class="creteil">
            <div class="bobigny"><?= $total_users ?></div>
            <div class="evry">Utilisateurs total</div>
        </div>
        <div class="creteil">
            <div class="bobigny" style="color:#16a34a"><?= $total_actifs ?></div>
            <div class="evry">Comptes actifs</div>
        </div>
        <div class="creteil">
            <div class="bobigny" style="color:#d97706"><?= $total_vip ?></div>
            <div class="evry">Membres VIP</div>
        </div>
        <div class="creteil">
            <div class="bobigny" style="color:#dc2626"><?= $total_suspendus ?></div>
            <div class="evry">Comptes suspendus</div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="rosny"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="GET" action="" class="antony">
        <input type="text" name="filtre"
               placeholder="Rechercher par pseudo, nom, email ou rôle..."
               value="<?= htmlspecialchars($filtre) ?>">
        <button type="submit">Rechercher</button>
        <?php if ($filtre): ?>
            <a href="admin.php">Tout afficher</a>
        <?php endif; ?>
    </form>

    <?php if (empty($utilisateurs_affiches)): ?>
        <p class="yerres">Aucun utilisateur trouvé.</p>

    <?php else: ?>

        <?php foreach ($utilisateurs_affiches as $u):
            $est_moi = ($u['pseudo'] === $_SESSION['pseudo']);
        ?>

        <div class="pontoise">

            <div class="argenteuil <?= htmlspecialchars($u['statut']) ?>">
                <div>
                    <div class="montreuil"><?= htmlspecialchars($u['nom']) ?></div>
                    <div class="aubervilliers">@<?= htmlspecialchars($u['pseudo']) ?></div>
                </div>

                <div class="bagneux">
                    <span class="clamart clamart-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>

                    <?php if ($u['statut'] === 'actif'): ?>
                        <span class="clamart clamart-actif">Actif</span>
                    <?php elseif ($u['statut'] === 'suspendu_temp'): ?>
                        <span class="clamart clamart-temp">Suspendu (temp.)</span>
                    <?php elseif ($u['statut'] === 'suspendu_def'): ?>
                        <span class="clamart clamart-def">Banni</span>
                    <?php endif; ?>

                    <?php if ($u['vip']): ?>
                        <span class="clamart clamart-vip">VIP -<?= $u['reduction_vip'] ?>%</span>
                    <?php endif; ?>

                    <?php if ($est_moi): ?>
                        <span class="clamart clamart-moi">Vous</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="courbevoie">

                <div class="asnieres">
                    <div class="colombes">
                        <span>Email</span><?= htmlspecialchars($u['email']) ?>
                    </div>
                    <div class="colombes">
                        <span>Rôle</span><?= ucfirst($u['role']) ?>
                    </div>
                    <div class="colombes">
                        <span>Réduction VIP</span>
                        <?= $u['vip'] ? $u['reduction_vip'] . '%' : '—' ?>
                    </div>
                </div>

                <?php if (!empty($u['raison_suspension'])): ?>
                    <div class="drancy <?= $u['statut'] === 'suspendu_def' ? 'def' : '' ?>">
                        <strong>
                            <?= $u['statut'] === 'suspendu_def' ? 'Raison du bannissement' : 'Raison de la suspension' ?> :
                        </strong>
                        <?= htmlspecialchars($u['raison_suspension']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($est_moi): ?>
                    <p class="savigny">t'es certes admin mais t'as pas tout les droits xd</p>

                <?php else: ?>

                    <div class="gennevilliers">Gérer cet utilisateur</div>

                    <div class="issy">

                        <div class="levallois">
                            <h3>Modifier les informations</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="modifier">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <label class="noisy">Nom complet</label>
                                <input type="text" name="nouveau_nom"
                                       value="<?= htmlspecialchars($u['nom']) ?>">
                                <label class="noisy">Email</label>
                                <input type="email" name="nouveau_email"
                                       value="<?= htmlspecialchars($u['email']) ?>">
                                <label class="noisy">Rôle</label>
                                <select name="nouveau_role">
                                    <option value="client"       <?= $u['role']==='client'       ?'selected':'' ?>>Client</option>
                                    <option value="restaurateur" <?= $u['role']==='restaurateur' ?'selected':'' ?>>Restaurateur</option>
                                    <option value="livreur"      <?= $u['role']==='livreur'      ?'selected':'' ?>>Livreur</option>
                                    <option value="admin"        <?= $u['role']==='admin'        ?'selected':'' ?>>Admin</option>
                                </select>
                                <button type="submit" class="pantin rueil">Enregistrer les modifications</button>
                            </form>
                        </div>

                        <div class="levallois">
                            <h3>Statut VIP</h3>
                            <?php if ($u['vip']): ?>
                                <p>Actuellement VIP avec <?= $u['reduction_vip'] ?>% de réduction.</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="vip_retirer">
                                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                    <button type="submit" class="pantin massy">Retirer le VIP</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="vip_activer">
                                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                    <label class="noisy">Réduction VIP (%)</label>
                                    <input type="number" name="reduction_vip"
                                           value="10" min="1" max="100"
                                           style="width:120px; display:inline-block;">
                                    <button type="submit" class="pantin suresnes">Activer le VIP</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="levallois">
                            <h3>Suspension du compte</h3>
                            <?php if ($u['statut'] !== 'actif'): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="reactiver">
                                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                    <button type="submit" class="pantin vitry">Réactiver le compte</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="suspendre_temp">
                                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                    <label class="noisy">Raison de la suspension temporaire *</label>
                                    <textarea name="raison"
                                              placeholder="Ex : comportement inapproprié, signalement client..."></textarea>
                                    <button type="submit" class="pantin aulnay">Suspendre temporairement</button>
                                </form>
                                <hr class="tremblay">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="suspendre_def">
                                    <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                    <label class="noisy">Raison du bannissement définitif *</label>
                                    <textarea name="raison"
                                              placeholder="Ex : fraude avérée, violation grave des CGU..."></textarea>
                                    <button type="submit" class="pantin bondy">Bannir définitivement</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="levallois villejuif">
                            <h3>Zone dangereuse</h3>
                            <p class="savigny">Cette action est irréversible. Le compte sera supprimé définitivement.</p>
                            <form method="POST" action=""
                                  onsubmit="return confirm('Supprimer définitivement le compte de <?= htmlspecialchars($u['nom'], ENT_QUOTES) ?> ?')">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="pseudo" value="<?= htmlspecialchars($u['pseudo']) ?>">
                                <!-- .gagny = ancien .btn-supprimer -->
                                <button type="submit" class="pantin gagny">Supprimer ce compte</button>
                            </form>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </div>

        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
