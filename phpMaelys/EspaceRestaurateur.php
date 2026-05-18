<?php
$role_requis = 'restaurateur';

$fichier_pmc  = 'data/PMC.json';
$data         = json_decode(file_get_contents($fichier_pmc), true);
$plats        = $data['plats'];
$menus        = $data['menus'];
$commandes    = $data['commandes'];

$message = '';
$erreur  = '';

// t'as pas soumis de formulaire, on passe direct à l'affichage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Changer le statut d'une commande
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

    // Basculer dispo d'un plat
    if ($action === 'toggle_dispo') {
        $nom_plat = $_POST['nom_plat'] ?? '';
        foreach ($data['plats'] as &$p) {
            if ($p['nom'] === $nom_plat) {
                // si le champ n'existe pas encore, on le crée à true puis on bascule
                $p['disponible'] = isset($p['disponible']) ? !$p['disponible'] : false;
                break;
            }
        }
        unset($p);
        file_put_contents($fichier_pmc, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: restaurateur.php');
        exit;
    }

    // Ajouter une nouvelle commande
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

    // recharger les données fraîches
    $data      = json_decode(file_get_contents($fichier_pmc), true);
    $plats     = $data['plats'];
    $menus     = $data['menus'];
    $commandes = $data['commandes'];
}

// commandes actives = tout sauf "Livrée"
$commandes_actives  = array_filter($commandes, fn($c) => $c['statut'] !== 'Livrée');
$commandes_terminees = array_filter($commandes, fn($c) => $c['statut'] === 'Livrée');

// couleurs par statut
$couleurs_statut = [
    'En attente'    => '#6b7280',
    'En préparation'=> '#d97706',
    'Prête'         => '#16a34a',
    'En livraison'  => '#2563eb',
    'Livrée'        => '#4b5563',
];

$statuts_liste = ['En attente', 'En préparation', 'Prête', 'En livraison', 'Livrée'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Restaurateur</title>
    <link rel="stylesheet" href="styless.css">

</head>
<body>

<header>
    <nav>
        <a href="restaurateur.php">Mes commandes</a>
        <a href="compte.php">Mon compte</a>
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>

<h1>Espace Restaurateur</h1>

<div class="meaux">

    <?php if ($message): ?>
        <div class="rosny"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="sartrouville"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- stats rapides -->
    <div class="evry95">
        <div class="draveil">
            <div class="draveil-nombre"><?= count($commandes_actives) ?></div>
            <div class="draveil-label">Commandes actives</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre" style="color:#d97706">
                <?= count(array_filter($commandes, fn($c) => $c['statut'] === 'En préparation')) ?>
            </div>
            <div class="draveil-label">En préparation</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre" style="color:#16a34a">
                <?= count(array_filter($commandes, fn($c) => $c['statut'] === 'Prête')) ?>
            </div>
            <div class="draveil-label">Prêtes</div>
        </div>
        <div class="draveil">
            <div class="draveil-nombre" style="color:#4b5563">
                <?= count($commandes_terminees) ?>
            </div>
            <div class="draveil-label">Livrées aujourd'hui</div>
        </div>
    </div>

    <!-- grille principale -->
    <div class="melun">

        <!-- ===== COLONNE GAUCHE : commandes ===== -->
        <div>

            <!-- Commandes actives -->
            <div class="chartres">
                <div class="fontainebleau">
                    Commandes en cours
                    <span><?= count($commandes_actives) ?></span>
                </div>
                <div class="rambouillet">

                    <?php if (empty($commandes_actives)): ?>
                        <p class="grigny">Aucune commande active pour le moment.</p>
                    <?php else: ?>

                        <?php foreach ($commandes_actives as $c):
                            $couleur = $couleurs_statut[$c['statut']] ?? '#6b7280';
                            $type_class = 'conflans-' . $c['type'];
                        ?>
                        <div class="mantes">

                            <div class="poissy">
                                <div class="poissy-gauche">
                                    <span class="numero-commande">#<?= $c['numero'] ?></span>
                                    <span class="conflans <?= $type_class ?>">
                                        <?= $c['type'] === 'livraison' ? 'Livraison' : 'Sur place' ?>
                                    </span>
                                </div>
                                <span class="argentan"
                                      style="background:<?= $couleur ?>">
                                    <?= htmlspecialchars($c['statut']) ?>
                                </span>
                            </div>

                            <div class="houilles">
                                <p><strong>Client :</strong> <?= htmlspecialchars($c['client']) ?></p>
                                <p><strong>Heure :</strong> <?= $c['heure'] ?></p>
                                <p><strong>Plats :</strong> <?= htmlspecialchars($c['plats']) ?></p>
                                <?php if (!empty($c['adresse'])): ?>
                                    <p><strong>Adresse :</strong> <?= htmlspecialchars($c['adresse']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($c['livreur'])): ?>
                                    <p><strong>Livreur :</strong> <?= htmlspecialchars($c['livreur']) ?></p>
                                <?php endif; ?>

                                <!-- Changement de statut -->
                                <form method="POST" action="" class="clichy">
                                    <input type="hidden" name="action" value="changer_statut">
                                    <input type="hidden" name="numero" value="<?= $c['numero'] ?>">
                                    <select name="nouveau_statut">
                                        <?php foreach ($statuts_liste as $s): ?>
                                            <option value="<?= $s ?>"
                                                <?= $s === $c['statut'] ? 'selected' : '' ?>>
                                                <?= $s ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Mettre à jour</button>
                                </form>
                            </div>

                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>

            <!-- Commandes terminées (repliées visuellement) -->
            <?php if (!empty($commandes_terminees)): ?>
            <div class="chartres">
                <div class="fontainebleau" style="background:#4b5563;">
                    Commandes livrées aujourd'hui
                    <span style="color:#4b5563;"><?= count($commandes_terminees) ?></span>
                </div>
                <div class="rambouillet">
                    <?php foreach ($commandes_terminees as $c): ?>
                    <div class="mantes">
                        <div class="poissy">
                            <div class="poissy-gauche">
                                <span class="numero-commande" style="color:#9ca3af;">#<?= $c['numero'] ?></span>
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

            <!-- Ajouter une commande -->
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

        <!-- ===== COLONNE DROITE : menu + dispo ===== -->
        <div>
            <div class="chartres">
                <div class="fontainebleau">
                    Carte & disponibilités
                </div>
                <div class="rambouillet">

                    <!-- Plats -->
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
                        <form method="POST" action="" style="margin:0">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <!-- Desserts -->
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
                        <form method="POST" action="" style="margin:0">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <!-- Boissons -->
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
                        <form method="POST" action="" style="margin:0">
                            <input type="hidden" name="action"   value="toggle_dispo">
                            <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat['nom']) ?>">
                            <button type="submit" class="lognes">
                                <?= $dispo ? 'Retirer' : 'Remettre' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <!-- Menus -->
                    <p class="ozoir">Menus</p>
                    <?php foreach ($menus as $menu): ?>
                    <div class="noisiel" style="flex-wrap:wrap; gap:6px;">
                        <div style="flex:1; min-width:140px;">
                            <span class="noisiel-nom"><?= htmlspecialchars($menu['nom']) ?></span>
                            <span style="display:block; font-size:0.75rem; color:#aaa;">
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

</body>
</html>
