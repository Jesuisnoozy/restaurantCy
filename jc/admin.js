/**
 * Bloquer les gens qu'on aime pas (notamment Maelys)
 */

class GestionnaireUtilisateurs {
    constructor() {
        this.apiUrl = 'api_bloquer_utilisateur.php';
        this.initListeners();
    }

    /**
     * Initialiser les event listeners pour les boutons de blocage/déblocage
     */
    initListeners() {
        // Boutons de blocage (dynamiques)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-bloquer')) {
                const pseudo = e.target.dataset.pseudo;
                this.bloquerUtilisateur(pseudo, e.target);
            }
            
            if (e.target.classList.contains('btn-debloquer')) {
                const pseudo = e.target.dataset.pseudo;
                this.debloquerUtilisateur(pseudo, e.target);
            }
        });
    }

    /**
     * Bloquer un utilisateur ( Comme mon Ex ) 
     * 
     * 
     */
    async bloquerUtilisateur(pseudo, bouton) {
        // Confirmation
        if (!confirm(`⚠️ Êtes-vous sûr de vouloir bloquer "${pseudo}" ?\n\nSa session sera terminée immédiatement.`)) {
            return;
        }

        await this.envoirRequete('bloquer', pseudo, bouton);
    }

    /**
     * 
     * Quoi ?
     * 
     */
    async debloquerUtilisateur(pseudo, bouton) {
        if (!confirm(`Êtes-vous sûr de vouloir débloquer "${pseudo}" ?`)) {
            return;
        }

        await this.envoirRequete('debloquer', pseudo, bouton);
    }

    /**
     * 
     * 
     * feur
     * 
     */
    async envoirRequete(action, pseudo, bouton) {
        const container = bouton.closest('.card-user') || bouton.closest('.pontoise');
        const messageDiv = container ? container.querySelector('.message-feedback') : null;

        // AAAAAAAAAAAA
        const feedback = messageDiv || this.creerMessageFeedback();
        if (!messageDiv) {
            bouton.parentElement.insertBefore(feedback, bouton.nextSibling);
        }

        // EEEEEE
        feedback.innerHTML = '⏳ Traitement en cours...';
        feedback.className = 'message-feedback loading';
        bouton.disabled = true;

        try {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('pseudo', pseudo);

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Succès ✅
                feedback.innerHTML = `✅ ${data.message}`;
                feedback.className = 'message-feedback success';

                // Mettre à jour l'interface
                this.mettreAJourInterface(pseudo, action);

                // Enlève le message après 3 secondes
                setTimeout(() => {
                    feedback.style.opacity = '0';
                    setTimeout(() => feedback.remove(), 300);
                }, 3000);
            } else {
                // Erreur ❌
                feedback.innerHTML = `❌ ${data.message}`;
                feedback.className = 'message-feedback error';
                bouton.disabled = false;
            }
        } catch (erreur) {
            console.error('Erreur réseau:', erreur);
            feedback.innerHTML = `❌ Erreur de connexion au serveur.`;
            feedback.className = 'message-feedback error';
            bouton.disabled = false;
        }
    }

    /**
     * ¨Paisaieau villebeau
     */
    creerMessageFeedback() {
        const div = document.createElement('div');
        div.className = 'message-feedback';
        div.style.cssText = `
            padding: 12px 16px;
            margin: 12px 0;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        `;
        return div;
    }


    mettreAJourInterface(pseudo, action) {
        // uifi
        const cards = document.querySelectorAll('[data-pseudo]');
        let userCard = null;

        cards.forEach(card => {
            if (card.dataset.pseudo === pseudo) {
                userCard = card;
            }
        });

        if (!userCard) {
            // KKK
            document.querySelectorAll('.pontoise').forEach(card => {
                if (card.textContent.includes(`@${pseudo}`)) {
                    userCard = card;
                }
            });
        }

        if (userCard) {
            if (action === 'bloquer') {
                // Ajoute la classe suspendu mdrr
                const argenteuil = userCard.querySelector('.argenteuil');
                if (argenteuil) {
                    argenteuil.classList.add('suspendu_temp');
                    argenteuil.classList.remove('actif');
                }

                // il é où le bouton 
                const btnBloquer = userCard.querySelector('.btn-bloquer');
                if (btnBloquer) {
                    const btnDebloquer = document.createElement('button');
                    btnDebloquer.className = 'btn-debloquer pantin vitry';
                    btnDebloquer.dataset.pseudo = pseudo;
                    btnDebloquer.textContent = 'Débloquer';
                    btnBloquer.replaceWith(btnDebloquer);
                }

                // Mario
                const badgeActif = userCard.querySelector('.clamart-actif');
                if (badgeActif) {
                    badgeActif.textContent = 'Bloqué';
                    badgeActif.classList.remove('clamart-actif');
                    badgeActif.classList.add('clamart-temp');
                }

            } else if (action === 'debloquer') {
                // débloque mdrrr (pas comme mon ex) 
                const argenteuil = userCard.querySelector('.argenteuil');
                if (argenteuil) {
                    argenteuil.classList.remove('suspendu_temp', 'suspendu_def');
                    argenteuil.classList.add('actif');
                }

                // je suis beau ahahaha
                const btnDebloquer = userCard.querySelector('.btn-debloquer');
                if (btnDebloquer) {
                    const btnBloquer = document.createElement('button');
                    btnBloquer.className = 'btn-bloquer pantin aulnay';
                    btnBloquer.dataset.pseudo = pseudo;
                    btnBloquer.textContent = 'Bloquer';
                    btnDebloquer.replaceWith(btnBloquer);
                }

                // puff gout puff ? 
                const badgeTemp = userCard.querySelector('.clamart-temp, .clamart-def');
                if (badgeTemp && !userCard.querySelector('.clamart-vip')) {
                    badgeTemp.textContent = 'Actif';
                    badgeTemp.classList.remove('clamart-temp', 'clamart-def');
                    badgeTemp.classList.add('clamart-actif');
                }
            }
        }
    }
}

// Initialise le DOM 
document.addEventListener('DOMContentLoaded', () => {
    window.gestionnaire = new GestionnaireUtilisateurs();
});
