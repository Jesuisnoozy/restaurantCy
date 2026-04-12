<?php
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
}// basé sur une vidéo youtube (c'était la manière la plus simple de regarder si le numéro de carte bancaire est valide);
// pour le tester utilisez des générateur en ligne de numéro de carte bleue

$errorliste=[];// les erreurs
$succ='';//succès?
$name='';//titulaire
$numcarte='';//bon c facile là
$expireM='';//je dois vrm faire ça?
$expireY='';// O.o
$cvv='';// code sécurité carte
$amount='50.00'; //à récup!

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

    if(empty($errorliste)){
        $paymentTime=date('Y-m-d H:i:s');
        $succ='Succès du payment à '.$paymentTime;
    }
}
// nombre de cafés(sur ce truc): 4
?>