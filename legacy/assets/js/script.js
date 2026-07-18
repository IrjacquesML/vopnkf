// Gestion du menu responsive
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar');
    const navContainer = document.querySelector('.nav-container');
    const navMenu = document.querySelector('.nav-menu');

    if (navbar && navContainer && navMenu && !document.querySelector('.nav-toggle')) {
        // Créer l'overlay
        const navOverlay = document.createElement('div');
        navOverlay.className = 'nav-overlay';
        document.body.appendChild(navOverlay);

        // Créer le bouton hamburger
        const navToggle = document.createElement('button');
        navToggle.className = 'nav-toggle';
        navToggle.setAttribute('aria-label', 'Toggle navigation');
        navToggle.innerHTML = '<span></span><span></span><span></span>';

        const navLogo = document.querySelector('.nav-logo');
        if (navLogo) {
            navLogo.after(navToggle);
        }

        // Toggle menu
        navToggle.addEventListener('click', function () {
            const isActive = navMenu.classList.contains('active');

            if (isActive) {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                navToggle.classList.add('active');
                navMenu.classList.add('active');
                navOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });

        // Fermer avec overlay
        navOverlay.addEventListener('click', function () {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
            navOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Fermer sur resize
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // Gestion du masquage/affichage de la navbar au scroll (mobile uniquement)
    if (navbar) {
        let lastScrollTop = 0;
        let scrollThreshold = 5; // Seuil minimum de scroll pour déclencher l'action
        let isScrolling;

        window.addEventListener('scroll', function () {
            // Annuler le timeout précédent
            clearTimeout(isScrolling);

            // Définir un nouveau timeout pour détecter la fin du scroll
            isScrolling = setTimeout(function () {
                // Vérifier si on est sur mobile
                if (window.innerWidth <= 768) {
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    // Ignorer les petits mouvements
                    if (Math.abs(scrollTop - lastScrollTop) < scrollThreshold) {
                        return;
                    }

                    // Scroll vers le bas - masquer la navbar
                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        navbar.classList.add('hidden');
                    }
                    // Scroll vers le haut - afficher la navbar
                    else if (scrollTop < lastScrollTop) {
                        navbar.classList.remove('hidden');
                    }

                    // En haut de la page, toujours afficher
                    if (scrollTop <= 50) {
                        navbar.classList.remove('hidden');
                    }

                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                } else {
                    // Sur desktop, toujours afficher la navbar
                    navbar.classList.remove('hidden');
                }
            }, 50); // Délai de 50ms pour optimiser les performances
        }, { passive: true });

        // Réinitialiser lors du redimensionnement
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                navbar.classList.remove('hidden');
            }
        });
    }

    // Gestion de l'affichage des versets bibliques
    // Récupérer tous les éléments de verset biblique
    const verseElements = document.querySelectorAll('.bible-verse');
    const modal = document.getElementById('verseModal');

    if (modal) {
        const closeBtn = modal.querySelector('.close');
        const verseReference = document.getElementById('verseReference');
        const verseText = document.getElementById('verseText');

        // Ajouter un événement click sur chaque verset
        verseElements.forEach(function (element) {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function () {
                const reference = this.getAttribute('data-reference');
                const livre = this.getAttribute('data-livre');
                const chapitre = this.getAttribute('data-chapitre');
                const verset = this.getAttribute('data-verset');

                // Afficher le modal avec un indicateur de chargement amélioré
                verseReference.innerHTML = '<span class="verse-loading"></span>' + reference;
                verseText.innerHTML = '<div class="verse-loading"></div>Chargement du verset...';
                modal.style.display = 'block';

                // Ajouter un effet visuel sur le verset cliqué
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 4px 15px rgba(212, 175, 55, 0.4)';

                // Récupérer le verset via AJAX avec timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 secondes timeout

                // Chemin de l'API configurable selon la page (défini via VERSE_API_PATH avant ce script)
                const _apiPath = (typeof VERSE_API_PATH !== 'undefined') ? VERSE_API_PATH : '../../api/get_verset.php';

                fetch(`${_apiPath}?reference=${encodeURIComponent(reference)}&livre=${encodeURIComponent(livre)}&chapitre=${chapitre}&verset=${encodeURIComponent(verset)}`, {
                    signal: controller.signal
                })
                    .then(response => {
                        clearTimeout(timeoutId);
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Réinitialiser le style du verset cliqué
                        this.style.transform = '';
                        this.style.boxShadow = '';

                        if (data.success) {
                            verseReference.textContent = reference;
                            verseText.innerHTML = `<span class="icon-bible"></span> ${data.texte}`;
                            if (data.version) {
                                const isEnglish = data.version === 'WEB' || data.source === 'bible-api.com';
                                const versionLabel = isEnglish
                                    ? `<span style="font-size:.75em;background:#fff3cd;color:#856404;padding:1px 6px;border-radius:3px;margin-left:4px;" title="Texte anglais — Louis Segond non disponible pour ce verset">🌐 ${data.version}</span>`
                                    : ` (${data.version})`;
                                verseReference.innerHTML = reference + versionLabel;
                            }

                            // Ajouter une animation de succès subtile
                            verseText.style.animation = 'fadeIn 0.5s ease';
                        } else {
                            verseReference.textContent = reference;
                            verseText.innerHTML = `<span style="color: var(--vert-foret);">❌</span> ${data.message || 'Verset non trouvé. Veuillez consulter votre Bible.'}`;
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        // Réinitialiser le style du verset cliqué
                        this.style.transform = '';
                        this.style.boxShadow = '';

                        if (error.name === 'AbortError') {
                            verseReference.textContent = reference;
                            verseText.innerHTML = `<span style="color: #f44336;">⏱️</span> Le chargement a pris trop de temps. Veuillez réessayer.`;
                        } else {
                            verseReference.textContent = reference;
                            verseText.innerHTML = `<span style="color: #f44336;">❌</span> Erreur lors du chargement du verset. Veuillez réessayer.`;
                        }
                        console.error('Erreur:', error);
                    });
            });

            // Ajouter un effet hover amélioré
            element.addEventListener('mouseenter', function () {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'all 0.2s ease';
            });

            element.addEventListener('mouseleave', function () {
                if (this.style.transform !== 'scale(1.05)') { // Ne pas réinitialiser si cliqué
                    this.style.transform = '';
                }
            });
        });

        // Fermer le modal
        closeBtn.addEventListener('click', function () {
            closeModal();
        });

        // Fermer le modal en cliquant en dehors
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Fermer le modal avec la touche Échap
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });

        // Fonction pour fermer la modale
        function closeModal() {
            modal.style.display = 'none';
            // Réinitialiser les styles des versets
            verseElements.forEach(function (element) {
                element.style.transform = '';
                element.style.boxShadow = '';
            });
        }

        // Gestion du focus pour l'accessibilité
        modal.addEventListener('keydown', function (event) {
            if (event.key === 'Tab') {
                const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                if (event.shiftKey) {
                    if (document.activeElement === firstElement) {
                        event.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        event.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }

    // Validation du formulaire de quiz
    const quizForm = document.getElementById('quizForm');
    if (quizForm) {
        quizForm.addEventListener('submit', function (e) {
            const radioGroups = quizForm.querySelectorAll('input[type="radio"]');
            const questionNames = new Set();

            radioGroups.forEach(radio => {
                questionNames.add(radio.name);
            });

            let allAnswered = true;
            questionNames.forEach(name => {
                const checked = quizForm.querySelector(`input[name="${name}"]:checked`);
                if (!checked) {
                    allAnswered = false;
                }
            });

            if (!allAnswered) {
                e.preventDefault();
                alert('Veuillez répondre à toutes les questions avant de soumettre.');
                return false;
            }

            // Confirmation avant soumission
            if (!confirm('Êtes-vous sûr de vouloir soumettre vos réponses? Vous ne pourrez pas les modifier après.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
