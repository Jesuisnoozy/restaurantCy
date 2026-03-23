<?php
// auth_admin.php — Gardien des pages admin
// À inclure en haut de admin.php

session_start();

// Pas connecté du tout → retour à la connexion
if (!isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit;
}

// Connecté mais pas admin → retour à sa propre page
if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . $_SESSION['role'] . '.php');
    exit;
}
