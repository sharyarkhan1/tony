document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const body = document.body;
    document.addEventListener('keydown', (event) => {
        if (!(event.ctrlKey || event.metaKey)) return;
        if (['+', '=', '-', '_', '0'].includes(event.key) || ['NumpadAdd', 'NumpadSubtract'].includes(event.code)) {
            event.preventDefault();
        }
    });

    const pageLoader = document.querySelector('[data-page-loader]');
    if (pageLoader) {
        const hidePageLoader = () => {
            pageLoader.classList.add('is-hidden');
            pageLoader.setAttribute('aria-hidden', 'true');
        };

        if (document.readyState === 'complete') hidePageLoader();
        else window.addEventListener('load', hidePageLoader, { once: true });
    }

    const menuToggle = document.querySelector('.menu-toggle');
    const mobileDrawer = document.querySelector('.mobile-drawer');
    const drawerClose = document.querySelector('.drawer-close');
    const drawerBackdrop = document.querySelector('.drawer-backdrop');
    let focusBeforeDrawer = null;

    const closeDrawer = (restoreFocus = true) => {
        if (!mobileDrawer || !menuToggle) return;
        body.classList.remove('drawer-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Open navigation');
        mobileDrawer.setAttribute('aria-hidden', 'true');
        mobileDrawer.setAttribute('inert', '');
        if (restoreFocus && focusBeforeDrawer) focusBeforeDrawer.focus({ preventScroll: true });
    };

    const openDrawer = () => {
        if (!mobileDrawer || !menuToggle) return;
        focusBeforeDrawer = document.activeElement;
        body.classList.add('drawer-open');
        menuToggle.setAttribute('aria-expanded', 'true');
        menuToggle.setAttribute('aria-label', 'Close navigation');
        mobileDrawer.removeAttribute('inert');
        mobileDrawer.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(() => {
            const firstControl = drawerClose || mobileDrawer.querySelector('a[href], button:not([disabled])');
            if (firstControl) firstControl.focus();
        });
    };

    if (menuToggle && mobileDrawer) {
        menuToggle.addEventListener('click', () => {
            if (menuToggle.getAttribute('aria-expanded') === 'true') closeDrawer(false);
            else openDrawer();
        });

        if (drawerClose) drawerClose.addEventListener('click', () => closeDrawer());
        if (drawerBackdrop) drawerBackdrop.addEventListener('click', () => closeDrawer());

        mobileDrawer.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const hash = link.getAttribute('href');
                if (!hash || !hash.startsWith('#')) return;
                const destination = document.getElementById(hash.slice(1));
                if (!destination) return;
                event.preventDefault();
                closeDrawer(false);
                if (window.location.hash !== hash) window.history.pushState(null, '', hash);
                destination.setAttribute('tabindex', '-1');
                window.requestAnimationFrame(() => {
                    const headerHeight = header ? header.offsetHeight : 0;
                    const top = destination.getBoundingClientRect().top + window.scrollY - headerHeight;
                    window.scrollTo({ top, behavior: reducedMotion ? 'auto' : 'smooth' });
                    if (reducedMotion) destination.focus({ preventScroll: true });
                    else {
                        const focusAfterScroll = () => {
                            if (Math.abs(destination.getBoundingClientRect().top - headerHeight) > 2) return;
                            window.removeEventListener('scroll', focusAfterScroll);
                            destination.focus({ preventScroll: true });
                        };
                        window.addEventListener('scroll', focusAfterScroll, { passive: true });
                        window.setTimeout(() => {
                            window.removeEventListener('scroll', focusAfterScroll);
                            if (Math.abs(destination.getBoundingClientRect().top - headerHeight) <= 2) {
                                destination.focus({ preventScroll: true });
                            }
                        }, 1200);
                    }
                });
            });
        });

        document.addEventListener('keydown', (event) => {
            if (!body.classList.contains('drawer-open')) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                closeDrawer();
                return;
            }

            if (event.key === 'Tab') {
                const focusable = Array.from(mobileDrawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'));
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (!first || !last) return;
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991 && menuToggle.getAttribute('aria-expanded') === 'true') {
                closeDrawer(false);
            }
        }, { passive: true });
    }

    const header = document.querySelector('.site-header');
    const updateHeader = () => {
        if (header) header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    const sectionLinks = Array.from(document.querySelectorAll('.desktop-nav a[href^="#"], .nav-cta[href^="#"], .drawer-links a[href^="#"]'));
    const sections = Array.from(new Set(sectionLinks
        .map((link) => document.querySelector(link.getAttribute('href')))
        .filter((section) => section instanceof HTMLElement)));
    let sectionUpdateFrame = 0;
    const updateActiveSection = () => {
        sectionUpdateFrame = 0;
        const marker = window.scrollY + (header ? header.offsetHeight : 0) + Math.min(window.innerHeight * 0.35, 260);
        let activeSection = sections[0] || null;
        sections.forEach((section) => {
            if (section.getBoundingClientRect().top + window.scrollY <= marker) activeSection = section;
        });

        sectionLinks.forEach((link) => {
            const isActive = Boolean(activeSection && link.getAttribute('href') === `#${activeSection.id}`);
            link.classList.toggle('is-active', isActive);
            if (isActive) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };
    const requestActiveSectionUpdate = () => {
        if (sectionUpdateFrame) return;
        sectionUpdateFrame = window.requestAnimationFrame(updateActiveSection);
    };
    updateActiveSection();
    window.addEventListener('scroll', requestActiveSectionUpdate, { passive: true });
    window.addEventListener('resize', requestActiveSectionUpdate, { passive: true });

    const movesImageStage = document.querySelector('.moves-image-stage');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (movesImageStage && finePointer && !reducedMotion) {
        let pointerFrame = 0;
        let latestPointer = null;

        const resetImageLayer = () => {
            if (pointerFrame) window.cancelAnimationFrame(pointerFrame);
            pointerFrame = 0;
            latestPointer = null;
            movesImageStage.style.setProperty('--moves-pointer-x', '0px');
            movesImageStage.style.setProperty('--moves-pointer-y', '0px');
        };

        movesImageStage.addEventListener('pointermove', (event) => {
            latestPointer = event;
            if (pointerFrame) return;
            pointerFrame = window.requestAnimationFrame(() => {
                pointerFrame = 0;
                if (!latestPointer) return;
                const bounds = movesImageStage.getBoundingClientRect();
                const x = ((latestPointer.clientX - bounds.left) / bounds.width - 0.5) * 18;
                const y = ((latestPointer.clientY - bounds.top) / bounds.height - 0.5) * 18;
                movesImageStage.style.setProperty('--moves-pointer-x', `${x.toFixed(2)}px`);
                movesImageStage.style.setProperty('--moves-pointer-y', `${y.toFixed(2)}px`);
            });
        }, { passive: true });

        movesImageStage.addEventListener('pointerleave', resetImageLayer, { passive: true });
    }

    const faqItems = Array.from(document.querySelectorAll('.faq-item'));
    if (faqItems.length) {
        const setFaqItemOpen = (item, isOpen) => {
            const trigger = item.querySelector('.faq-question');
            const panel = item.querySelector('.faq-answer');
            if (!trigger || !panel) return;
            trigger.setAttribute('aria-expanded', String(isOpen));
            panel.setAttribute('aria-hidden', String(!isOpen));
            if (isOpen) panel.removeAttribute('inert');
            else panel.setAttribute('inert', '');
        };

        faqItems.forEach((item) => {
            const trigger = item.querySelector('.faq-question');
            if (!trigger) return;
            trigger.addEventListener('click', () => {
                const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';
                if (shouldOpen) {
                    faqItems.forEach((otherItem) => {
                        if (otherItem !== item) setFaqItemOpen(otherItem, false);
                    });
                }
                setFaqItemOpen(item, shouldOpen);
            });
        });
    }

    const marqueeTrack = document.querySelector('[data-marquee-track]');
    const marqueeBand = document.querySelector('.marquee-band');
    const marqueeGroup = marqueeTrack ? marqueeTrack.querySelector('[data-marquee-group]') : null;
    if (marqueeTrack && marqueeBand && marqueeGroup) {
        const originalMarqueeItems = Array.from(marqueeGroup.children).map((item) => item.cloneNode(true));
        let marqueeFrame = 0;

        const rebuildMarquee = () => {
            marqueeGroup.replaceChildren(...originalMarqueeItems.map((item) => item.cloneNode(true)));
            const minimumGroupWidth = marqueeBand.clientWidth * 1.2;
            let repetitions = 0;
            while (marqueeGroup.scrollWidth < minimumGroupWidth && repetitions < 50) {
                originalMarqueeItems.forEach((item) => marqueeGroup.append(item.cloneNode(true)));
                repetitions += 1;
            }

            const secondGroup = marqueeGroup.cloneNode(true);
            secondGroup.removeAttribute('data-marquee-group');
            secondGroup.setAttribute('aria-hidden', 'true');
            marqueeTrack.replaceChildren(marqueeGroup, secondGroup);
        };

        const scheduleMarqueeRebuild = () => {
            if (marqueeFrame) window.cancelAnimationFrame(marqueeFrame);
            marqueeFrame = window.requestAnimationFrame(() => {
                marqueeFrame = 0;
                rebuildMarquee();
            });
        };

        rebuildMarquee();
        if ('ResizeObserver' in window) {
            const marqueeObserver = new ResizeObserver(scheduleMarqueeRebuild);
            marqueeObserver.observe(marqueeBand);
        } else {
            window.addEventListener('resize', scheduleMarqueeRebuild, { passive: true });
        }
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(scheduleMarqueeRebuild);
    }

    const serviceModal = document.querySelector('[data-service-modal]');
    if (serviceModal) {
        const modalPanel = serviceModal.querySelector('.service-modal-panel');
        const modalClose = serviceModal.querySelector('[data-service-modal-close]');
        const modalImage = serviceModal.querySelector('[data-service-modal-image]');
        const modalTitle = serviceModal.querySelector('[data-service-modal-title]');
        const modalCategory = serviceModal.querySelector('[data-service-modal-category]');
        const modalSummary = serviceModal.querySelector('[data-service-modal-summary]');
        const modalDetail = serviceModal.querySelector('[data-service-modal-detail]');
        const serviceEnquiry = serviceModal.querySelector('[data-service-enquiry]');
        const serviceSelect = document.querySelector('#enquiry-service');
        let modalTrigger = null;
        let previousPageState = [];
        let hideModalTimer = 0;

        const setPageInert = (isInert) => {
            if (isInert) {
                previousPageState = Array.from(body.children)
                    .filter((element) => element !== serviceModal)
                    .map((element) => ({
                        element,
                        inert: element.inert,
                        ariaHidden: element.getAttribute('aria-hidden')
                    }));
                previousPageState.forEach(({ element }) => {
                    element.inert = true;
                    element.setAttribute('aria-hidden', 'true');
                });
                return;
            }

            previousPageState.forEach(({ element, inert, ariaHidden }) => {
                element.inert = inert;
                if (ariaHidden === null) element.removeAttribute('aria-hidden');
                else element.setAttribute('aria-hidden', ariaHidden);
            });
            previousPageState = [];
        };

        const openServiceModal = (trigger) => {
            if (hideModalTimer) window.clearTimeout(hideModalTimer);
            modalTrigger = trigger;
            modalTitle.textContent = trigger.dataset.title || '';
            modalCategory.textContent = trigger.dataset.category || '';
            modalSummary.textContent = trigger.dataset.summary || '';
            modalDetail.textContent = trigger.dataset.detail || '';
            modalImage.src = trigger.dataset.image || '';
            modalImage.alt = trigger.dataset.alt || '';
            modalImage.style.setProperty('--service-modal-image-position', trigger.dataset.imagePosition || '50% 50%');
            serviceModal.hidden = false;
            serviceModal.setAttribute('aria-hidden', 'false');
            body.classList.add('service-modal-open');
            window.requestAnimationFrame(() => {
                serviceModal.classList.add('is-open');
                modalClose.focus();
                setPageInert(true);
            });
        };

        const closeServiceModal = () => {
            if (serviceModal.hidden || !serviceModal.classList.contains('is-open')) return;
            serviceModal.classList.remove('is-open');
            serviceModal.setAttribute('aria-hidden', 'true');
            body.classList.remove('service-modal-open');
            setPageInert(false);
            if (modalTrigger && document.contains(modalTrigger)) modalTrigger.focus({ preventScroll: true });
            hideModalTimer = window.setTimeout(() => {
                serviceModal.hidden = true;
                hideModalTimer = 0;
            }, reducedMotion ? 0 : 320);
        };

        document.querySelectorAll('[data-service-details]').forEach((trigger) => {
            trigger.addEventListener('click', () => openServiceModal(trigger));
        });
        if (modalClose) modalClose.addEventListener('click', closeServiceModal);
        serviceModal.addEventListener('click', (event) => {
            if (event.target === serviceModal) closeServiceModal();
        });
        document.addEventListener('keydown', (event) => {
            if (serviceModal.hidden || !serviceModal.classList.contains('is-open')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeServiceModal();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(serviceModal.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                .filter((element) => element.getClientRects().length > 0);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (!first || !last) {
                event.preventDefault();
                modalPanel.focus();
            } else if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        if (serviceEnquiry) {
            serviceEnquiry.addEventListener('click', () => {
                const selectedService = modalTitle.textContent;
                if (serviceSelect && Array.from(serviceSelect.options).some((option) => option.value === selectedService)) {
                    serviceSelect.value = selectedService;
                    serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
                closeServiceModal();
                if (serviceSelect) window.setTimeout(() => serviceSelect.focus({ preventScroll: true }), 0);
            });
        }
    }

    const revealItems = document.querySelectorAll('[data-reveal]');
    if (!reducedMotion && 'IntersectionObserver' in window && revealItems.length) {
        body.classList.add('motion-ready');
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -55px 0px' });

        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    const setupHorizontalServices = () => {
        const stage = document.querySelector('[data-services-stage]');
        const pin = document.querySelector('[data-services-pin]');
        const viewport = document.querySelector('[data-services-viewport]');
        const track = document.querySelector('[data-services-track]');
        const cards = track ? Array.from(track.querySelectorAll('.service-card')) : [];
        const progressBar = document.querySelector('[data-services-progress]');
        const progressFill = progressBar ? progressBar.querySelector('span') : null;
        const counter = document.querySelector('[data-services-count]');

        if (!stage || !pin || !viewport || !track || !cards.length) return;

        let travelDistance = 0;
        let visibleCount = 4;
        let cardStep = 0;
        let currentFirstIndex = 0;
        let animationFrame = 0;
        let staticScrollFrame = 0;
        let mobileCarouselActive = false;

        const syncMobileCarousel = () => {
            const owlItems = Array.from(track.querySelectorAll('.owl-item'));
            const carousel = window.jQuery(track).data('owl.carousel');
            const carouselIndex = carousel && Number.isInteger(carousel.current())
                ? carousel.relative(carousel.current())
                : -1;
            const firstIndex = carouselIndex >= 0
                ? carouselIndex
                : owlItems.findIndex((item) => item.classList.contains('active'));
            if (firstIndex < 0) return;

            visibleCount = 1;
            currentFirstIndex = firstIndex;
            cards.forEach((card, index) => {
                const isVisible = index === firstIndex;
                card.classList.add('is-visible');
                card.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
                card.inert = !isVisible;
            });
            syncCounter(firstIndex, firstIndex / Math.max(1, cards.length - 1));

            const navButtons = Array.from(track.querySelectorAll('.owl-nav button'));
            navButtons.forEach((button, index) => {
                button.removeAttribute('role');
                button.setAttribute('aria-label', index === 0 ? 'Previous service' : 'Next service');
                button.disabled = button.classList.contains('disabled');
            });
            track.querySelectorAll('.owl-dot').forEach((button, index) => {
                button.removeAttribute('role');
                button.setAttribute('aria-label', `Show service ${index + 1} of ${cards.length}`);
                if (index === firstIndex) button.setAttribute('aria-current', 'page');
                else button.removeAttribute('aria-current');
            });
        };

        const destroyMobileCarousel = () => {
            if (!mobileCarouselActive) return;
            window.jQuery(track).trigger('destroy.owl.carousel');
            track.classList.remove('owl-carousel');
            mobileCarouselActive = false;
        };

        const stickyOffset = () => {
            const token = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--sticky-header-height'));
            return Number.isFinite(token) ? token : (header ? header.offsetHeight : 82);
        };

        const syncCounter = (firstIndex, progress) => {
            if (progressFill) progressFill.style.transform = `scaleX(${progress})`;
            if (progressBar) progressBar.setAttribute('aria-valuenow', String(Math.round(progress * 100)));
            if (!counter) return;
            const first = String(firstIndex + 1).padStart(2, '0');
            const last = String(Math.min(cards.length, firstIndex + visibleCount)).padStart(2, '0');
            counter.textContent = `${first}–${last} / ${String(cards.length).padStart(2, '0')}`;
        };

        const setVisibleCards = (firstIndex) => {
            cards.forEach((card, index) => {
                const isVisible = index >= firstIndex && index < firstIndex + visibleCount;
                card.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
                card.inert = !isVisible;
            });
        };

        const render = () => {
            animationFrame = 0;
            if (stage.classList.contains('static-ready')) return;
            if (!travelDistance) {
                track.style.transform = 'translate3d(0, 0, 0)';
                if (progressFill) progressFill.style.transform = 'scaleX(0)';
                if (progressBar) progressBar.setAttribute('aria-valuenow', '0');
                setVisibleCards(0);
                return;
            }

            const stageTop = stage.getBoundingClientRect().top;
            const elapsed = Math.max(0, Math.min(travelDistance, stickyOffset() - stageTop));
            const progress = elapsed / travelDistance;
            const displacement = travelDistance * progress;
            const maxFirstIndex = Math.max(0, cards.length - visibleCount);
            const firstIndex = Math.min(maxFirstIndex, Math.floor((displacement / cardStep) + 0.01));
            currentFirstIndex = firstIndex;

            track.style.transform = `translate3d(${-displacement}px, 0, 0)`;
            syncCounter(firstIndex, progress);
            setVisibleCards(firstIndex);
        };

        const requestRender = () => {
            if (stage.classList.contains('static-ready')) return;
            if (animationFrame) return;
            animationFrame = window.requestAnimationFrame(render);
        };

        const renderStaticScroll = () => {
            staticScrollFrame = 0;
            if (!stage.classList.contains('static-ready')) return;
            const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
            const progress = maxScroll ? Math.max(0, Math.min(1, viewport.scrollLeft / maxScroll)) : 0;
            const firstIndex = Math.min(Math.max(0, cards.length - visibleCount), Math.floor((viewport.scrollLeft / Math.max(1, cardStep)) + 0.01));
            currentFirstIndex = firstIndex;
            syncCounter(firstIndex, progress);
        };

        const requestStaticRender = () => {
            if (staticScrollFrame) return;
            staticScrollFrame = window.requestAnimationFrame(renderStaticScroll);
        };

        const measure = () => {
            const width = viewport.clientWidth;
            const gapValue = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
            visibleCount = window.innerWidth >= 1200 ? 4 : window.innerWidth >= 992 ? 3 : window.innerWidth >= 768 ? 2 : 1;
            const cardWidth = Math.max(220, (width - gapValue * (visibleCount - 1)) / visibleCount);
            const smallScreen = window.innerWidth < 768;

            if (smallScreen) {
                if (stage.classList.contains('horizontal-ready')) viewport.scrollLeft = 0;
                stage.classList.remove('horizontal-ready');
                stage.classList.add('static-ready', 'mobile-carousel');
                stage.style.removeProperty('--services-stage-height');
                track.style.removeProperty('transform');
                cards.forEach((card) => {
                    card.style.removeProperty('width');
                    card.removeAttribute('aria-hidden');
                    card.inert = false;
                });
                visibleCount = 1;

                if (!window.jQuery || typeof window.jQuery.fn.owlCarousel !== 'function') {
                    console.error('Owl Carousel could not be loaded; service cards are using the native scroll fallback.');
                    stage.classList.remove('mobile-carousel');
                    cardStep = (cards[0].getBoundingClientRect().width || cardWidth) + gapValue;
                    travelDistance = Math.max(0, track.scrollWidth - viewport.clientWidth);
                    requestStaticRender();
                    return;
                }

                if (!mobileCarouselActive) {
                    track.classList.add('owl-carousel');
                    window.jQuery(track).owlCarousel({
                        items: 1,
                        margin: 14,
                        stagePadding: 0,
                        nav: true,
                        navText: ['<span aria-hidden="true">‹</span>', '<span aria-hidden="true">›</span>'],
                        dots: true,
                        loop: false,
                        rewind: false,
                        autoplay: !reducedMotion,
                        autoplayTimeout: 5000,
                        autoplayHoverPause: true,
                        autoplaySpeed: reducedMotion ? 0 : 320,
                        smartSpeed: reducedMotion ? 0 : 320,
                        navSpeed: reducedMotion ? 0 : 260,
                        dotsSpeed: reducedMotion ? 0 : 260,
                        mouseDrag: true,
                        touchDrag: true,
                        pullDrag: true,
                        freeDrag: false,
                        responsiveRefreshRate: 120,
                        onInitialized: syncMobileCarousel,
                        onChanged: syncMobileCarousel,
                        onTranslated: syncMobileCarousel
                    });
                    mobileCarouselActive = true;
                } else {
                    window.jQuery(track).trigger('refresh.owl.carousel');
                    window.requestAnimationFrame(syncMobileCarousel);
                }

                cardStep = cards[0].getBoundingClientRect().width + gapValue;
                travelDistance = Math.max(0, track.scrollWidth - viewport.clientWidth);
                requestStaticRender();
                return;
            }

            destroyMobileCarousel();
            stage.classList.remove('mobile-carousel');

            if (reducedMotion) {
                if (stage.classList.contains('horizontal-ready')) viewport.scrollLeft = 0;
                stage.classList.remove('horizontal-ready');
                stage.classList.add('static-ready');
                stage.style.removeProperty('--services-stage-height');
                track.style.removeProperty('transform');
                cards.forEach((card) => {
                    if (smallScreen) card.style.removeProperty('width');
                    else card.style.width = `${cardWidth}px`;
                    card.removeAttribute('aria-hidden');
                    card.inert = false;
                });
                cardStep = (cards[0].getBoundingClientRect().width || cardWidth) + gapValue;
                travelDistance = Math.max(0, track.scrollWidth - viewport.clientWidth);
                requestStaticRender();
                return;
            }

            if (stage.classList.contains('static-ready')) viewport.scrollLeft = 0;
            stage.classList.remove('static-ready');
            stage.classList.add('horizontal-ready');
            stage.style.setProperty('--service-card-width', `${cardWidth}px`);
            cards.forEach((card) => { card.style.width = `${cardWidth}px`; });

            window.requestAnimationFrame(() => {
                if (smallScreen && pin.scrollHeight > pin.clientHeight + 2) {
                    stage.classList.remove('horizontal-ready');
                    stage.classList.add('static-ready');
                    stage.style.removeProperty('--services-stage-height');
                    track.style.removeProperty('transform');
                    cards.forEach((card) => {
                        card.style.width = `${cardWidth}px`;
                        card.removeAttribute('aria-hidden');
                        card.inert = false;
                    });
                    cardStep = cardWidth + gapValue;
                    travelDistance = Math.max(0, track.scrollWidth - viewport.clientWidth);
                    requestStaticRender();
                    return;
                }
                const pinHeight = pin.clientHeight;
                travelDistance = Math.max(0, track.scrollWidth - viewport.clientWidth);
                cardStep = cardWidth + gapValue;
                stage.style.setProperty('--services-stage-height', `${pinHeight + travelDistance}px`);
                requestRender();
            });
        };

        measure();
        viewport.addEventListener('scroll', requestStaticRender, { passive: true });
        if (!reducedMotion) window.addEventListener('scroll', requestRender, { passive: true });
        window.addEventListener('resize', measure, { passive: true });
        window.addEventListener('orientationchange', measure, { passive: true });
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);

        track.addEventListener('keydown', (event) => {
            if (reducedMotion || event.key !== 'Tab' || event.shiftKey) return;
            const card = event.target.closest('.service-card');
            const cardIndex = card ? cards.indexOf(card) : -1;
            const lastVisibleIndex = currentFirstIndex + visibleCount - 1;
            if (cardIndex !== lastVisibleIndex || cardIndex >= cards.length - 1) return;

            const nextCard = cards[cardIndex + 1];
            const nextControl = nextCard.querySelector('a[href], button:not([disabled])');
            if (!nextControl) return;
            if (mobileCarouselActive) {
                event.preventDefault();
                window.jQuery(track).trigger('next.owl.carousel', [reducedMotion ? 0 : 260]);
                let attempts = 0;
                const focusNextWhenActive = () => {
                    if (!nextCard.inert) {
                        nextControl.focus({ preventScroll: true });
                        return;
                    }
                    if (attempts++ < 40) window.requestAnimationFrame(focusNextWhenActive);
                };
                window.requestAnimationFrame(focusNextWhenActive);
                return;
            }
            if (stage.classList.contains('static-ready')) {
                event.preventDefault();
                viewport.scrollBy({ left: cardStep, behavior: reducedMotion ? 'auto' : 'smooth' });
                nextControl.focus({ preventScroll: true });
                return;
            }
            event.preventDefault();
            window.scrollBy({ top: cardStep, behavior: 'smooth' });

            let attempts = 0;
            const focusNextWhenVisible = () => {
                if (!nextCard.inert) {
                    nextControl.focus({ preventScroll: true });
                    return;
                }
                if (attempts++ < 40) window.requestAnimationFrame(focusNextWhenVisible);
            };
            window.requestAnimationFrame(focusNextWhenVisible);
        });
    };

    setupHorizontalServices();

    const contactForm = document.querySelector('[data-contact-form]');
    if (contactForm) {
        const status = contactForm.querySelector('.form-status');
        const fields = Array.from(contactForm.querySelectorAll('input, select, textarea'));

        const errorTextFor = (field) => {
            if (field.validity.valueMissing || (field.required && !field.value.trim())) return 'Please complete this field.';
            if (field.validity.typeMismatch) return 'Enter a valid email address.';
            if (field.validity.tooShort || (field.minLength > 0 && field.value.trim().length < field.minLength)) return `Please enter at least ${field.minLength} characters.`;
            return 'Please check this field.';
        };

        const clearError = (field) => {
            const wrapper = field.closest('.contact-field');
            const error = wrapper ? wrapper.querySelector('.field-error') : null;
            if (error) error.remove();
            field.removeAttribute('aria-invalid');
            const descriptions = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean).filter((id) => id !== `${field.id}-error`);
            if (descriptions.length) field.setAttribute('aria-describedby', descriptions.join(' '));
            else field.removeAttribute('aria-describedby');
        };

        const showError = (field, message) => {
            const wrapper = field.closest('.contact-field');
            if (!wrapper) return;
            let error = wrapper.querySelector('.field-error');
            if (!error) {
                error = document.createElement('span');
                error.className = 'field-error';
                error.id = `${field.id}-error`;
                wrapper.append(error);
            }
            error.textContent = message;
            field.setAttribute('aria-invalid', 'true');
            const descriptions = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
            if (!descriptions.includes(error.id)) descriptions.push(error.id);
            field.setAttribute('aria-describedby', descriptions.join(' '));
        };

        fields.forEach((field) => {
            field.addEventListener('input', () => clearError(field));
            field.addEventListener('change', () => clearError(field));
        });

        contactForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (contactForm.dataset.submitting === 'true') return;

            const invalidFields = fields.filter((field) => !field.checkValidity() || (field.required && !field.value.trim()) || (field.minLength > 0 && field.value.trim().length < field.minLength));
            fields.forEach((field) => clearError(field));
            invalidFields.forEach((field) => showError(field, errorTextFor(field)));

            if (invalidFields.length) {
                if (status) {
                    status.textContent = 'Please check the highlighted details and try again.';
                    status.classList.add('is-error');
                    status.classList.remove('is-success');
                }
                invalidFields[0].focus();
                invalidFields[0].scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
                return;
            }

            contactForm.dataset.submitting = 'true';
            const submitButton = contactForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            try {
                const response = await fetch(contactForm.action, {
                    method: 'POST',
                    body: new FormData(contactForm),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                const succeeded = response.ok && result.ok === true;

                if (status) {
                    status.textContent = result.message || (succeeded ? 'Thank you. Your enquiry has been sent.' : 'We could not send your enquiry. Please try again or email Tony directly.');
                    status.classList.toggle('is-success', succeeded);
                    status.classList.toggle('is-error', !succeeded);
                }
                if (succeeded) contactForm.reset();
            } catch (error) {
                if (status) {
                    status.textContent = 'We could not send your enquiry just now. Please try again or use the email link in this section.';
                    status.classList.add('is-error');
                    status.classList.remove('is-success');
                }
            } finally {
                delete contactForm.dataset.submitting;
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

    const newsletterForm = document.querySelector('[data-newsletter-form]');
    if (newsletterForm) {
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const status = newsletterForm.querySelector('.footer-newsletter-status');

        newsletterForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!emailInput || !emailInput.checkValidity()) {
                if (emailInput) emailInput.reportValidity();
                if (status) {
                    status.textContent = 'Enter a valid email address and try again.';
                    status.classList.add('is-error');
                    status.classList.remove('is-success');
                }
                return;
            }

            if (newsletterForm.dataset.submitting === 'true') return;
            newsletterForm.dataset.submitting = 'true';
            const submitButton = newsletterForm.querySelector('button[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            try {
                const response = await fetch(newsletterForm.action, {
                    method: 'POST',
                    body: new FormData(newsletterForm),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                const succeeded = response.ok && result.ok === true;

                if (status) {
                    status.textContent = result.message || (succeeded ? 'Thank you. Your request for updates has been sent.' : 'We could not send your request. Please try again.');
                    status.classList.toggle('is-success', succeeded);
                    status.classList.toggle('is-error', !succeeded);
                }
                if (succeeded) newsletterForm.reset();
            } catch (error) {
                if (status) {
                    status.textContent = 'We could not send your request just now. Please use the email link in the footer.';
                    status.classList.add('is-error');
                    status.classList.remove('is-success');
                }
            } finally {
                delete newsletterForm.dataset.submitting;
                if (submitButton) submitButton.disabled = false;
            }
        });
    }

});
