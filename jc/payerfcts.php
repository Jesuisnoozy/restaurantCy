<?php
// Algorithme de Luhn — inventé en 1954, toujours vivant, c'est lui le vrai senior dev
function cardchecker($cnumber){
    $sum=0;
    $a=false;
    for($i=strlen($cnumber)-1;$i>=0;$i--){
        $n=intval($cnumber[$i]);
        if($a){
            $n*=2;
            if($n>9){
                $n-=9;
            }
        }
        $sum+=$n;
        $a=!$a;
    }
    return $sum%10===0;
}// Basé sur une vidéo YouTube — la seule fois où yt-dl a rendu service à un projet scolaire

// Init des variables — oui il faut toutes les déclarer, sinon PHP crie
$errorliste=[];
$succ='';
$name='';
$numcarte='';
$expireM='';
$expireY='';
$cvv='';
// Calcul du montant dynamique — fini le hardcode à 50€, on est des pros maintenant
$fichier = 'data/PMC.json';
$dataJson = json_decode(file_get_contents($fichier), true);
$numeroCmd = intval($_GET['numero'] ?? $_POST['numero'] ?? 0);
$amount = '0.00';
foreach ($dataJson["commandes"] as $cmd) {
    if ($cmd["numero"] === $numeroCmd) {
        $total = 0;
        foreach ($dataJson["plats"] as $plat) {
            $prixPlat = floatval(str_replace(['€', ' ', ','], ['', '', '.'], $plat["prix"]));
            if (preg_match('/(\d+)x ' . preg_quote($plat["nom"], '/') . '/', $cmd["plats"], $match)) {
                $total += intval($match[1]) * $prixPlat;
            }
        }
        foreach ($dataJson["menus"] as $menu) {
            $prixMenu = floatval(str_replace(['€', ' ', ','], ['', '', '.'], $menu["prix"]));
            if (strpos($cmd["plats"], $menu["nom"]) !== false) {
                $total += $prixMenu;
            }
        }
        $amount = number_format($total, 2, '.', '');
        break;
    }
}

// Validation du formulaire — si t'as mis une carte à 0001 0001 0001 0001 tu mérites l'erreur
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['cardholder']??'');
    $numcarte=preg_replace('/\s+/','',$_POST['numcarte']??'');
    $numcarte=preg_replace('/\D/','',$numcarte);
    $expireM=$_POST['expiryMonth']??'';
    $expireY=$_POST['expiryYear']??'';
    $cvv=trim($_POST['cvv']??'');
    if($name===''){
        $errorliste[]='Nom du titulaire requis.';
    }
    if(!preg_match('/^[0-9]{13,19}$/',$numcarte)){
        $errorliste[]='Numéro de carte doit être composé de 13 à 19 chiffres.';
    }elseif(!cardchecker($numcarte)){
        $errorliste[]='Numéro de carte invalide.';
    }
    if(!preg_match('/^(0[1-9]|1[0-2])$/',$expireM)){
        $errorliste[]='Date d\'expiration invalide (mois).';
    }
    if(!preg_match('/^[0-9]{4}$/',$expireY)){
        $errorliste[]='Date d\'expiration invalide (année).';
    }
    if(empty($errorliste)){
        $currentyear=intval(date('Y'));
        $expYearInt=intval($expireY);
        $currentMonth=intval(date('m'));
        $expMonthInt=intval($expireM);
        if($expYearInt<$currentyear||($expYearInt===$currentyear&&$expMonthInt<$currentMonth)){
            $errorliste[]='La date d\'expiration est pasée.';
        }
    }
    if(!preg_match('/^[0-9]{3,4}$/',$cvv)){
        $errorliste[]='Le CVV doit être composé de 3 ou 4 chiffres.';
    }

    // Tout est bon → on encaisse et on met à jour le JSON
if(empty($errorliste)){
        $paymentTime=date('Y-m-d H:i:s');
        foreach ($dataJson["commandes"] as $i => $cmd) {
            if ($cmd["numero"] === $numeroCmd) {
                $dataJson["commandes"][$i]["paiement"] = "Payé";
                break;
            }
        }
        file_put_contents($fichier, json_encode($dataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: client.php?message=' . urlencode('Commande n°' . $numeroCmd . ' payée avec succès !'));
        exit;
    }
}
// Bilan café : 4 cafés pour ce fichier. Respect.
?>