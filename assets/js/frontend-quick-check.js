(function () {
    'use strict';

    function renderPanel(checks) {
        var existing = document.getElementById('da11y-quick-check-panel');
        if (existing) {
            existing.remove();
        }

        var panel = document.createElement('div');
        panel.id = 'da11y-quick-check-panel';
        panel.className = 'da11y-quick-check-panel';

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'da11y-quick-check-close';
        close.textContent = '×';
        close.setAttribute('aria-label', 'Close quick accessibility checks');
        close.addEventListener('click', function () {
            panel.remove();
        });

        var heading = document.createElement('h3');
        heading.textContent = 'Accessibility quick check';

        var description = document.createElement('p');
        description.textContent = 'These checks are partial and informational; they do not replace a full accessibility review.';

        var list = document.createElement('ul');

        Object.keys(checks).forEach(function (key) {
            var check = checks[key] || {};
            var status = check.status || 'info';
            var message = check.message || '';

            var statusClass =
                status === 'ok'
                    ? 'da11y-check-status-ok'
                    : status === 'warn'
                    ? 'da11y-check-status-warn'
                    : 'da11y-check-status-error';

            var li = document.createElement('li');
            var dot = document.createElement('span');
            dot.className = statusClass;
            dot.textContent = '● ';
            li.appendChild(dot);
            li.appendChild(document.createTextNode(message));
            list.appendChild(li);
        });

        panel.appendChild(close);
        panel.appendChild(heading);
        panel.appendChild(description);
        panel.appendChild(list);

        document.body.appendChild(panel);
    }

    function runQuickChecks() {
        var checks = {};

        // Skip link.
        var hasSkip = false;
        var anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(function (el) {
            var href = (el.getAttribute('href') || '').toLowerCase();
            if (href === '#' || href.length < 2) {
                return;
            }
            if (
                href.indexOf('#main') === 0 ||
                href.indexOf('#content') === 0 ||
                href.indexOf('#primary') === 0 ||
                href.indexOf('#skip') === 0
            ) {
                hasSkip = true;
            }
        });

        checks.skip_link = hasSkip
            ? { status: 'ok', message: 'Skip link: a skip link appears to be present on this page.' }
            : { status: 'warn', message: 'Skip link: no skip link was detected; consider adding one for keyboard navigation.' };

        // Headings.
        var headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        if (!headings.length) {
            checks.headings = { status: 'info', message: 'Headings: no headings were detected; ensure there is a clear heading structure.' };
        } else {
            var levels = [];
            var h1Count = 0;
            headings.forEach(function (el) {
                var level = parseInt(el.tagName.replace(/h/i, ''), 10);
                if (!isNaN(level)) {
                    levels.push(level);
                    if (level === 1) { h1Count++; }
                }
            });

            var hasJump = false;
            var previous = null;
            levels.forEach(function (level) {
                if (previous !== null && level > previous + 1) { hasJump = true; }
                previous = level;
            });

            if (h1Count === 0) {
                checks.headings = { status: 'info', message: 'Headings: no H1 heading was detected; ensure there is a clear main heading.' };
            } else if (h1Count > 1) {
                checks.headings = { status: 'warn', message: 'Headings: multiple H1 headings were detected; review heading structure for clarity.' };
            } else if (hasJump) {
                checks.headings = { status: 'info', message: 'Headings: heading levels may skip (e.g. H1 to H3); review for a logical outline.' };
            } else {
                checks.headings = { status: 'ok', message: 'Headings: no obvious heading structure issues were detected.' };
            }
        }

        // Images.
        var images = document.querySelectorAll('img');
        if (!images.length) {
            checks.images = { status: 'info', message: 'Images: no images were detected on this page.' };
        } else {
            var limit = 100;
            var totalSampled = 0;
            var withAlt = 0;
            var decorativeAlt = 0;
            var missingAlt = 0;

            images.forEach(function (el) {
                if (totalSampled >= limit) { return; }
                totalSampled++;
                var alt = el.getAttribute('alt');
                if (alt !== null) {
                    if (alt === '') { decorativeAlt++; } else { withAlt++; }
                } else {
                    missingAlt++;
                }
            });

            checks.images = {
                status: missingAlt > 0 ? 'warn' : 'ok',
                message: 'Images: sampled ' + totalSampled + ' images – ' + withAlt + ' with alt text, ' + decorativeAlt + ' decorative (empty alt), ' + missingAlt + ' with missing alt attributes.'
            };
        }

        // Forms.
        var labelForMap = {};
        document.querySelectorAll('label[for]').forEach(function (el) {
            var id = el.getAttribute('for');
            if (id) { labelForMap[id] = true; }
        });

        var controls = document.querySelectorAll('input, select, textarea');
        if (!controls.length) {
            checks.forms = { status: 'info', message: 'Forms: no form controls were detected on this page.' };
        } else {
            var totalControls = 0;
            var likelyLabeled = 0;
            var unlabeled = 0;

            controls.forEach(function (el) {
                if (el.tagName.toLowerCase() === 'input') {
                    var type = (el.getAttribute('type') || '').toLowerCase();
                    if (type === 'hidden') { return; }
                }
                totalControls++;
                var hasAria = el.hasAttribute('aria-label') || el.hasAttribute('aria-labelledby');
                var id = el.getAttribute('id');
                var hasForLabel = id && labelForMap[id];
                if (hasAria || hasForLabel) { likelyLabeled++; } else { unlabeled++; }
            });

            checks.forms = {
                status: unlabeled > 0 ? 'warn' : 'ok',
                message: 'Forms: sampled ' + totalControls + ' controls – ' + likelyLabeled + ' appear labeled, ' + unlabeled + ' appear unlabeled.'
            };
        }

        // Empty links.
        var emptyLinks = 0;
        document.querySelectorAll('a[href]').forEach(function (el) {
            var text = el.textContent.replace(/\s+/g, ' ').trim();
            if (text.length === 0) { emptyLinks++; }
        });
        if (emptyLinks > 0) {
            checks.links_empty = {
                status: 'warn',
                message: 'Links: ' + emptyLinks + ' link(s) appear to have no visible text; ensure every link has a readable label.'
            };
        }

        // Inconsistent link text.
        var linkMap = {};
        document.querySelectorAll('a[href]').forEach(function (el) {
            var href = (el.getAttribute('href') || '').split('#')[0];
            if (!href) { return; }
            var text = el.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
            if (!linkMap[href]) { linkMap[href] = {}; }
            if (text) { linkMap[href][text] = true; }
        });
        var inconsistentLinks = 0;
        Object.keys(linkMap).forEach(function (href) {
            if (Object.keys(linkMap[href]).length > 3) { inconsistentLinks++; }
        });
        if (inconsistentLinks > 0) {
            checks.links_inconsistent = {
                status: 'info',
                message: 'Links: some destinations have many different link phrases; consider using consistent, descriptive link text.'
            };
        }

        // Layout lists.
        var layoutLists = 0;
        document.querySelectorAll('ul, ol').forEach(function (el) {
            var items = el.querySelectorAll(':scope > li');
            if (!items.length) { return; }
            var shortCount = 0;
            items.forEach(function (li) {
                var text = li.textContent.replace(/\s+/g, ' ').trim();
                if (text.length === 0 || text.length < 5) { shortCount++; }
            });
            if (shortCount / items.length > 0.7 && items.length >= 3) { layoutLists++; }
        });
        if (layoutLists > 0) {
            checks.lists_layout = {
                status: 'info',
                message: 'Lists: some lists may be used for layout rather than real list content.'
            };
        }

        // Long lists.
        var longLists = 0;
        document.querySelectorAll('ul, ol').forEach(function (el) {
            if (el.querySelectorAll(':scope > li').length >= 50) { longLists++; }
        });
        if (longLists > 0) {
            checks.lists_long = {
                status: 'info',
                message: 'Lists: very long lists detected; consider breaking them up or adding headings for easier navigation.'
            };
        }

        // Non-semantic clickables.
        var nonSemanticClickables = 0;
        document.querySelectorAll('[role="button"], [onclick]').forEach(function (el) {
            var tag = el.tagName.toLowerCase();
            if (tag !== 'button' && tag !== 'a') { nonSemanticClickables++; }
        });
        if (nonSemanticClickables > 0) {
            checks.clickables = {
                status: 'info',
                message: 'Interactive elements: some clickable elements are not native buttons or links; ensure they are keyboard accessible.'
            };
        }

        // Positive tabindex.
        var positiveTabindex = 0;
        document.querySelectorAll('[tabindex]').forEach(function (el) {
            var val = parseInt(el.getAttribute('tabindex'), 10);
            if (!isNaN(val) && val > 0) { positiveTabindex++; }
        });
        if (positiveTabindex > 0) {
            checks.tabindex = {
                status: 'info',
                message: 'Tab order: elements with positive tabindex were found; they can create confusing keyboard navigation.'
            };
        }

        // Autoplay media.
        var autoplayMedia =
            document.querySelectorAll('video[autoplay], audio[autoplay]').length +
            document.querySelectorAll('video[muted][loop]').length;
        if (autoplayMedia > 0) {
            checks.autoplay = {
                status: 'info',
                message: 'Media: auto-playing media detected; ensure users can pause or stop it.'
            };
        }

        // Animated GIFs.
        var animatedGifs = document.querySelectorAll('img[src$=".gif"], img[data-src$=".gif"]').length;
        if (animatedGifs > 0) {
            checks.animated = {
                status: 'info',
                message: 'Images: animated GIFs detected; ensure motion is not distracting.'
            };
        }

        // Tables without headers.
        var tablesNoHeaders = 0;
        document.querySelectorAll('table').forEach(function (el) {
            if (!el.querySelector('th')) { tablesNoHeaders++; }
        });
        if (tablesNoHeaders > 0) {
            checks.tables = {
                status: 'info',
                message: 'Tables: some tables do not appear to have header cells; ensure data tables have headers defined.'
            };
        }

        // Nested tables.
        var nestedTables = document.querySelectorAll('table table').length;
        if (nestedTables > 0) {
            checks.tables_nested = {
                status: 'info',
                message: 'Tables: nested tables detected; they can be difficult to navigate with assistive technology.'
            };
        }

        // Document language.
        var docLang = (document.documentElement.getAttribute('lang') || '').trim();
        if (!docLang) {
            checks.lang = {
                status: 'info',
                message: 'Language: no lang attribute detected on the <html> element; set the primary language of the page.'
            };
        }

        // Page title.
        var title = (document.title || '').trim();
        if (!title) {
            checks.title = { status: 'info', message: 'Title: this page appears to have no title; ensure each page has a meaningful title.' };
        } else if (title.length < 5) {
            checks.title = { status: 'info', message: 'Title: this page title is very short; consider a more descriptive title.' };
        }

        renderPanel(checks);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var trigger = document.querySelector('#wp-admin-bar-da11y-quick-check a');
        if (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                runQuickChecks();
            });
        }
    });

})();