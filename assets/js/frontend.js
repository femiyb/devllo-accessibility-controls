(function () {
    'use strict';

    const Da11y = {
        // Config passed from PHP via wp_localize_script.
        config: window.da11yConfig || {},

        // Preference state.
        state: {
            textSize: 0,
            contrast: false,
            dyslexia: false,
            reducedMotion: false,
            spacing: 0,
            theme: 'default',
            readingMode: false,
            grayscale: false,
            letterSpacing: 0,
            readingGuide: false,
            bigCursor: false,
            highlightLinks: false,
            focusEnhanced: false,
            brightness: 0,
            wordSpacing: 0,
            alignLeft: false,
            readingMask: false,
            hideImages: false,
        },

        init() {
            this.loadConfigDefaults();
            this.loadStoredPreferences();
            this.cacheElements();
            this.applyStateToDOM();
            this.bindEvents();
        },

        loadConfigDefaults() {
            const defaults = this.config.defaults || {};

            if (typeof defaults.textSize === 'number') {
                this.state.textSize = defaults.textSize;
            }

            if (typeof defaults.contrast === 'boolean') {
                this.state.contrast = defaults.contrast;
            }

            if (typeof defaults.dyslexia === 'boolean') {
                this.state.dyslexia = defaults.dyslexia;
            }

            if (typeof defaults.reducedMotion === 'boolean') {
                this.state.reducedMotion = defaults.reducedMotion;
            }

            if (typeof defaults.spacing === 'number') {
                this.state.spacing = defaults.spacing;
            }

            if (typeof defaults.theme === 'string') {
                this.state.theme = defaults.theme;
            }

            if (typeof defaults.readingMode === 'boolean') {
                this.state.readingMode = defaults.readingMode;
            }

            if (typeof defaults.grayscale === 'boolean') {
                this.state.grayscale = defaults.grayscale;
            }

            if (typeof defaults.letterSpacing === 'number') {
                this.state.letterSpacing = defaults.letterSpacing;
            }

            if (typeof defaults.readingGuide === 'boolean') {
                this.state.readingGuide = defaults.readingGuide;
            }

            if (typeof defaults.highlightLinks === 'boolean') {
                this.state.highlightLinks = defaults.highlightLinks;
            }

            if (typeof defaults.brightness === 'number') {
                this.state.brightness = defaults.brightness;
            }

            if (typeof defaults.wordSpacing === 'number') {
                this.state.wordSpacing = defaults.wordSpacing;
            }

            if (typeof defaults.alignLeft === 'boolean') {
                this.state.alignLeft = defaults.alignLeft;
            }

            if (typeof defaults.readingMask === 'boolean') {
                this.state.readingMask = defaults.readingMask;
            }

            if (typeof defaults.hideImages === 'boolean') {
                this.state.hideImages = defaults.hideImages;
            }

            if (typeof defaults.focusEnhanced === 'boolean') {
                this.state.focusEnhanced = defaults.focusEnhanced;
            }

            if (typeof defaults.bigCursor === 'boolean') {
                this.state.bigCursor = defaults.bigCursor;
            }
        },

        loadStoredPreferences() {
            try {
                const raw = window.localStorage.getItem('da11yPrefs');
                if (!raw) {
                    return;
                }

                const stored = JSON.parse(raw);

                if (stored && typeof stored === 'object') {
                    if (typeof stored.textSize === 'number') {
                        this.state.textSize = stored.textSize;
                    }

                    if (typeof stored.contrast === 'boolean') {
                        this.state.contrast = stored.contrast;
                    }

                    if (typeof stored.dyslexia === 'boolean') {
                        this.state.dyslexia = stored.dyslexia;
                    }

                    if (typeof stored.reducedMotion === 'boolean') {
                        this.state.reducedMotion = stored.reducedMotion;
                    }

                    if (typeof stored.spacing === 'number') {
                        this.state.spacing = stored.spacing;
                    }

                    if (typeof stored.theme === 'string') {
                        // Map legacy high-contrast themes back to default.
                        if (
                            stored.theme === 'high-contrast-light' ||
                            stored.theme === 'high-contrast-dark'
                        ) {
                            this.state.theme = 'default';
                        } else {
                            this.state.theme = stored.theme;
                        }
                    }

                    if (typeof stored.readingMode === 'boolean') {
                        this.state.readingMode = stored.readingMode;
                    }

                    if (typeof stored.grayscale === 'boolean') {
                        this.state.grayscale = stored.grayscale;
                    }

                    if (typeof stored.letterSpacing === 'number') {
                        this.state.letterSpacing = stored.letterSpacing;
                    }

                    if (typeof stored.readingGuide === 'boolean') {
                        this.state.readingGuide = stored.readingGuide;
                    }

                    if (typeof stored.bigCursor === 'boolean') {
                        this.state.bigCursor = stored.bigCursor;
                    }

                    if (typeof stored.highlightLinks === 'boolean') {
                        this.state.highlightLinks = stored.highlightLinks;
                    }

                    if (typeof stored.focusEnhanced === 'boolean') {
                        this.state.focusEnhanced = stored.focusEnhanced;
                    }

                    if (typeof stored.brightness === 'number') {
                        this.state.brightness = stored.brightness;
                    }

                    if (typeof stored.wordSpacing === 'number') {
                        this.state.wordSpacing = stored.wordSpacing;
                    }

                    if (typeof stored.alignLeft === 'boolean') {
                        this.state.alignLeft = stored.alignLeft;
                    }

                    if (typeof stored.readingMask === 'boolean') {
                        this.state.readingMask = stored.readingMask;
                    }

                    if (typeof stored.hideImages === 'boolean') {
                        this.state.hideImages = stored.hideImages;
                    }
                                    }
            } catch (e) {
                // Fail silently if storage is unavailable or JSON is invalid.
            }

            // If user has not stored a preference, respect system prefers-reduced-motion.
            if (typeof this.state.reducedMotion !== 'boolean') {
                this.state.reducedMotion = false;
            }

            try {
                const mql = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
                if (mql && mql.matches && !('reducedMotion' in (this.config.defaults || {}))) {
                    // Only use system preference when there is no explicit default from PHP.
                    this.state.reducedMotion = true;
                }
            } catch (e) {
                // Ignore matchMedia failures.
            }
        },

        savePreferences() {
            try {
                const payload = JSON.stringify(this.state);
                window.localStorage.setItem('da11yPrefs', payload);
            } catch (e) {
                // Ignore storage failures.
            }
        },

        clearPreferences() {
            try {
                window.localStorage.removeItem('da11yPrefs');
            } catch (e) {
                // Ignore storage failures.
            }
        },

        cacheElements() {
            // Dialog + trigger.
            this.$trigger      = document.querySelector('.da11y-trigger');
            this.$backdrop     = document.querySelector('.da11y-dialog-backdrop');
            this.$dialog       = document.querySelector('.da11y-dialog');
            this.$closeButton  = document.querySelector('.da11y-dialog-close');

            // Controls.
            this.$textSmaller    = document.querySelector('.da11y-text-smaller');
            this.$textLarger     = document.querySelector('.da11y-text-larger');
            this.$textReset      = document.querySelector('.da11y-text-reset');
            this.$contrastToggle      = document.querySelector('.da11y-contrast-toggle');
            this.$dyslexiaToggle      = document.querySelector('.da11y-dyslexia-toggle');
            this.$reducedMotionToggle = document.querySelector('.da11y-reduced-motion-toggle');
            this.$spacingMore    = document.querySelector('.da11y-spacing-more');
            this.$spacingReset   = document.querySelector('.da11y-spacing-reset');
            this.$themeButtons   = document.querySelectorAll('.da11y-theme-button');
            this.$readingMode    = document.querySelector('.da11y-reading-mode-toggle');
            this.$resetAll       = document.querySelector('.da11y-reset-all');
            this.$grayscaleToggle = document.querySelector('.da11y-grayscale-toggle');
            this.$letterSpacingMore = document.querySelector('.da11y-letter-spacing-more');
            this.$letterSpacingReset = document.querySelector('.da11y-letter-spacing-reset');
            this.$readingGuideToggle = document.querySelector('.da11y-reading-guide-toggle');
            this.$readingGuideLine   = document.querySelector('.da11y-reading-guide-line');
            this.$bigCursorToggle = document.querySelector('.da11y-big-cursor-toggle');
            this.$highlightLinksToggle = document.querySelector('.da11y-highlight-links-toggle');
            this.$focusToggle = document.querySelector('.da11y-focus-toggle');
            this.$brightnessDown  = document.querySelector('.da11y-brightness-down');
            this.$brightnessUp    = document.querySelector('.da11y-brightness-up');
            this.$brightnessReset = document.querySelector('.da11y-brightness-reset');
            this.$wordSpacingMore  = document.querySelector('.da11y-word-spacing-more');
            this.$wordSpacingReset = document.querySelector('.da11y-word-spacing-reset');
            this.$alignLeftToggle = document.querySelector('.da11y-align-left-toggle');
            this.$readingMaskToggle  = document.querySelector('.da11y-reading-mask-toggle');
            this.$readingMaskTop     = document.querySelector('.da11y-reading-mask-top');
            this.$readingMaskBottom  = document.querySelector('.da11y-reading-mask-bottom');
            this.$accordionToggles   = document.querySelectorAll('.da11y-accordion-toggle');
            this.$hideImagesToggle = document.querySelector('.da11y-hide-images-toggle');

            // Selector for focusable elements inside the dialog.
            this.focusableSelector =
                'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

            this.firstFocusable     = null;
            this.lastFocusable      = null;
            this.previouslyFocused  = null;
            this.isOpen             = false;
        },

        applyStateToDOM() {
            // Apply modes on <html>.
            const root = document.documentElement;

            // Reset text size classes.
            root.classList.remove('da11y-text-scale--1', 'da11y-text-scale-1', 'da11y-text-scale-2', 'da11y-text-scale-3');

            if (this.state.textSize === -1) {
                root.classList.add('da11y-text-scale--1');
            } else if (this.state.textSize === 1) {
                root.classList.add('da11y-text-scale-1');
            } else if (this.state.textSize === 2) {
                root.classList.add('da11y-text-scale-2');
            } else if (this.state.textSize === 3) {
                root.classList.add('da11y-text-scale-3');
            }

            // Contrast (only when no explicit theme override is selected).
            if (this.state.theme === 'default' && this.state.contrast) {
                root.classList.add('da11y-contrast-on');
            } else {
                root.classList.remove('da11y-contrast-on');
            }

            // Sync aria-pressed on contrast toggle.
            if (this.$contrastToggle) {
                this.$contrastToggle.setAttribute(
                    'aria-pressed',
                    this.state.contrast ? 'true' : 'false'
                );
                const contrastActive = this.state.theme === 'default' && !!this.state.contrast;
                this.$contrastToggle.classList.toggle('da11y-toggle-active', contrastActive);
            }

            // Dyslexia mode.
            if (this.state.dyslexia) {
                root.classList.add('da11y-dyslexia-on');
            } else {
                root.classList.remove('da11y-dyslexia-on');
            }

            if (this.$dyslexiaToggle) {
                this.$dyslexiaToggle.setAttribute(
                    'aria-pressed',
                    this.state.dyslexia ? 'true' : 'false'
                );
                this.$dyslexiaToggle.classList.toggle('da11y-toggle-active', !!this.state.dyslexia);
            }

            // Reduced motion.
            if (this.state.reducedMotion) {
                root.classList.add('da11y-reduced-motion-on');
            } else {
                root.classList.remove('da11y-reduced-motion-on');
            }

            if (this.$reducedMotionToggle) {
                this.$reducedMotionToggle.setAttribute(
                    'aria-pressed',
                    this.state.reducedMotion ? 'true' : 'false'
                );
                this.$reducedMotionToggle.classList.toggle('da11y-toggle-active', !!this.state.reducedMotion);
            }

            // Spacing.
            root.classList.remove('da11y-spacing-1', 'da11y-spacing-2');
            if (this.state.spacing === 1) {
                root.classList.add('da11y-spacing-1');
            } else if (this.state.spacing === 2) {
                root.classList.add('da11y-spacing-2');
            }

            // Color themes: default or dark mode.
            root.classList.remove(
                'da11y-theme-dark-mode'
            );
            if (this.state.theme === 'dark-mode') {
                root.classList.add('da11y-theme-dark-mode');
            }

            if (this.$themeButtons && this.$themeButtons.length) {
                this.$themeButtons.forEach((button) => {
                    const theme = button.getAttribute('data-da11y-theme');
                    const isActive = theme === this.state.theme;
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    button.classList.toggle('da11y-toggle-active', isActive);
                });
            }

            // Hide images.
            if (this.state.hideImages) {
                root.classList.add('da11y-hide-images-on');
            } else {
                root.classList.remove('da11y-hide-images-on');
            }

            if (this.$hideImagesToggle) {
                this.$hideImagesToggle.setAttribute(
                    'aria-pressed',
                    this.state.hideImages ? 'true' : 'false'
                );
                this.$hideImagesToggle.classList.toggle('da11y-toggle-active', !!this.state.hideImages);
            }

            // Reading mask.
            if (this.state.readingMask) {
                root.classList.add('da11y-reading-mask-on');
            } else {
                root.classList.remove('da11y-reading-mask-on');
            }

            if (this.$readingMaskToggle) {
                this.$readingMaskToggle.setAttribute(
                    'aria-pressed',
                    this.state.readingMask ? 'true' : 'false'
                );
                this.$readingMaskToggle.classList.toggle('da11y-toggle-active', !!this.state.readingMask);
            } 

            // Align left.
            if (this.state.alignLeft) {
                root.classList.add('da11y-align-left-on');
            } else {
                root.classList.remove('da11y-align-left-on');
            }

            if (this.$alignLeftToggle) {
                this.$alignLeftToggle.setAttribute(
                    'aria-pressed',
                    this.state.alignLeft ? 'true' : 'false'
                );
                this.$alignLeftToggle.classList.toggle('da11y-toggle-active', !!this.state.alignLeft);
            }

            // Word spacing.
            root.classList.remove('da11y-word-spacing-1', 'da11y-word-spacing-2');
            if (this.state.wordSpacing === 1) {
                root.classList.add('da11y-word-spacing-1');
            } else if (this.state.wordSpacing === 2) {
                root.classList.add('da11y-word-spacing-2');
            }

            // Letter spacing.
            root.classList.remove('da11y-letter-spacing-1', 'da11y-letter-spacing-2');
            if (this.state.letterSpacing === 1) {
                root.classList.add('da11y-letter-spacing-1');
            } else if (this.state.letterSpacing === 2) {
                root.classList.add('da11y-letter-spacing-2');
            }

            // Reading guide.
            if (this.state.readingGuide) {
                root.classList.add('da11y-reading-guide-on');
            } else {
                root.classList.remove('da11y-reading-guide-on');
            }

            if (this.$readingGuideToggle) {
                this.$readingGuideToggle.setAttribute(
                    'aria-pressed',
                    this.state.readingGuide ? 'true' : 'false'
                );
                this.$readingGuideToggle.classList.toggle('da11y-toggle-active', !!this.state.readingGuide);
            }

            // Brightness.
            root.classList.remove('da11y-brightness-1', 'da11y-brightness-2', 'da11y-brightness-3', 'da11y-brightness-4');
            if (this.state.brightness === -2) {
                root.classList.add('da11y-brightness-2');
            } else if (this.state.brightness === -1) {
                root.classList.add('da11y-brightness-1');
            } else if (this.state.brightness === 1) {
                root.classList.add('da11y-brightness-3');
            } else if (this.state.brightness === 2) {
                root.classList.add('da11y-brightness-4');
            }

            // Focus enhancement.
            if (this.state.focusEnhanced) {
                root.classList.add('da11y-focus-on');
            } else {
                root.classList.remove('da11y-focus-on');
            }

            if (this.$focusToggle) {
                this.$focusToggle.setAttribute(
                    'aria-pressed',
                    this.state.focusEnhanced ? 'true' : 'false'
                );
                this.$focusToggle.classList.toggle('da11y-toggle-active', !!this.state.focusEnhanced);
            }

            // Highlight links.
            if (this.state.highlightLinks) {
                root.classList.add('da11y-highlight-links-on');
            } else {
                root.classList.remove('da11y-highlight-links-on');
            }

            if (this.$highlightLinksToggle) {
                this.$highlightLinksToggle.setAttribute(
                    'aria-pressed',
                    this.state.highlightLinks ? 'true' : 'false'
                );
                this.$highlightLinksToggle.classList.toggle('da11y-toggle-active', !!this.state.highlightLinks);
            }

            // Big cursor.
            if (this.state.bigCursor) {
                root.classList.add('da11y-big-cursor-on');
            } else {
                root.classList.remove('da11y-big-cursor-on');
            }

            if (this.$bigCursorToggle) {
                this.$bigCursorToggle.setAttribute(
                    'aria-pressed',
                    this.state.bigCursor ? 'true' : 'false'
                );
                this.$bigCursorToggle.classList.toggle('da11y-toggle-active', !!this.state.bigCursor);
            }

            // Grayscale.
            if (this.state.grayscale) {
                root.classList.add('da11y-grayscale-on');
            } else {
                root.classList.remove('da11y-grayscale-on');
            }

            if (this.$grayscaleToggle) {
                this.$grayscaleToggle.setAttribute(
                    'aria-pressed',
                    this.state.grayscale ? 'true' : 'false'
                );
                this.$grayscaleToggle.classList.toggle('da11y-toggle-active', !!this.state.grayscale);
            }

            // Reading mode.
            if (this.state.readingMode) {
                root.classList.add('da11y-reading-mode-on');
            } else {
                root.classList.remove('da11y-reading-mode-on');
            }

            if (this.$readingMode) {
                this.$readingMode.setAttribute(
                    'aria-pressed',
                    this.state.readingMode ? 'true' : 'false'
                );
                this.$readingMode.classList.toggle('da11y-toggle-active', !!this.state.readingMode);
            }
        },

        bindEvents() {
            // Dialog open/close.
            if (this.$trigger && this.$dialog && this.$backdrop) {
                // Open dialog on button click.
                this.$trigger.addEventListener('click', () => {
                    this.openDialog();
                });

                // Open dialog on Enter/Space when trigger has focus.
                this.$trigger.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.openDialog();
                    }
                });

                // Global key handling for Escape and Tab trapping when dialog is open.
                if (this.config.features && this.config.features.keyboardShortcut) {
                    document.addEventListener('keydown', (event) => {
                        this.handleKeydown(event);

                        // Alt+A only closes the dialog when open (WCAG 2.1.1 compliance)
                        if (event.altKey && (event.key === 'a' || event.key === 'A' || event.key === 'å' || event.key === 'Å')) {
                            if (this.isOpen) {
                                event.preventDefault();
                                this.closeDialog();
                            }
                        }
                    });
                }
            }

            // Close button inside dialog.
            if (this.$closeButton) {
                this.$closeButton.addEventListener('click', () => {
                    this.closeDialog();
                });
            }

            // Text size controls.
            if (this.$textSmaller) {
                this.$textSmaller.addEventListener('click', () => {
                    this.changeTextSize(-1);
                });
            }

            if (this.$textLarger) {
                this.$textLarger.addEventListener('click', () => {
                    this.changeTextSize(1);
                });
            }

            if (this.$textReset) {
                this.$textReset.addEventListener('click', () => {
                    this.resetTextSize();
                });
            }

            // Contrast toggle.
            if (this.$contrastToggle) {
                this.$contrastToggle.addEventListener('click', () => {
                    this.toggleContrast();
                });
            }

            // Reset all.
            if (this.$resetAll) {
                this.$resetAll.addEventListener('click', () => {
                    this.resetAll();
                });
            }

            // Dyslexia toggle.
            if (this.$dyslexiaToggle) {
                this.$dyslexiaToggle.addEventListener('click', () => {
                    this.toggleDyslexia();
                });
            }

            // Reduced motion toggle.
            if (this.$reducedMotionToggle) {
                this.$reducedMotionToggle.addEventListener('click', () => {
                    this.toggleReducedMotion();
                });
            }

            // Spacing controls.
            if (this.$spacingMore) {
                this.$spacingMore.addEventListener('click', () => {
                    this.changeSpacing(1);
                });
            }

            if (this.$spacingReset) {
                this.$spacingReset.addEventListener('click', () => {
                    this.resetSpacing();
                });
            }

            // Theme buttons.
            if (this.$themeButtons && this.$themeButtons.length) {
                this.$themeButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const theme = button.getAttribute('data-da11y-theme') || 'default';
                        this.setTheme(theme);
                    });
                });
            }

            // Letter spacing controls.
            if (this.$letterSpacingMore) {
                this.$letterSpacingMore.addEventListener('click', () => {
                    this.changeLetterSpacing(1);
                });
            }

            if (this.$letterSpacingReset) {
                this.$letterSpacingReset.addEventListener('click', () => {
                    this.resetLetterSpacing();
                });
            }

            // Reading guide toggle.
            if (this.$readingGuideToggle) {
                this.$readingGuideToggle.addEventListener('click', () => {
                    this.toggleReadingGuide();
                });
            }

            // Hide images toggle.
            if (this.$hideImagesToggle) {
                this.$hideImagesToggle.addEventListener('click', () => {
                    this.toggleHideImages();
                });
            }

            // Reading mask toggle.
            if (this.$readingMaskToggle) {
                this.$readingMaskToggle.addEventListener('click', () => {
                    this.toggleReadingMask();
                });
            }

            // Align left toggle.
            if (this.$alignLeftToggle) {
                this.$alignLeftToggle.addEventListener('click', () => {
                    this.toggleAlignLeft();
                });
            }

            // Word spacing controls.
            if (this.$wordSpacingMore) {
                this.$wordSpacingMore.addEventListener('click', () => {
                    this.changeWordSpacing(1);
                });
            }

            if (this.$wordSpacingReset) {
                this.$wordSpacingReset.addEventListener('click', () => {
                    this.resetWordSpacing();
                });
            }

            // Brightness controls.
            if (this.$brightnessDown) {
                this.$brightnessDown.addEventListener('click', () => {
                    this.changeBrightness(-1);
                });
            }

            if (this.$brightnessUp) {
                this.$brightnessUp.addEventListener('click', () => {
                    this.changeBrightness(1);
                });
            }

            if (this.$brightnessReset) {
                this.$brightnessReset.addEventListener('click', () => {
                    this.resetBrightness();
                });
            }

            // Focus enhancement toggle.
            if (this.$focusToggle) {
                this.$focusToggle.addEventListener('click', () => {
                    this.toggleFocusEnhanced();
                });
            }

            // Highlight links toggle.
            if (this.$highlightLinksToggle) {
                this.$highlightLinksToggle.addEventListener('click', () => {
                    this.toggleHighlightLinks();
                });
            }

            // Big cursor toggle.
            if (this.$bigCursorToggle) {
                this.$bigCursorToggle.addEventListener('click', () => {
                    this.toggleBigCursor();
                });
            }

            // Grayscale toggle.
            if (this.$grayscaleToggle) {
                this.$grayscaleToggle.addEventListener('click', () => {
                    this.toggleGrayscale();
                });
            }

            // Reading mode toggle.
            if (this.$readingMode) {
                this.$readingMode.addEventListener('click', () => {
                    this.toggleReadingMode();
                });
            }

            // Accordion groups (dialog sections).
            if (this.$accordionToggles && this.$accordionToggles.length) {
                this.$accordionToggles.forEach((toggle) => {
                    const panelId = toggle.getAttribute('aria-controls');
                    const panel = panelId ? document.getElementById(panelId) : null;

                    toggle.addEventListener('click', () => {
                        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                        toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');

                        if (panel) {
                            if (isExpanded) {
                                panel.setAttribute('hidden', '');
                                // Return focus to toggle when collapsing
                                toggle.focus();
                            } else {
                                panel.removeAttribute('hidden');
                                // Move focus to first focusable element in panel
                                const focusables = panel.querySelectorAll(this.focusableSelector);
                                if (focusables.length > 0) {
                                    focusables[0].focus();
                                }
                            }
                        }
                    });
                });
            }
        },

        openDialog() {
            if (!this.$backdrop || !this.$dialog || this.isOpen) {
                return;
            }

            this.previouslyFocused = document.activeElement || null;

            // Show dialog.
            this.$backdrop.hidden = false;
            this.isOpen = true;

            if (this.$trigger) {
                this.$trigger.setAttribute('aria-expanded', 'true');
            }

            // Find focusable elements inside the dialog.
            const focusables = this.$dialog.querySelectorAll(this.focusableSelector);

            if (focusables.length > 0) {
                this.firstFocusable = focusables[0];
                this.lastFocusable  = focusables[focusables.length - 1];
                this.firstFocusable.focus();
            } else {
                this.$dialog.focus();
            }
        },

        closeDialog() {
            if (!this.$backdrop || !this.isOpen) {
                return;
            }

            this.$backdrop.hidden = true;
            this.isOpen = false;

            if (this.$trigger) {
                this.$trigger.setAttribute('aria-expanded', 'false');
            }

            // Restore focus to previously focused element (ideally the trigger).
            if (this.previouslyFocused && typeof this.previouslyFocused.focus === 'function') {
                this.previouslyFocused.focus();
            }
        },

        handleKeydown(event) {
            if (!this.isOpen) {
                return;
            }

            // Close on Escape.
            if (event.key === 'Escape' || event.key === 'Esc') {
                event.preventDefault();
                this.closeDialog();
                return;
            }

            // Focus trap with Tab / Shift+Tab.
            if (event.key === 'Tab') {
                if (!this.firstFocusable || !this.lastFocusable) {
                    return;
                }

                if (event.shiftKey) {
                    // Shift+Tab: if on first, loop to last.
                    if (document.activeElement === this.firstFocusable) {
                        event.preventDefault();
                        this.lastFocusable.focus();
                    }
                } else {
                    // Tab: if on last, loop to first.
                    if (document.activeElement === this.lastFocusable) {
                        event.preventDefault();
                        this.firstFocusable.focus();
                    }
                }
            }
        },

        changeTextSize(delta) {
            // Clamp between 0 and 2 for now.
            const min = -1;
            const max = 3;
            let next = this.state.textSize + delta;

            if (next < min) {
                next = min;
            } else if (next > max) {
                next = max;
            }

            this.state.textSize = next;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetTextSize() {
            this.state.textSize = 0;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleContrast() {
            this.state.contrast = !this.state.contrast;

            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleDyslexia() {
            this.state.dyslexia = !this.state.dyslexia;

            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleReducedMotion() {
            this.state.reducedMotion = !this.state.reducedMotion;

            this.applyStateToDOM();
            this.savePreferences();
        },

        changeSpacing(delta) {
            const min = 0;
            const max = 2;
            let next = this.state.spacing + delta;

            if (next < min) {
                next = min;
            } else if (next > max) {
                next = max;
            }

            this.state.spacing = next;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetSpacing() {
            this.state.spacing = 0;
            this.applyStateToDOM();
            this.savePreferences();
        },

        setTheme(theme) {
            this.state.theme = theme || 'default';
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleReadingMode() {
            this.state.readingMode = !this.state.readingMode;
            this.applyStateToDOM();
            this.savePreferences();
        },

        changeLetterSpacing(delta) {
            const min = 0;
            const max = 2;
            let next = this.state.letterSpacing + delta;
            if (next < min) { next = min; }
            if (next > max) { next = max; }
            this.state.letterSpacing = next;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetLetterSpacing() {
            this.state.letterSpacing = 0;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleReadingGuide() {
            this.state.readingGuide = !this.state.readingGuide;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleHideImages() {
            this.state.hideImages = !this.state.hideImages;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleReadingMask() {
            this.state.readingMask = !this.state.readingMask;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleAlignLeft() {
            this.state.alignLeft = !this.state.alignLeft;
            this.applyStateToDOM();
            this.savePreferences();
        },

        changeWordSpacing(delta) {
            const min = 0;
            const max = 2;
            let next = this.state.wordSpacing + delta;
            if (next < min) { next = min; }
            if (next > max) { next = max; }
            this.state.wordSpacing = next;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetWordSpacing() {
            this.state.wordSpacing = 0;
            this.applyStateToDOM();
            this.savePreferences();
        },

        changeBrightness(delta) {
            const min = -2;
            const max = 2;
            let next = this.state.brightness + delta;
            if (next < min) { next = min; }
            if (next > max) { next = max; }
            this.state.brightness = next;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetBrightness() {
            this.state.brightness = 0;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleFocusEnhanced() {
            this.state.focusEnhanced = !this.state.focusEnhanced;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleHighlightLinks() {
            this.state.highlightLinks = !this.state.highlightLinks;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleBigCursor() {
            this.state.bigCursor = !this.state.bigCursor;
            this.applyStateToDOM();
            this.savePreferences();
        },

        toggleGrayscale() {
            this.state.grayscale = !this.state.grayscale;
            this.applyStateToDOM();
            this.savePreferences();
        },

        resetAll() {
            this.state.textSize = 0;
            this.state.contrast = false;
            this.state.dyslexia = false;
            this.state.reducedMotion = false;
            this.state.spacing = 0;
            this.state.theme = 'default';
            this.state.readingMode = false;
            this.state.grayscale = false;
            this.state.letterSpacing = 0;
            this.state.readingGuide = false;
            this.state.bigCursor = false;
            this.state.highlightLinks = false;
            this.state.focusEnhanced = false;
            this.state.brightness = 0;
            this.state.wordSpacing = 0;
            this.state.alignLeft = false;
            this.state.readingMask = false;
            this.state.hideImages = false;

            this.applyStateToDOM();
            this.clearPreferences();
        },
    };

    // Initialize once the DOM is ready so that
    // the trigger button and dialog markup exist.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            Da11y.init();
        });
    } else {
        Da11y.init();
    }

    document.addEventListener('mousemove', function (event) {
        if (Da11y.state.readingGuide && Da11y.$readingGuideLine) {
            Da11y.$readingGuideLine.style.top = (event.clientY) + 'px';
        }

        if (Da11y.state.readingMask && Da11y.$readingMaskTop && Da11y.$readingMaskBottom) {
        var maskHeight = 40;
        Da11y.$readingMaskTop.style.top = '0';
        Da11y.$readingMaskTop.style.height = (event.clientY - maskHeight) + 'px';
        Da11y.$readingMaskBottom.style.top = (event.clientY + maskHeight) + 'px';
        Da11y.$readingMaskBottom.style.height = (window.innerHeight - event.clientY - maskHeight) + 'px';
        }
    });
})();
