/**
 * Système de validation de formulaires côté client
 * Gère la validation en temps réel, au bout de 8h de code ça a marché heureusement que je fais plus d'info 
 */

class ValidateurFormulaire {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;

        this.champValidites = {};
        this.maxCaracteres = 128;

        this.init();
    }

    /**
     *  le début du code 
     */
    init() {
        // le début du code j'ai dit 
        const inputs = this.form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], select');
        
        inputs.forEach(input => {
            input.addEventListener('input', (e) => this.validerChamp(e.target));
            input.addEventListener('blur', (e) => this.validerChamp(e.target));
            
            input.addEventListener('input', (e) => this.limiterCaracteres(e.target));
        });

        const champsMotDePasse = this.form.querySelectorAll('input[type="password"]');
        champsMotDePasse.forEach(input => {
            this.ajouterToggleVisibilite(input);
        });

        this.form.addEventListener('submit', (e) => this.onSubmit(e));
    }

    /**
     * SIIUUUUUUUUU
     *
     */
    ajouterToggleVisibilite(input) {
        const container = input.parentElement;
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        wrapper.style.cssText = 'position: relative; display: inline-block; width: 100%;';

        // créer un bouton
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'toggle-password';
        btn.innerHTML = '👁️'; // Œil ouvert
        btn.title = 'Afficher/masquer le mot de passe';
        btn.style.cssText = `
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 4px 8px;
            transition: opacity 0.3s;
            z-index: 10;
        `;

        // etat du toggle
        let estVisible = false;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            estVisible = !estVisible;
            input.type = estVisible ? 'text' : 'password';
            btn.innerHTML = estVisible ? '🙈' : '👁️';
            btn.title = estVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
        });

        // Ajouter du padding
        input.style.paddingRight = '45px';

        // rajouter du ba
        input.parentElement.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(btn);
    }

    /**
     * C'est Nul l'info
     * 
     */
    limiterCaracteres(input) {
        if (input.value.length > this.maxCaracteres) {
            input.value = input.value.substring(0, this.maxCaracteres);
        }

        // flm de tout expliquer 
        const compteur = input.parentElement.querySelector('.compteur-chars');
        if (compteur) {
            compteur.textContent = `${input.value.length}/${this.maxCaracteres}`;
        }
    }

    /**
     * who he is ? 
     * 
     */
    validerChamp(input) {
        // uuuiiimmm
        if (input.tagName === 'SELECT' || input.type === 'checkbox') {
            return;
        }

        const nom = input.name;
        let estValide = true;
        let message = '';

        // j'ai besoin d'expliquer ?
        const valeur = input.value.trim();

        // les vérifs importantes 
        if (input.hasAttribute('required') && valeur === '') {
            estValide = false;
            message = 'Ce champ est obligatoire.';
        } else if (valeur !== '') {
            if (input.type === 'email') {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(valeur)) {
                    estValide = false;
                    message = 'Adresse email invalide.';
                }
            } else if (nom === 'mot_de_passe' || nom === 'mot_de_passe_confirmation') {
                if (valeur.length < 6) {
                    estValide = false;
                    message = 'Le mot de passe doit faire au moins 6 caractères.';
                }
                if (nom === 'mot_de_passe') {
                    this.afficherForceMotDePasse(input, valeur);
                }
            } else if (nom === 'pseudo') {
                const regexPseudo = /^[a-zA-Z0-9_]{3,}$/;
                if (!regexPseudo.test(valeur)) {
                    estValide = false;
                    message = 'Le pseudo doit contenir au moins 3 caractères (lettres, chiffres, tirets bas uniquement).';
                }
            } else if (nom === 'nom') {
                if (valeur.length < 2) {
                    estValide = false;
                    message = 'Le nom doit avoir au moins 2 caractères.';
                }
            }
        }

        this.champValidites[nom] = estValide;

        this.afficherValidation(input, estValide, message);
    }

    /**
     * Autre partie
     * 
     * 
     */
    afficherForceMotDePasse(input, mdp) {
        let force = 0;
        let label = 'Très faible';
        let couleur = '#dc2626'; // Rouge

        // Critères de force ( c'est moi le plus fort) 
        if (mdp.length >= 8) force += 20;
        if (mdp.length >= 12) force += 20;
        if (/[a-z]/.test(mdp)) force += 15;
        if (/[A-Z]/.test(mdp)) force += 15;
        if (/[0-9]/.test(mdp)) force += 15;
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(mdp)) force += 15;

        // la couleur 
        if (force >= 90) {
            label = 'Très fort';
            couleur = '#15803d'; // Vert foncé
        } else if (force >= 75) {
            label = 'Fort';
            couleur = '#16a34a'; // Vert
        } else if (force >= 60) {
            label = 'Moyen';
            couleur = '#d97706'; // Orange
        } else if (force >= 45) {
            label = 'Faible';
            couleur = '#ea580c'; // Orange foncé
        }

        // Cdddldd
        let barreForce = input.parentElement.parentElement.querySelector('.barre-force');
        if (!barreForce) {
            barreForce = document.createElement('div');
            barreForce.className = 'barre-force';
            barreForce.style.cssText = `
                width: 100%;
                height: 6px;
                background-color: #e5e7eb;
                border-radius: 3px;
                margin-top: 6px;
                overflow: hidden;
            `;
            const remplissage = document.createElement('div');
            remplissage.className = 'force-remplissage';
            remplissage.style.cssText = `
                height: 100%;
                width: 0%;
                transition: all 0.3s ease;
                background-color: ${couleur};
            `;
            barreForce.appendChild(remplissage);

            const labelForce = document.createElement('div');
            labelForce.className = 'label-force';
            labelForce.style.cssText = `
                margin-top: 4px;
                font-size: 12px;
                font-weight: 600;
                color: ${couleur};
                transition: color 0.3s;
            `;
            barreForce.appendChild(labelForce);

            input.parentElement.parentElement.appendChild(barreForce);
        }

        // bah oui ? 
        const remplissage = barreForce.querySelector('.force-remplissage');
        const labelForce = barreForce.querySelector('.label-force');
        remplissage.style.width = Math.min(force, 100) + '%';
        remplissage.style.backgroundColor = couleur;
        labelForce.textContent = label;
        labelForce.style.color = couleur;
    }

    /**
     * 
     * 
     * belle espace non ? 
     * 
     */
    afficherValidation(input, estValide, message) {
        // Supprimer le message d'erreur existant
        const ancienMessage = input.parentElement.querySelector('.erreur-message');
        if (ancienMessage) ancienMessage.remove();

        //  asnières sur seine 
        if (estValide || input.value.trim() === '') {
            input.style.borderColor = '';
            input.classList.remove('champ-erreur');
        } else {
            input.style.borderColor = '#dc2626';
            input.classList.add('champ-erreur');

            // afficher le message d'erreur
            const msgDiv = document.createElement('div');
            msgDiv.className = 'erreur-message';
            msgDiv.textContent = message;
            msgDiv.style.cssText = `
                color: #dc2626;
                font-size: 12px;
                margin-top: 4px;
                animation: slideDown 0.2s ease;
            `;
            input.parentElement.appendChild(msgDiv);
        }
    }

    /**
     * ifii
     *
     */
    onSubmit(e) {
        let formulaireValide = true;

        // 1. Valider les inputs textuels (nom, pseudo, email, mdp)
        const inputs = this.form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            if (input.hasAttribute('required')) {
                // nan jte valide t'as pas le choix 
                this.validerChamp(input);
                
                if (!this.champValidites[input.name]) {
                    formulaireValide = false;
                }
            }
        });

        // Validé 
        const selectRole = this.form.querySelector('select[name="role"]');
        if (selectRole && selectRole.hasAttribute('required') && selectRole.value === '') {
            formulaireValide = false;
        }

        // je valide ? 
        const mdp = this.form.querySelector('input[name="mot_de_passe"]');
        const mdpConf = this.form.querySelector('input[name="mot_de_passe_confirmation"]');
        
        if (mdp && mdpConf && mdp.value !== mdpConf.value) {
            this.afficherValidation(mdpConf, false, 'Les mots de passe ne correspondent pas.');
            formulaireValide = false;
        }

        // j'accepte 
        const checkbox = this.form.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.hasAttribute('required') && !checkbox.checked) {
            formulaireValide = false;
        }

        // nan tu viens pas 
        if (!formulaireValide) {
            e.preventDefault();
            alert('❌ Veuillez corriger les erreurs ou accepter les conditions avant de continuer.');
        }
    }
}

// Initialiser les formulaires quand tout est prêt ? 
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('form.vincennes')) {
        new ValidateurFormulaire('form.vincennes');
    }

    if (document.querySelector('form.poulpe')) {
        new ValidateurFormulaire('form.poulpe');
    }

    if (!document.querySelector('#animation-styles')) {
        const style = document.createElement('style');
        style.id = 'animation-styles';
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .champ-erreur {
                background-color: #fee2e2 !important;
            }
        `;
        document.head.appendChild(style);
    }
});