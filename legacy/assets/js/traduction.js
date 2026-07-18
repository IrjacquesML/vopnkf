/**
 * Widget de traduction pour VOP
 * Permet de traduire le contenu des leçons en temps réel
 */

class TraducteurVOP {
    constructor() {
        this.langueActuelle = 'fr';
        this.langueUtilisateur = document.documentElement.getAttribute('data-langue') || 'fr';
        this.contenuOriginal = {};
        this.init();
    }

    init() {
        this.creerWidget();
        this.attacherEvenements();
        
        // Charger la langue préférée de l'utilisateur
        if (this.langueUtilisateur !== 'fr') {
            this.traduireContenu(this.langueUtilisateur);
        }
    }

    creerWidget() {
        const widget = document.createElement('div');
        widget.id = 'traduction-widget';
        widget.className = 'traduction-widget';
        widget.innerHTML = `
            <div class="traduction-header">
                <span class="traduction-icon">🌍</span>
                <span class="traduction-label">Traduire</span>
            </div>
            <div class="traduction-content" style="display: none;">
                <select id="langue-select" class="traduction-select">
                    <option value="fr">Français</option>
                    <option value="en">English</option>
                    <option value="es">Español</option>
                    <option value="pt">Português</option>
                    <option value="sw">Kiswahili</option>
                    <option value="ln">Lingala</option>
                    <option value="kg">Kikongo</option>
                    <option value="ar">العربية</option>
                    <option value="zh">中文</option>
                    <option value="de">Deutsch</option>
                    <option value="it">Italiano</option>
                    <option value="ru">Русский</option>
                </select>
                <button id="traduire-btn" class="traduction-btn">Traduire</button>
                <button id="original-btn" class="traduction-btn traduction-btn-secondary" style="display: none;">Texte original</button>
                <div id="traduction-loader" class="traduction-loader" style="display: none;">
                    <span class="loader-spinner"></span> Traduction en cours...
                </div>
            </div>
        `;
        
        document.body.appendChild(widget);
    }

    attacherEvenements() {
        const header = document.querySelector('.traduction-header');
        const content = document.querySelector('.traduction-content');
        const langueSelect = document.getElementById('langue-select');
        const traduireBtn = document.getElementById('traduire-btn');
        const originalBtn = document.getElementById('original-btn');

        // Toggle widget
        header.addEventListener('click', () => {
            const isVisible = content.style.display === 'block';
            content.style.display = isVisible ? 'none' : 'block';
        });

        // Sélectionner la langue de l'utilisateur par défaut
        if (langueSelect) {
            langueSelect.value = this.langueUtilisateur;
        }

        // Bouton traduire
        if (traduireBtn) {
            traduireBtn.addEventListener('click', () => {
                const langue = langueSelect.value;
                this.traduireContenu(langue);
            });
        }

        // Bouton texte original
        if (originalBtn) {
            originalBtn.addEventListener('click', () => {
                this.restaurerOriginal();
            });
        }
    }

    async traduireContenu(langue) {
        if (langue === 'fr') {
            this.restaurerOriginal();
            return;
        }

        const loader = document.getElementById('traduction-loader');
        const traduireBtn = document.getElementById('traduire-btn');
        const originalBtn = document.getElementById('original-btn');

        // Afficher le loader
        if (loader) loader.style.display = 'block';
        if (traduireBtn) traduireBtn.disabled = true;

        try {
            // Récupérer tous les éléments à traduire
            const elementsATraduire = document.querySelectorAll('[data-traduire]');
            
            for (const element of elementsATraduire) {
                const type = element.getAttribute('data-traduire');
                const id = element.getAttribute('data-id');
                
                // Sauvegarder le contenu original
                if (!this.contenuOriginal[type + '_' + id]) {
                    this.contenuOriginal[type + '_' + id] = element.innerHTML;
                }

                // Appeler l'API de traduction
                const texte = element.textContent.trim();
                const texteTradu = await this.appellerAPITraduction(texte, langue);
                
                if (texteTradu) {
                    element.innerHTML = texteTradu;
                }
            }

            this.langueActuelle = langue;
            
            // Afficher le bouton "Texte original"
            if (originalBtn) originalBtn.style.display = 'inline-block';
            
        } catch (error) {
            console.error('Erreur de traduction:', error);
            alert('Erreur lors de la traduction. Veuillez réessayer.');
        } finally {
            // Masquer le loader
            if (loader) loader.style.display = 'none';
            if (traduireBtn) traduireBtn.disabled = false;
        }
    }

    async appellerAPITraduction(texte, langue) {
        try {
            const response = await fetch('../../api/traduire.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    texte: texte,
                    langue: langue
                })
            });

            if (!response.ok) {
                throw new Error('Erreur réseau');
            }

            const data = await response.json();
            return data.traduction || texte;
        } catch (error) {
            console.error('Erreur API:', error);
            return texte;
        }
    }

    restaurerOriginal() {
        const elementsATraduire = document.querySelectorAll('[data-traduire]');
        
        elementsATraduire.forEach(element => {
            const type = element.getAttribute('data-traduire');
            const id = element.getAttribute('data-id');
            const cle = type + '_' + id;
            
            if (this.contenuOriginal[cle]) {
                element.innerHTML = this.contenuOriginal[cle];
            }
        });

        this.langueActuelle = 'fr';
        
        const originalBtn = document.getElementById('original-btn');
        if (originalBtn) originalBtn.style.display = 'none';
    }
}

// Initialiser le traducteur au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    new TraducteurVOP();
});
