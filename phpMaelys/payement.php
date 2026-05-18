
<?php include 'payerfcts.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page de payement</title>
    <link rel="stylesheet" href="styless.css">
</head>

<body class="payment-body">
<header class="payment-header">
    <div class="header-inner">
        <img src="goupix.webp" alt="Goupix" style="height:50px;margin-right:20px;">
        <h1 style="color:#EE7000;outline:5px solid black;">Paiement</h1>
        <img src="goupix.webp" alt="Goupix" style="height:50px;margin-left:20px;">
    </div>
</header>
<?php if(!empty($errorliste)):?>
    <div class="errors">
        <p><strong>Erreurs:</strong></p>
        <ul>
            <?php foreach($errorliste as $error):?>
                <li><?php echo htmlspecialchars($error,ENT_QUOTES,'UTF-8');?></li>
            <?php endforeach;?>
        </ul>
    </div>
<?php endif;?>
<?php if($succ):?>
    <div class="success">
        <p><?php echo htmlspecialchars($succ,ENT_QUOTES,'UTF-8');?></p>
    </div>
<?php endif;?>

<div class="form-container">
    <form method="post" class="payment-form">
        <p>Sous total: <?php echo htmlspecialchars($amount,ENT_QUOTES,'UTF-8');?> €</p>
        <p>
            <label>Nom du titulaire<br>
                <input type="text" name="cardholder" value="<?php echo htmlspecialchars($name,ENT_QUOTES,'UTF-8');?>">
            </label>
        </p>
        <p>
            <label>Numéro de carte<br>
                <input type="text" id="numcarte" name="numcarte" value="<?php echo htmlspecialchars($numcarte,ENT_QUOTES,'UTF-8');?>">
            </label>
        </p>
        <p>
            <label>Mois d'expiration<br>
                <input type="text" name="expiryMonth" value="<?php echo htmlspecialchars($expireM,ENT_QUOTES,'UTF-8');?>" placeholder="MM">
            </label>
        </p>
        <p>
            <label>Année d'expiration<br>
                <input type="text" name="expiryYear" value="<?php echo htmlspecialchars($expireY,ENT_QUOTES,'UTF-8');?>" placeholder="YYYY">
           </label>
        </p>
        <p>
            <label>CVV<br>
                <input type="text" id="cvv" name="cvv" value="<?php echo htmlspecialchars($cvv,ENT_QUOTES,'UTF-8');?>">
            </label>
        </p>
        <p><button type="submit">PAYER !</button></p>
    </form>
</div>

</body>
<!-- nombre de cafés(sur ce truc): 2-->
</html>
