if (!isset($_SESSION['pseudo'])){
    header('Location: connexion.php');
    exit;
}
 
if ($_SERVER['REQUEST_METHOD']!== 'POST'){
    header('Location: EspaceLivreur.php');
    exit;
}
 
$fichier= 'data/PMC.json';
$data= json_decode(file_get_contents($fichier), true);
$numero=intval($_POST['numero'] ?? 0);
$action= $_POST['action'] ?? '';
 
foreach ($data["commandes"] as $i=> $cmd) {
    if ($cmd["numero"]=== $numero){
        if ($action==='livree'){
            $data["commandes"][$i]["statut"]= "Livrée";
        }
        if ($action=== 'abandonnee') {
            $data["commandes"][$i]["statut"]= "Abandonnée";
        }
        break;
    }
}
 
file_put_contents(
    $fichier,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
 
header('Location: EspaceLivreur.php?message=Statut mis à jour !');
exit;
?>
