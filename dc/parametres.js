/**
 * parametres.js
 * Gestion asynchrone (fetch) de la modification du profil — page Paramètres.
 * Partagé par les 3 espaces (client, livreur, restaurateur).
 */

"use strict";

/* ────────────────────────────────────────────
   Feedback
──────────────────────────────────────────── */

function afficherSucces(message) {
    const el = document.getElementById("ouganda");
    el.textContent = message;
    el.className = "mozambique zambia";     // fond vert
    el.scrollIntoView({ behavior: "smooth", block: "center" });
}

function afficherErreur(message) {
    const el = document.getElementById("ouganda");
    el.textContent = message;
    el.className = "mozambique ethiopia";   // fond rouge
    el.scrollIntoView({ behavior: "smooth", block: "center" });
}

function cacherFeedback() {
    const el = document.getElementById("ouganda");
    el.textContent = "";
    el.className = "mozambique";
}

/* ────────────────────────────────────────────
   Spinner
──────────────────────────────────────────── */

function afficherSpinner(visible) {
    const btn     = document.getElementById("btn-valider");
    const spinner = btn.querySelector(".cameroun");

    if (visible) {
        btn.disabled = true;
        if (!spinner) {
            const s = document.createElement("span");
            s.className = "cameroun";
            btn.appendChild(s);
        }
    } else {
        btn.disabled = false;
        if (spinner) spinner.remove();
    }
}

/* ────────────────────────────────────────────
   Validation & envoi asynchrone
──────────────────────────────────────────── */

async function validerProfil() {
    cacherFeedback();

    const nom    = document.getElementById("champ-nom").value.trim();
    const pseudo = document.getElementById("champ-pseudo").value.trim();
    const email  = document.getElementById("champ-email").value.trim();
    const mdp    = document.getElementById("champ-mdp").value;
    const role   = document.getElementById("champ-role").value;

    /* ── Validation côté client ── */
    if (!nom) {
        afficherErreur("Le nom ne peut pas être vide.");
        return;
    }
    if (!pseudo) {
        afficherErreur("Le pseudo ne peut pas être vide.");
        return;
    }
    if (!email || !email.includes("@")) {
        afficherErreur("Veuillez saisir un email valide.");
        return;
    }
    if (mdp && mdp.length < 6) {
        afficherErreur("Le mot de passe doit contenir au moins 6 caractères.");
        return;
    }

    /* ── Envoi asynchrone ── */
    afficherSpinner(true);

    try {
        const payload = { nom, pseudo, email, role };
        if (mdp) payload.mot_de_passe = mdp;

        const reponse = await fetch("updateProfil.php", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify(payload),
        });

        const resultat = await reponse.json();

        if (resultat.succes) {
            afficherSucces("✅ " + (resultat.message ?? "Profil mis à jour avec succès !"));

            /* ── Redirection si changement de rôle ── */
            if (resultat.nouveauRole && resultat.nouveauRole !== utilisateurSession.role) {
                setTimeout(() => {
                    switch (resultat.nouveauRole) {
                        case "client":
                            window.location.href = "EspaceClient.php";
                            break;
                        case "livreur":
                            window.location.href = "EspaceLivreur.php";
                            break;
                        case "restaurateur":
                            window.location.href = "EspaceRestaurateur.php";
                            break;
                        default:
                            window.location.reload();
                    }
                }, 1400);
            } else {
                /* Même rôle : simple rechargement après confirmation visuelle */
                setTimeout(() => window.location.reload(), 1400);
            }
        } else {
            afficherErreur("❌ " + (resultat.erreur ?? "Une erreur est survenue. Veuillez réessayer."));
        }

    } catch (err) {
        console.error("Erreur réseau :", err);
        afficherErreur("❌ Impossible de contacter le serveur. Vérifiez votre connexion.");
    } finally {
        afficherSpinner(false);
    }
}
