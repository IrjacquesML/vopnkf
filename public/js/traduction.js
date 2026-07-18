/**
 * Widget de traduction VOP — traduit le contenu des leçons
 * tout en préservant la structure HTML (versets, titres, listes).
 */
class TraducteurVOP {
    constructor(options = {}) {
        this.langueActuelle = 'fr';
        this.langueUtilisateur = options.langue || document.documentElement.getAttribute('data-langue') || 'fr';
        this.apiUrl = options.apiUrl || '/api/traduire';
        this.csrfToken = options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
        this.langues = options.langues || {
            fr: 'Français',
            en: 'English',
            es: 'Español',
            pt: 'Português',
            sw: 'Kiswahili',
            ln: 'Lingala',
            kg: 'Kikongo',
            ar: 'العربية',
            zh: '中文',
            de: 'Deutsch',
            it: 'Italiano',
            ru: 'Русский',
        };
        this.originalNodes = new WeakMap();
        this.init();
    }

    init() {
        if (!document.querySelector('[data-traduire]')) {
            return;
        }

        this.creerWidget();
        this.attacherEvenements();

        if (this.langueUtilisateur && this.langueUtilisateur !== 'fr') {
            this.traduireContenu(this.langueUtilisateur);
        }
    }

    creerWidget() {
        if (document.getElementById('traduction-widget')) {
            return;
        }

        const optionsHtml = Object.entries(this.langues)
            .map(([code, label]) => `<option value="${code}">${label}</option>`)
            .join('');

        const widget = document.createElement('div');
        widget.id = 'traduction-widget';
        widget.className = 'traduction-widget';
        widget.innerHTML = `
            <div class="traduction-header" role="button" tabindex="0" aria-expanded="false">
                <span class="traduction-icon" aria-hidden="true">🌐</span>
                <span class="traduction-label">Traduire la leçon</span>
            </div>
            <div class="traduction-content" hidden>
                <label class="traduction-field-label" for="langue-select">Choisir une langue</label>
                <select id="langue-select" class="traduction-select">${optionsHtml}</select>
                <button type="button" id="traduire-btn" class="traduction-btn">Traduire</button>
                <button type="button" id="original-btn" class="traduction-btn traduction-btn-secondary" hidden>Texte original (FR)</button>
                <div id="traduction-loader" class="traduction-loader" hidden>
                    <span class="loader-spinner"></span> Traduction en cours…
                </div>
                <p class="traduction-hint">Le titre, le contenu et les questions sont traduits. Les versets bibliques restent cliquables.</p>
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

        const toggle = () => {
            const open = !content.hidden;
            content.hidden = open;
            header.setAttribute('aria-expanded', String(!open));
        };

        header?.addEventListener('click', toggle);
        header?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle();
            }
        });

        if (langueSelect) {
            langueSelect.value = this.langues[this.langueUtilisateur] ? this.langueUtilisateur : 'fr';
        }

        traduireBtn?.addEventListener('click', () => {
            this.traduireContenu(langueSelect.value);
        });

        originalBtn?.addEventListener('click', () => {
            this.restaurerOriginal();
            if (langueSelect) langueSelect.value = 'fr';
        });
    }

    collectTextNodes(root) {
        const nodes = [];
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                if (!node.nodeValue || !node.nodeValue.trim()) {
                    return NodeFilter.FILTER_REJECT;
                }
                const parent = node.parentElement;
                if (!parent) return NodeFilter.FILTER_REJECT;
                const tag = parent.tagName;
                if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'NOSCRIPT') {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            },
        });

        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }

        return nodes;
    }

    async traduireContenu(langue) {
        if (langue === 'fr') {
            this.restaurerOriginal();
            return;
        }

        const loader = document.getElementById('traduction-loader');
        const traduireBtn = document.getElementById('traduire-btn');
        const originalBtn = document.getElementById('original-btn');
        const roots = document.querySelectorAll('[data-traduire]');

        if (!roots.length) return;

        if (loader) loader.hidden = false;
        if (traduireBtn) traduireBtn.disabled = true;

        try {
            for (const root of roots) {
                const textNodes = this.collectTextNodes(root);

                for (const node of textNodes) {
                    if (!this.originalNodes.has(node)) {
                        this.originalNodes.set(node, node.nodeValue);
                    }

                    const original = this.originalNodes.get(node);
                    const leading = original.match(/^\s*/)?.[0] ?? '';
                    const trailing = original.match(/\s*$/)?.[0] ?? '';
                    const core = original.trim();

                    if (!core) continue;

                    const translated = await this.appellerAPITraduction(core, langue);
                    node.nodeValue = leading + (translated || core) + trailing;
                }
            }

            this.langueActuelle = langue;
            if (originalBtn) originalBtn.hidden = false;
            document.documentElement.setAttribute('lang', langue);
            if (langue === 'ar') {
                document.querySelectorAll('[data-traduire]').forEach((el) => {
                    el.setAttribute('dir', 'rtl');
                });
            } else {
                document.querySelectorAll('[data-traduire]').forEach((el) => {
                    el.removeAttribute('dir');
                });
            }
        } catch (error) {
            console.error('Erreur de traduction:', error);
            alert('Erreur lors de la traduction. Vérifiez votre connexion et réessayez.');
        } finally {
            if (loader) loader.hidden = true;
            if (traduireBtn) traduireBtn.disabled = false;
        }
    }

    async appellerAPITraduction(texte, langue) {
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                texte,
                langue,
                source: 'fr',
            }),
        });

        if (!response.ok) {
            throw new Error('Erreur réseau (' + response.status + ')');
        }

        const data = await response.json();
        return data.traduction || texte;
    }

    restaurerOriginal() {
        document.querySelectorAll('[data-traduire]').forEach((root) => {
            root.removeAttribute('dir');
            this.collectTextNodes(root).forEach((node) => {
                if (this.originalNodes.has(node)) {
                    node.nodeValue = this.originalNodes.get(node);
                }
            });
        });

        this.langueActuelle = 'fr';
        document.documentElement.setAttribute('lang', 'fr');

        const originalBtn = document.getElementById('original-btn');
        if (originalBtn) originalBtn.hidden = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.VOP_TRADUCTION || {};
    window.traducteurVOP = new TraducteurVOP(cfg);
});
