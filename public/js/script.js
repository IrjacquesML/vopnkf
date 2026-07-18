// Gestion du menu responsive
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar');
    const navContainer = document.querySelector('.nav-container');
    const navMenu = document.querySelector('.nav-menu');

    // Ne pas recréer un toggle si la page en a déjà un (layouts app/admin)
    if (navbar && navContainer && navMenu && !document.querySelector('.nav-toggle') && !document.body.classList.contains('admin-body') && !document.body.classList.contains('app-body')) {
        const navOverlay = document.createElement('div');
        navOverlay.className = 'nav-overlay';
        document.body.appendChild(navOverlay);

        const navToggle = document.createElement('button');
        navToggle.className = 'nav-toggle';
        navToggle.setAttribute('aria-label', 'Toggle navigation');
        navToggle.innerHTML = '<span></span><span></span><span></span>';

        const navLogo = document.querySelector('.nav-logo');
        if (navLogo) {
            navLogo.after(navToggle);
        }

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

        navOverlay.addEventListener('click', function () {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
            navOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    if (navbar && !document.body.classList.contains('admin-body') && !document.body.classList.contains('app-body')) {
        let lastScrollTop = 0;
        const scrollThreshold = 5;
        let isScrolling;

        window.addEventListener('scroll', function () {
            clearTimeout(isScrolling);

            isScrolling = setTimeout(function () {
                if (window.innerWidth <= 768) {
                    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    if (Math.abs(scrollTop - lastScrollTop) < scrollThreshold) {
                        return;
                    }

                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        navbar.classList.add('hidden');
                    } else if (scrollTop < lastScrollTop) {
                        navbar.classList.remove('hidden');
                    }

                    if (scrollTop <= 50) {
                        navbar.classList.remove('hidden');
                    }

                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                } else {
                    navbar.classList.remove('hidden');
                }
            }, 50);
        }, { passive: true });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                navbar.classList.remove('hidden');
            }
        });
    }

    // Versets bibliques — clic → API
    const modal = document.getElementById('verseModal');

    if (modal) {
        const closeBtn = modal.querySelector('.close');
        const verseReference = document.getElementById('verseReference');
        const verseText = document.getElementById('verseText');
        const apiPath = (typeof VERSE_API_PATH !== 'undefined') ? VERSE_API_PATH : '/api/versets';

        document.addEventListener('click', function (event) {
            const element = event.target.closest('.bible-verse[data-reference], .bible-verse[data-livre]');
            if (!element || element.closest('.hero-content')) {
                return;
            }

            event.preventDefault();

            const reference = element.getAttribute('data-reference') || '';
            const livre = element.getAttribute('data-livre') || '';
            const chapitre = element.getAttribute('data-chapitre') || '';
            const verset = element.getAttribute('data-verset') || '';

            verseReference.innerHTML = '<span class="verse-loading"></span> ' + reference;
            verseText.innerHTML = '<div class="verse-loading"></div> Chargement du verset...';
            modal.style.display = 'block';

            element.style.transform = 'scale(1.05)';
            element.style.boxShadow = '0 4px 15px rgba(212, 175, 55, 0.4)';

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000);
            const params = new URLSearchParams({ reference, livre, chapitre, verset });

            fetch(`${apiPath}?${params.toString()}`, { signal: controller.signal })
                .then(response => response.json())
                .then(data => {
                    clearTimeout(timeoutId);
                    element.style.transform = '';
                    element.style.boxShadow = '';

                    if (data.success) {
                        const displayRef = data.reference || reference;
                        verseReference.textContent = displayRef;
                        verseText.textContent = data.texte || '';
                        if (data.version) {
                            const isEnglish = data.version === 'WEB' || data.source === 'bible-api.com';
                            const versionLabel = isEnglish
                                ? ` <span style="font-size:.75em;background:#fff3cd;color:#856404;padding:1px 6px;border-radius:3px;" title="Texte anglais">🌐 ${data.version}</span>`
                                : ` (${data.version})`;
                            verseReference.innerHTML = displayRef + versionLabel;
                        }
                    } else {
                        verseReference.textContent = reference || 'Verset';
                        verseText.innerHTML = '❌ ' + (data.message || 'Verset non trouvé.');
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    element.style.transform = '';
                    element.style.boxShadow = '';
                    verseReference.textContent = reference || 'Verset';
                    verseText.innerHTML = error.name === 'AbortError'
                        ? '⏱️ Délai dépassé. Réessayez.'
                        : '❌ Erreur lors du chargement du verset.';
                    console.error('Erreur verset:', error);
                });
        });

        function closeModal() {
            modal.style.display = 'none';
            verseReference.textContent = '';
            verseText.textContent = '';
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
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

            if (!confirm('Êtes-vous sûr de vouloir soumettre vos réponses? Vous ne pourrez pas les modifier après.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
