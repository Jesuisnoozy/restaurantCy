<?php
session_start();

// Redirige vers le bon espace selon le rôle
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'client') {
        header('Location: EspaceClient.php');
        exit;
    } elseif ($_SESSION['role'] === 'livreur') {
        header('Location: EspaceLivreur.php');
        exit;
    }
}

$role_requis = 'restaurateur';
$fichier_pmc = __DIR__ . '/data/PMC.json';

if (!file_exists($fichier_pmc)) {
    die("❌ Fichier PMC.json introuvable à : $fichier_pmc");
}

$data = json_decode(file_get_contents($fichier_pmc), true);
if ($data === null) {
    die("❌ Erreur de décodage JSON : " . json_last_error_msg());
}

$plats     = $data['plats']     ?? [];
$menus     = $data['menus']     ?? [];
$commandes = $data['commandes'] ?? [];

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'changer_statut') {
        $numero     = intval($_POST['numero'] ?? 0);
        $nouveau    = $_POST['nouveau_statut'] ?? '';
        $statuts_ok = ['En attente', 'En préparation', 'Prête', 'En livraison', 'Livrée'];

        if (!in_array($nouveau, $statuts_ok)) {
            $erreur = "Statut invalide.";
        } else {
            foreach ($data['commandes'] as &$c) {
                if ($c['numero'] === $numero) {
                    $c['statut'] = $nouveau;
                    break;
                }
            }
            unset($c);
            file_put_contents($fichier_pmc, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "Commande #$numero mise à jour : « $nouveau ».";
        }
    }

    if ($action === 'toggle_dispo') {
        $nom_plat = $_POST['nom_plat'] ?? '';
        foreach ($data['plats'] as &$p) {
            if ($p['nom'] === $nom_plat) {
                $p['disponible'] = isset($p['disponible']) ? !$p['disponible'] : false;
                break;
            }
        }
        unset($p);
        file_put_contents($fichier_pmc, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: EspaceRestaurateur.php');
        exit;
    }

    if ($action === 'ajouter_commande') {
        $client  = trim($_POST['client']  ?? '');
        $type    = $_POST['type']         ?? '';
        $adresse = trim($_POST['adresse'] ?? '');
        $plats_c = trim($_POST['plats']   ?? '');
        $heure   = date('H:i');
        $date    = date('Y-m-d');

        if (empty($client) || empty($type) || empty($plats_c)) {
            $erreur = "Client, type et plats sont obligatoires.";
        } else {
            $dernier_numero = max(array_column($data['commandes'], 'numero'));
            $data['commandes'][] = [
                'numero'  => $dernier_numero + 1,
                'client'  => $client,
                'type'    => $type,
                'adresse' => $adresse,
                'date'    => $date,
                'heure'   => $heure,
                'plats'   => $plats_c,
                'statut'  => 'En attente',
                'livreur' => ''
            ];
            file_put_contents($fichier_pmc, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message = "Commande #" . ($dernier_numero + 1) . " ajoutée avec succès !";
        }
    }

    // pizza !! 
    $data      = json_decode(file_get_contents($fichier_pmc), true);
    $plats     = $data['plats'];
    $menus     = $data['menus'];
    $commandes = $data['commandes'];
}

$commandes_actives   = array_filter($commandes, fn($c) => $c['statut'] !== 'Livrée');
$commandes_terminees = array_filter($commandes, fn($c) => $c['statut'] === 'Livrée');

$couleurs_statut = [
    'En attente'     => '#6b7280',
    'En préparation' => '#d97706',
    'Prête'          => '#16a34a',
    'En livraison'   => '#2563eb',
    'Livrée'         => '#4b5563',
];

$statuts_liste = ['En attente', 'En préparation', 'Prête', 'En livraison', 'Livrée'];

$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Restaurateur</title>
    <link rel="stylesheet" href="styless.css">
    <link rel="stylesheet" href="darkMode.css">
</head>
<body class="mode-sombre">

<header>
    <nav>
        <a href="EspaceRestaurateur.php">Mes commandes</a>
        <a href="compte.php">Mon compte</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Espace Restaurateur</h1>

<?php if ($utilisateurConnecte): ?>
    <div class="restaurateur-intro">
        <!-- Bouton de basculement mode clair/sombre -->
        <button id="toggle-mode-theme">☀️ Mode clair</button>
        
        <p>
            Bonjour <strong><?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? $utilisateurConnecte['nom']) ?></strong> 👨‍🍳
        </p>
        <button class="modal-open-btn modal-open-btn-restaurateur" onclick="ouvrirModaleProfil()">✏️ Modifier mes informations</button>
    </div>
<?php endif; ?>

<div class="meaux">

    <?php if ($message): ?>
        <div class="rosny"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <div class="evry95">
        <div class="draveil">
            <div class="draveil-nombre"><?= count($commandes_actives) ?></div>
            <div class="draveil-label">Commandes actives</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre draveil-nombre-orange">
                <?= count(array_filter($commandes, fn($c) => $c['statut'] === 'En préparation')) ?>
            </div>
            <div class="draveil-label">En préparation</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre draveil-nombre-green">
                <?= count(array_filter($commandes, fn($c) => $c['statut'] === 'Prête')) ?>
            </div>
            <div class="draveil-label">Prêtes</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre draveil-nombre-grey">
                <?= count($commandes_terminees) ?>
            </div>
            <div class="draveil-label">Livrées aujourd'hui</div>
        </div>
    </div>

    <div class="melun">

        <div>

            <div class="chartres">
                <div class="fontainebleau">
                    Commandes en cours
                    <span><?= count($commandes_actives) ?></span>
                </div>
                <div class="rambouillet">

                    <?php if (empty($commandes_actives)): ?>
                        <p class="grigny">Aucune commande active pour le moment.</p>
                    <?php else: ?>

                        <?php foreach ($commandes_actives as $c): ?>
                        <div class="mantes">
                            <div class="poissy">
                                <div class="poissy-gauche">
                                    <span class="numero-commande">#<?= $c['numero'] ?></span>
                                    <span class="conflans conflans-<?= $c['type'] ?>">
                                        <?= $c['type'] === 'livraison' ? 'Livraison' : 'Sur place' ?>
                                    </span>
                                </div>
                                <span class="argentan" style="background:<?= $couleurs_statut[$c['statut']] ?>">
                                    <?= htmlspecialchars($c['statut']) ?>
                                </span>
                            </div>
                            <div class="houilles">
                                <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
                                <p><strong>Plats :</strong> <?= htmlspecialchars($c['plats']) ?></p>
                                <p><strong>Adresse :</strong> <?= htmlspecialchars($c['adresse'] ?? '—') ?></p>
                            </div>
                            <form method="POST" action="" class="form-change-statut">
                                <input type="hidden" name="action" value="changer_statut">
                                <input type="hidden" name="numero" value="<?= $c['numero'] ?>">
                                <select name="nouveau_statut" onchange="this.form.submit()">
                                    <option value="">— Changer statut —</option>
                                    <?php foreach ($statuts_liste as $statut): ?>
                                    <option value="<?= $statut ?>" <?= $c['statut'] === $statut ? 'selected' : '' ?>>
                                        <?= $statut ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>

            <?php if (!empty($commandes_terminees)): ?>
            <div class="chartres">
                <div class="fontainebleau">
                    Commandes livrées
                    <span><?= count($commandes_terminees) ?></span>
                </div>
                <div class="rambouillet">
                    <?php foreach ($commandes_terminees as $c): ?>
                    <div class="mantes">
                        <div class="poissy">
                            <div class="poissy-gauche">
                                <span class="numero-commande numero-commande-done">#<?= $c['numero'] ?></span>
                                <span class="conflans conflans-<?= $c['type'] ?>">
                                    <?= $c['type'] === 'livraison' ? 'Livraison' : 'Sur place' ?>
                                </span>
                            </div>
                            <span class="argentan" style="background:#4b5563;">Livrée</span>
                        </div>
                        <div class="houilles">
                            <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
                            <p><strong>Plats :</strong> <?= htmlspecialchars($c['plats']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="chartres">
                <div class="fontainebleau">Ajouter une commande</div>
                <form method="POST" action="" class="puteaux">
                    <input type="hidden" name="action" value="ajouter_commande">

                    <label>Nom du client</label>
                    <input type="text" name="client" placeholder="Ex : Jean Dupont">

                    <label>Type de commande</label>
                    <select name="type">
                        <option value="sur_place">Sur place</option>
                        <option value="livraison">Livraison</option>
                    </select>

                    <label>Adresse (si livraison)</label>
                    <input type="text" name="adresse" placeholder="Ex : 7 avenue Victor Hugo, Paris">

                    <label>Plats commandés</label>
                    <textarea name="plats"
                              placeholder="Ex : 2x Tacos del Cochono + 1x Agua de Jamaica"></textarea>

                    <button type="submit">Ajouter la commande</button>
                </form>
            </div>

        </div>

        <div>
            <div class="chartres">
                <div class="fontainebleau">
                    Carte & disponibilités
                </div>
                <div class="rambouillet">

                    <p class="ozoir">Plats</p>
                    <?php foreach ($plats as $plat):
                        if ($plat['categorie'] !== 'plat') continue;
                        $dispo = $plat['disponible'] ?? true;
                    ?>
                    <div class="noisiel">
                        <span class="noisiel-nom"><?= htmlspecialchars($plat['nom']) ?></span>
                        <span class="noisiel-prix"><?= $plat['prix'] ?></span>
                        <span class="<?= $dispo ? 'torcy-dispo' : 'torcy-indispo' ?>">
                            <?= $dispo ? 'Dispo' : 'Indispo' ?>
                        </span>
                        <form method="POST" action="" class="form-inline-zero">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <p class="ozoir">Desserts</p>
                    <?php foreach ($plats as $plat):
                        if ($plat['categorie'] !== 'dessert') continue;
                        $dispo = $plat['disponible'] ?? true;
                    ?>
                    <div class="noisiel">
                        <span class="noisiel-nom"><?= htmlspecialchars($plat['nom']) ?></span>
                        <span class="noisiel-prix"><?= $plat['prix'] ?></span>
                        <span class="<?= $dispo ? 'torcy-dispo' : 'torcy-indispo' ?>">
                            <?= $dispo ? 'Dispo' : 'Indispo' ?>
                        </span>
                        <form method="POST" action="" class="form-inline-zero">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <p class="ozoir">Boissons</p>
                    <?php foreach ($plats as $plat):
                        if ($plat['categorie'] !== 'boisson') continue;
                        $dispo = $plat['disponible'] ?? true;
                    ?>
                    <div class="noisiel">
                        <span class="noisiel-nom"><?= htmlspecialchars($plat['nom']) ?></span>
                        <span class="noisiel-prix"><?= $plat['prix'] ?></span>
                        <span class="<?= $dispo ? 'torcy-dispo' : 'torcy-indispo' ?>">
                            <?= $dispo ? 'Dispo' : 'Indispo' ?>
                        </span>
                        <form method="POST" action="" class="form-inline-zero">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <p class="ozoir">Menus</p>
                    <?php foreach ($menus as $menu): ?>
                    <div class="noisiel">
                        <div style="flex:1; min-width:140px;">
                            <span class="noisiel-nom"><?= htmlspecialchars($menu['nom']) ?></span>
                            <span class="menu-creneaux">
                                <?= htmlspecialchars($menu['creneaux']) ?>
                            </span>
                        </div>
                        <span class="noisiel-prix"><?= $menu['prix'] ?></span>
                        <span class="torcy-dispo">Dispo</span>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<div id="modal-profil-restaurateur" role="dialog" aria-modal="true" aria-labelledby="titreProfil-r">
    <div id="modal-box-restaurateur">

        <button class="modal-close" onclick="fermerModaleProfil()" aria-label="Fermer">×</button>

        <p class="modal-title" id="titreProfil-r">✏️ Modifier mes informations</p>

        <div id="modal-feedback-restaurateur"></div>

        <div class="modal-field">
            <label for="champ-nom-r">Nom</label>
            <input type="text" id="champ-nom-r"
                   value="<?= htmlspecialchars($utilisateurConnecte['nom'] ?? '') ?>"
                   placeholder="Votre nom"/>
        </div>

        <div class="modal-field">
            <label for="champ-pseudo-r">Pseudo</label>
            <input type="text" id="champ-pseudo-r"
                   value="<?= htmlspecialchars($utilisateurConnecte['pseudo'] ?? '') ?>"
                   placeholder="Votre pseudo"/>
        </div>

        <div class="modal-field">
            <label for="champ-email-r">Email</label>
            <input type="email" id="champ-email-r"
                   value="<?= htmlspecialchars($utilisateurConnecte['email'] ?? '') ?>"
                   placeholder="Votre adresse email"/>
        </div>

        <div class="modal-field">
            <label for="champ-mdp-r">Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
            <input type="password" id="champ-mdp-r" placeholder="••••••••"/>
        </div>

        <div class="modal-field">
            <label for="champ-role-r">Rôle</label>
            <select id="champ-role-r">
                <option value="client"       <?= (($utilisateurConnecte['role'] ?? '') === 'client')       ? 'selected' : '' ?>>Client</option>
                <option value="livreur"      <?= (($utilisateurConnecte['role'] ?? '') === 'livreur')      ? 'selected' : '' ?>>Livreur</option>
                <option value="restaurateur" <?= (($utilisateurConnecte['role'] ?? '') === 'restaurateur') ? 'selected' : '' ?>>Restaurateur</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="modal-btn-cancel" onclick="fermerModaleProfil()">Annuler</button>
            <button class="modal-btn-submit-restaurateur" onclick="validerProfil()">
                Valider <span id="spinner-restaurateur" class="modal-spinner" style="display:none"></span>
            </button>
        </div>

    </div>
</div>

<script src="darkMode.js"></script>
</body>
</html>
