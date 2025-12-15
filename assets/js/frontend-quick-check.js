(function ($) {
    'use strict';

    function renderPanel(checks) {
        var existing = $('#da11y-quick-check-panel');
        if (existing.length) {
            existing.remove();
        }

        var $panel = $('<div>', {
            id: 'da11y-quick-check-panel',
            class: 'da11y-quick-check-panel'
        });

        var $close = $('<button>', {
            type: 'button',
            class: 'da11y-quick-check-close',
            text: '×',
            'aria-label': 'Close quick accessibility checks'
        }).on('click', function () {
            $panel.remove();
        });

        $panel.append($close);
        $panel.append($('<h3>').text('Accessibility quick check'));
        $panel.append(
            $('<p>').text(
                'These checks are partial and informational; they do not replace a full accessibility review.'
            )
        );

        var $list = $('<ul>');

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

            var $li = $('<li>');
            $li.append($('<span>').addClass(statusClass).text('● '));
            $li.append(document.createTextNode(message));
            $list.append($li);
        });

        $panel.append($list);

        $('body').append($panel);
    }

    function runQuickChecks() {
        var checks = {};

        // Skip link: look for link to a main/content anchor near top.
        var hasSkip = false;
        $('a[href^="#"]').each(function (index, el) {
            var href = (el.getAttribute('href') || '').toLowerCase();
            if (href === '#' || href.length < 2) {
                return;
            }
            if (href.indexOf('#main') === 0 || href.indexOf('#content') === 0 || href.indexOf('#primary') === 0 || href.indexOf('#skip') === 0) {
                hasSkip = true;
                return false;
            }
        });

        if (hasSkip) {
            checks.skip_link = {
                status: 'ok',
                message: 'Skip link: a skip link appears to be present on this page.'
            };
        } else {
            checks.skip_link = {
                status: 'warn',
                message: 'Skip link: no skip link was detected on this page; consider adding one for keyboard navigation.'
            };
        }

        // Headings: check structure.
        var headings = $('h1, h2, h3, h4, h5, h6');
        if (!headings.length) {
            checks.headings = {
                status: 'info',
                message: 'Headings: no headings were detected; ensure there is a clear heading structure.'
            };
        } else {
            var levels = [];
            var h1Count = 0;
            headings.each(function (_, el) {
                var tag = el.tagName.toLowerCase();
                var level = parseInt(tag.replace('h', ''), 10);
                if (!isNaN(level)) {
                    levels.push(level);
                    if (level === 1) {
                        h1Count++;
                    }
                }
            });

            var hasJump = false;
            var previous = null;
            levels.forEach(function (level) {
                if (previous !== null && level > previous + 1) {
                    hasJump = true;
                }
                previous = level;
            });

            if (h1Count === 0) {
                checks.headings = {
                    status: 'info',
                    message: 'Headings: no H1 heading was detected; ensure there is a clear main heading.'
                };
            } else if (h1Count > 1) {
                checks.headings = {
                    status: 'warn',
                    message: 'Headings: multiple H1 headings were detected; review heading structure for clarity.'
                };
            } else if (hasJump) {
                checks.headings = {
                    status: 'info',
                    message: 'Headings: heading levels may skip (e.g. H1 to H3); review for a logical outline.'
                };
            } else {
                checks.headings = {
                    status: 'ok',
                    message: 'Headings: no obvious heading structure issues were detected.'
                };
            }
        }

        // Images: alt usage sampling.
        var images = $('img');
        if (!images.length) {
            checks.images = {
                status: 'info',
                message: 'Images: no images were detected on this page.'
            };
        } else {
            var limit = 100;
            var totalSampled = 0;
            var withAlt = 0;
            var decorativeAlt = 0;
            var missingAlt = 0;

            images.each(function (_, el) {
                if (totalSampled >= limit) {
                    return false;
                }
                totalSampled++;

                var alt = el.getAttribute('alt');
                if (alt !== null) {
                    if (alt === '') {
                        decorativeAlt++;
                    } else {
                        withAlt++;
                    }
                } else {
                    missingAlt++;
                }
            });

            checks.images = {
                status: missingAlt > 0 ? 'warn' : 'ok',
                message:
                    'Images: sampled ' +
                    totalSampled +
                    ' images – ' +
                    withAlt +
                    ' with alt text, ' +
                    decorativeAlt +
                    ' decorative (empty alt), ' +
                    missingAlt +
                    ' with missing alt attributes.'
            };
        }

        // Forms: label heuristic.
        var labelForMap = {};
        $('label[for]').each(function (_, el) {
            var id = el.getAttribute('for');
            if (id) {
                labelForMap[id] = true;
            }
        });

        var controls = $('input, select, textarea');
        if (!controls.length) {
            checks.forms = {
                status: 'info',
                message: 'Forms: no form controls were detected on this page.'
            };
        } else {
            var totalControls = 0;
            var likelyLabeled = 0;
            var unlabeled = 0;

            controls.each(function (_, el) {
                if (el.tagName.toLowerCase() === 'input') {
                    var type = (el.getAttribute('type') || '').toLowerCase();
                    if (type === 'hidden') {
                        return;
                    }
                }

                totalControls++;

                var hasAria =
                    el.hasAttribute('aria-label') || el.hasAttribute('aria-labelledby');

                var hasForLabel = false;
                var id = el.getAttribute('id');
                if (id && labelForMap[id]) {
                    hasForLabel = true;
                }

                if (hasAria || hasForLabel) {
                    likelyLabeled++;
                } else {
                    unlabeled++;
                }
            });

            checks.forms = {
                status: unlabeled > 0 ? 'warn' : 'ok',
                message:
                    'Forms: sampled ' +
                    totalControls +
                    ' controls – ' +
                    likelyLabeled +
                    ' appear to have labels or accessible names, ' +
                    unlabeled +
                    ' appear unlabeled.'
            };
        }

        // Links: empty or whitespace-only text.
        var emptyLinks = 0;
        var allLinks = $('a[href]').filter(function () {
            var text = $(this).text().replace(/\s+/g, ' ').trim();
            return text.length === 0;
        });
        emptyLinks = allLinks.length;
        if (emptyLinks > 0) {
            checks.links_empty = {
                status: 'warn',
                message:
                    'Links: ' +
                    emptyLinks +
                    ' link(s) appear to have no visible text; ensure every link has a readable label.'
            };
        }

        // Links: same href, very different texts (heuristic).
        var linkMap = {};
        $('a[href]').each(function (_, el) {
            var href = (el.getAttribute('href') || '').split('#')[0];
            if (!href) {
                return;
            }
            var text = $(el).text().replace(/\s+/g, ' ').trim().toLowerCase();
            if (!linkMap[href]) {
                linkMap[href] = {};
            }
            if (text) {
                linkMap[href][text] = true;
            }
        });
        var inconsistentLinks = 0;
        Object.keys(linkMap).forEach(function (href) {
            var texts = Object.keys(linkMap[href]);
            if (texts.length > 3) {
                inconsistentLinks++;
            }
        });
        if (inconsistentLinks > 0) {
            checks.links_inconsistent = {
                status: 'info',
                message:
                    'Links: some destinations have many different link phrases; consider using consistent, descriptive link text.'
            };
        }

        // Lists used for layout (many empty or very short items).
        var layoutLists = 0;
        $('ul, ol').each(function (_, el) {
            var $items = $(el).children('li');
            if ($items.length === 0) {
                return;
            }
            var shortCount = 0;
            $items.each(function (_, li) {
                var text = $(li).text().replace(/\s+/g, ' ').trim();
                if (text.length === 0 || text.length < 5) {
                    shortCount++;
                }
            });
            if (shortCount / $items.length > 0.7 && $items.length >= 3) {
                layoutLists++;
            }
        });
        if (layoutLists > 0) {
            checks.lists_layout = {
                status: 'info',
                message:
                    'Lists: some lists may be used for layout rather than real lists; ensure lists represent actual lists of items.'
            };
        }

        // Very long lists.
        var longLists = 0;
        $('ul, ol').each(function (_, el) {
            var count = $(el).children('li').length;
            if (count >= 50) {
                longLists++;
            }
        });
        if (longLists > 0) {
            checks.lists_long = {
                status: 'info',
                message:
                    'Lists: very long lists detected; consider breaking them up or adding headings for easier navigation.'
            };
        }

        // Clickable non-semantic elements (role/button or onclick without button/a).
        var nonSemanticClickables = 0;
        $('[role="button"], [onclick]').each(function (_, el) {
            var tag = el.tagName.toLowerCase();
            if (tag !== 'button' && tag !== 'a') {
                nonSemanticClickables++;
            }
        });
        if (nonSemanticClickables > 0) {
            checks.clickables = {
                status: 'info',
                message:
                    'Interactive elements: some clickable elements are not native buttons/links; ensure they are keyboard accessible and properly labeled.'
            };
        }

        // Positive tabindex.
        var positiveTabindex = $('[tabindex]').filter(function () {
            var val = parseInt(this.getAttribute('tabindex'), 10);
            return !isNaN(val) && val > 0;
        }).length;
        if (positiveTabindex > 0) {
            checks.tabindex = {
                status: 'info',
                message:
                    'Tab order: elements with positive tabindex were found; they can create confusing keyboard navigation.'
            };
        }

        // Auto-playing media.
        var autoplayMedia =
            $('video[autoplay], audio[autoplay]').length +
            $('video[muted][loop]').length;
        if (autoplayMedia > 0) {
            checks.autoplay = {
                status: 'info',
                message:
                    'Media: auto-playing media detected; ensure users can pause/stop and that motion is not overwhelming.'
            };
        }

        // Animated GIFs (very simple heuristic).
        var animatedGifs = $('img[src$=".gif"], img[data-src$=".gif"]').length;
        if (animatedGifs > 0) {
            checks.animated = {
                status: 'info',
                message:
                    'Images: animated GIFs detected; ensure motion is not distracting and consider providing controls or alternatives.'
            };
        }

        // Tables: no header cells.
        var tablesNoHeaders = 0;
        $('table').each(function (_, el) {
            var hasTh = $(el).find('th').length > 0;
            if (!hasTh) {
                tablesNoHeaders++;
            }
        });
        if (tablesNoHeaders > 0) {
            checks.tables = {
                status: 'info',
                message:
                    'Tables: some tables do not appear to have header cells; ensure data tables have headers defined.'
            };
        }

        // Nested tables.
        var nestedTables = 0;
        $('table table').each(function () {
            nestedTables++;
        });
        if (nestedTables > 0) {
            checks.tables_nested = {
                status: 'info',
                message:
                    'Tables: nested tables detected; they can be difficult to navigate for assistive technology.'
            };
        }

        // Document language.
        var docLang = (document.documentElement.getAttribute('lang') || '').trim();
        if (!docLang) {
            checks.lang = {
                status: 'info',
                message:
                    'Language: no `lang` attribute detected on the <html> element; set the primary language of the page.'
            };
        }

        // Page title.
        var title = (document.title || '').trim();
        if (!title) {
            checks.title = {
                status: 'info',
                message:
                    'Title: this page appears to have no title; ensure each page has a meaningful title.'
            };
        } else if (title.length < 5) {
            checks.title = {
                status: 'info',
                message:
                    'Title: this page title is very short; consider a more descriptive title.'
            };
        }

        renderPanel(checks);
    }

    $(function () {
        $('#wp-admin-bar-da11y-quick-check a').on('click', function (event) {
            event.preventDefault();
            runQuickChecks();
        });
    });
})(jQuery);
