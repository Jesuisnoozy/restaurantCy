/**
 * darkMode.js
 * Gère le basculement entre mode sombre et mode clair
 */

// Initialiser le mode au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le mode sauvegardé dans localStorage
    const modeActuel = localStorage.getItem('mode-theme') || 'sombre';
    
    // Appliquer le mode
    appliquerMode(modeActuel);
    
    // Ajouter l'écouteur au bouton de basculement
    const toggleBtn = document.getElementById('toggle-mode-theme');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', basculeMode);
    }
});

// Fonction pour basculer le mode
function basculeMode() {
    const modeActuel = localStorage.getItem('mode-theme') || 'sombre';
    const nouveauMode = modeActuel === 'sombre' ? 'clair' : 'sombre';
    
    // Sauvegarder le nouveau mode
    localStorage.setItem('mode-theme', nouveauMode);
    
    // Appliquer le nouveau mode
    appliquerMode(nouveauMode);
}

// Fonction pour appliquer le mode
function appliquerMode(mode) {
    const body = document.body;
    const toggleBtn = document.getElementById('toggle-mode-theme');
    
    if (mode === 'clair') {
        // Mode clair
        body.classList.remove('mode-sombre');
        body.classList.add('mode-clair');
        
        if (toggleBtn) {
            toggleBtn.textContent = '🌙 Mode sombre';
        }
    } else {
        // Mode sombre (par défaut)
        body.classList.remove('mode-clair');
        body.classList.add('mode-sombre');
        
        if (toggleBtn) {
            toggleBtn.textContent = '☀️ Mode clair';
        }
    }
}
