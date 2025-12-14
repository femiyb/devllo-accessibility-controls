(function () {
    if (
        typeof wp === 'undefined' ||
        !wp.plugins ||
        !wp.editPost ||
        !wp.element ||
        !wp.components ||
        !wp.data ||
        !wp.i18n
    ) {
        return;
    }

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar } = wp.editPost;
    const { PanelBody, Notice } = wp.components;
    const { createElement: el } = wp.element;
    const { __ } = wp.i18n;
    const { select, useSelect } = wp.data;

    const PANEL_NAME = 'da11y-accessibility-hints';

    const flattenBlocks = (blocks) => {
        const all = [];
        (blocks || []).forEach((block) => {
            all.push(block);
            if (block.innerBlocks && block.innerBlocks.length) {
                all.push(...flattenBlocks(block.innerBlocks));
            }
        });
        return all;
    };

    const getHints = () => {
        const hints = [];
        const editor = select('core/block-editor');
        if (!editor) {
            return hints;
        }

        const blocks = flattenBlocks(editor.getBlocks());

        // Multiple H1s.
        const headings = blocks.filter((block) => block.name === 'core/heading');
        const h1Count = headings.filter((block) => block.attributes.level === 1).length;
        const h2Count = headings.filter((block) => block.attributes.level === 2).length;

        if (h1Count > 1) {
            hints.push({
                id: 'multiple-h1',
                type: 'warning',
                message: __('More than one H1 heading detected. Consider using a single main heading per page.', 'devllo-accessibility-controls'),
            });
        }

        if (h1Count === 0 && headings.length > 0) {
            hints.push({
                id: 'no-h1',
                type: 'info',
                message: __('No H1 heading was detected in this content. Ensure there is a clear main heading on the page (often provided by the theme).', 'devllo-accessibility-controls'),
            });
        }

        const deeperHeadings = headings.filter((block) => block.attributes.level > 2);
        if (deeperHeadings.length > 0 && h2Count === 0) {
            hints.push({
                id: 'heading-structure',
                type: 'info',
                message: __('Heading structure may skip levels (e.g. H1 directly to H3+). Review heading levels for a logical outline.', 'devllo-accessibility-controls'),
            });
        }

        // Image-like blocks with empty alt (core/image, core/cover, core/media-text).
        const imageBlocks = blocks.filter((block) => block.name === 'core/image');
        const coverBlocks = blocks.filter(
            (block) =>
                block.name === 'core/cover' &&
                block.attributes &&
                block.attributes.backgroundType === 'image' &&
                !!block.attributes.url
        );
        const mediaBlocks = blocks.filter(
            (block) =>
                block.name === 'core/media-text' &&
                block.attributes &&
                block.attributes.mediaType === 'image' &&
                !!block.attributes.mediaUrl
        );

        const imagesWithEmptyAlt = imageBlocks.filter(
            (block) => !block.attributes || !block.attributes.alt
        );
        const coversWithEmptyAlt = coverBlocks.filter(
            (block) => !block.attributes || !block.attributes.alt
        );
        const mediaWithEmptyAlt = mediaBlocks.filter(
            (block) => !block.attributes || !block.attributes.mediaAlt
        );

        if (imagesWithEmptyAlt.length > 0 || coversWithEmptyAlt.length > 0 || mediaWithEmptyAlt.length > 0) {
            hints.push({
                id: 'image-alt',
                type: 'warning',
                message: __('Some images have empty or missing alternative text. Ensure informative images have meaningful alt text.', 'devllo-accessibility-controls'),
            });
        }

        // Generic link text heuristic (very simple).
        const problematicPhrases = ['click here', 'read more', 'learn more'];
        const textBlocks = blocks.filter((block) =>
            ['core/paragraph', 'core/button'].includes(block.name)
        );
        const genericLinksFound = textBlocks.some((block) => {
            const content = (block.attributes.content || block.attributes.text || '').toString().toLowerCase();
            return problematicPhrases.some((phrase) => content.includes(phrase));
        });

        if (genericLinksFound) {
            hints.push({
                id: 'generic-links',
                type: 'info',
                message: __('Some link text looks generic (e.g. “click here”). Consider using more descriptive link text.', 'devllo-accessibility-controls'),
            });
        }

        // Overly long paragraphs.
        const longParagraphThreshold = 800; // characters.
        const longParagraphFound = textBlocks.some((block) => {
            const content = (block.attributes.content || block.attributes.text || '').toString();
            return content.replace(/<[^>]+>/g, '').length > longParagraphThreshold;
        });

        if (longParagraphFound) {
            hints.push({
                id: 'long-paragraphs',
                type: 'info',
                message: __('Some paragraphs are very long. Consider splitting long paragraphs to improve readability.', 'devllo-accessibility-controls'),
            });
        }

        // ALL CAPS headings or paragraphs.
        const isAllCaps = (text) => {
            const letters = text.replace(/[^A-Za-z]+/g, '');
            return letters.length > 0 && letters === letters.toUpperCase();
        };

        const capsBlockFound = blocks.some((block) => {
            if (!['core/heading', 'core/paragraph'].includes(block.name)) {
                return false;
            }
            const content = (block.attributes.content || '').toString().replace(/<[^>]+>/g, '');
            return isAllCaps(content);
        });

        if (capsBlockFound) {
            hints.push({
                id: 'all-caps',
                type: 'info',
                message: __('Some text appears in ALL CAPS, which can be harder to read. Consider using normal capitalization.', 'devllo-accessibility-controls'),
            });
        }

        // Tables without apparent headers.
        const tableBlocks = blocks.filter((block) => block.name === 'core/table');
        const tablesWithoutHead = tableBlocks.filter((block) => {
            const attrs = block.attributes || {};
            return !attrs.hasFixedLayout && !attrs.head;
        });

        if (tablesWithoutHead.length > 0) {
            hints.push({
                id: 'tables-no-headers',
                type: 'info',
                message: __('Some tables may not have clear header cells. Ensure data tables have header rows or cells defined.', 'devllo-accessibility-controls'),
            });
        }

        // Video/audio/media hints.
        const videoBlocks = blocks.filter((block) => block.name === 'core/video');
        if (videoBlocks.length > 0) {
            hints.push({
                id: 'video-captions',
                type: 'info',
                message: __('Video content is present. Consider providing captions or transcripts for videos.', 'devllo-accessibility-controls'),
            });
        }

        const audioBlocks = blocks.filter((block) => block.name === 'core/audio');
        if (audioBlocks.length > 0) {
            hints.push({
                id: 'audio-transcripts',
                type: 'info',
                message: __('Audio content is present. Consider providing transcripts for audio-only content.', 'devllo-accessibility-controls'),
            });
        }

        // Raw URL link text (URL used as visible text).
        const rawUrlLinkPattern = /<a[^>]*>(https?:\/\/[^<]+)<\/a>/i;
        const rawUrlLinksFound = textBlocks.some((block) => {
            const content = (block.attributes.content || block.attributes.text || '').toString();
            return rawUrlLinkPattern.test(content);
        });

        if (rawUrlLinksFound) {
            hints.push({
                id: 'raw-url-links',
                type: 'info',
                message: __('Some links use raw URLs as link text. Consider using descriptive link text instead of the bare URL.', 'devllo-accessibility-controls'),
            });
        }

        return hints;
    };

    const AccessibilitySidebar = () => {
        const hints = useSelect(() => getHints(), []);

        const hintCount = hints.length;

        const IconWithBadge = () =>
            el(
                'span',
                { className: 'da11y-editor-icon' },
                el('span', { className: 'dashicons dashicons-universal-access-alt' }),
                hintCount > 0 &&
                    el(
                        'span',
                        { className: 'da11y-editor-badge' },
                        hintCount > 9 ? '9+' : hintCount.toString()
                    )
            );

        return el(
            PluginSidebar,
            {
                name: PANEL_NAME,
                title: __('Accessibility hints', 'devllo-accessibility-controls'),
                icon: IconWithBadge,
            },
            el(
                PanelBody,
                { title: __('Overview', 'devllo-accessibility-controls'), initialOpen: true },
                el(
                    'p',
                    null,
                    __('These hints are partial and do not replace a full accessibility review.', 'devllo-accessibility-controls')
                ),
                el(
                    'ul',
                    null,
                    el(
                        'li',
                        null,
                        __('Use clear, structured headings (H1–H2–H3…).', 'devllo-accessibility-controls')
                    ),
                    el(
                        'li',
                        null,
                        __('Provide meaningful alt text or mark decorative images appropriately.', 'devllo-accessibility-controls')
                    ),
                    el(
                        'li',
                        null,
                        __('Use descriptive link text (avoid “click here”).', 'devllo-accessibility-controls')
                    )
                )
            ),
            el(
                PanelBody,
                { title: __('Post-specific hints', 'devllo-accessibility-controls'), initialOpen: true },
                hints.length === 0
                    ? el(
                          'p',
                          null,
                          __('No specific hints at the moment. This does not mean the content is fully accessible.', 'devllo-accessibility-controls')
                      )
                    : hints.map((hint) =>
                          el(
                              Notice,
                              {
                                  key: hint.id,
                                  status: hint.type === 'warning' ? 'warning' : 'info',
                                  isDismissible: false,
                              },
                              hint.message
                          )
                      )
            )
        );
    };

    registerPlugin(PANEL_NAME, {
        render: AccessibilitySidebar,
        icon: 'universal-access',
    });
})();
