(function () {
    'use strict';

    function initSettingsTabs() {
        var wrapper = document.querySelector('.da11y-tabs-wrapper');
        var panelsContainer = document.querySelector('.da11y-tab-panels');

        if (!wrapper || !panelsContainer) {
            return;
        }

        var tabs = Array.prototype.slice.call(wrapper.querySelectorAll('.da11y-tab'));
        var panels = Array.prototype.slice.call(panelsContainer.querySelectorAll('.da11y-tab-panel'));

        if (!tabs.length || !panels.length) {
            return;
        }

        var storageKey = 'da11ySettingsActiveTab';

        function panelFor(tab) {
            return panelsContainer.querySelector('#' + tab.getAttribute('aria-controls'));
        }

        function activate(tab, focusTab) {
            tabs.forEach(function (t) {
                var selected = t === tab;
                t.setAttribute('aria-selected', selected ? 'true' : 'false');
                t.setAttribute('tabindex', selected ? '0' : '-1');
                t.classList.toggle('da11y-tab-active', selected);
            });

            panels.forEach(function (panel) {
                panel.classList.toggle('da11y-tab-panel-active', panel === panelFor(tab));
            });

            if (focusTab) {
                tab.focus();
            }

            try {
                window.localStorage.setItem(storageKey, tab.getAttribute('data-tab'));
            } catch (e) {
                // Ignore storage failures.
            }

            if (history.replaceState) {
                history.replaceState(null, '', '#da11y-tab-' + tab.getAttribute('data-tab'));
            }
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                activate(tab, false);
            });

            tab.addEventListener('keydown', function (event) {
                var newIndex = null;

                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    newIndex = (index + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    newIndex = (index - 1 + tabs.length) % tabs.length;
                } else if (event.key === 'Home') {
                    newIndex = 0;
                } else if (event.key === 'End') {
                    newIndex = tabs.length - 1;
                }

                if (newIndex !== null) {
                    event.preventDefault();
                    activate(tabs[newIndex], true);
                }
            });
        });

        // Enable the tabbed UI now that JS is confirmed to be running.
        wrapper.classList.add('da11y-js-tabs-ready');
        panelsContainer.classList.add('da11y-js-tabs');

        // Determine initial tab: URL hash > stored preference > first tab.
        var initialTab = null;
        var hash = window.location.hash.replace('#da11y-tab-', '');

        if (hash) {
            initialTab = tabs.filter(function (t) {
                return t.getAttribute('data-tab') === hash;
            })[0] || null;
        }

        if (!initialTab) {
            try {
                var stored = window.localStorage.getItem(storageKey);
                if (stored) {
                    initialTab = tabs.filter(function (t) {
                        return t.getAttribute('data-tab') === stored;
                    })[0] || null;
                }
            } catch (e) {
                // Ignore storage failures.
            }
        }

        if (!initialTab) {
            initialTab = tabs[0];
        }

        activate(initialTab, false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSettingsTabs);
    } else {
        initSettingsTabs();
    }
})();
