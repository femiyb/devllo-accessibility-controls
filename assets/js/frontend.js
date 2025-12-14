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
            root.classList.remove('da11y-text-scale-1', 'da11y-text-scale-2');

            if (this.state.textSize === 1) {
                root.classList.add('da11y-text-scale-1');
            } else if (this.state.textSize === 2) {
                root.classList.add('da11y-text-scale-2');
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
                document.addEventListener('keydown', (event) => {
                    this.handleKeydown(event);
                });
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

            // Reading mode toggle.
            if (this.$readingMode) {
                this.$readingMode.addEventListener('click', () => {
                    this.toggleReadingMode();
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
            const min = 0;
            const max = 2;
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

        resetAll() {
            this.state.textSize = 0;
            this.state.contrast = false;
            this.state.dyslexia = false;
            this.state.reducedMotion = false;
             this.state.spacing = 0;
             this.state.theme = 'default';
             this.state.readingMode = false;

            this.applyStateToDOM();
            this.clearPreferences();
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        Da11y.init();
    });
})();
