(function () {
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n;
  const { createElement, useState, useEffect } = wp.element;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const { PanelBody, Placeholder, ToggleControl, SelectControl, TextControl, Spinner, CheckboxControl } = wp.components;
  const { useSelect } = wp.data;

  // ← HIER ggf. deinen CPT-Slug anpassen:
  const CPT_SLUG = 'hcs_slide';

  function Edit(props) {
    const { attributes, setAttributes } = props;
    const blockProps = useBlockProps();

    // Alle Slides aus dem CPT laden (REST)
    const slidesPosts = useSelect(function (select) {
      return select('core').getEntityRecords('postType', CPT_SLUG, {
        per_page: -1, status: 'publish', _embed: true, orderby: 'title', order: 'asc'
      });
    }, []);
    const isLoading = (slidesPosts === undefined);

    // Hilfsfunktion: ID in Set toggeln
    function toggleSlide(id) {
      const next = new Set(attributes.slides || []);
      if (next.has(id)) next.delete(id); else next.add(id);
      setAttributes({ slides: Array.from(next) });
    }

    function SlidesMultiSelect() {
      if (isLoading) return createElement(Spinner, null);
      if (!slidesPosts || !slidesPosts.length) {
        return createElement('div', { style: { opacity: 0.8 } },
          __('Keine Slides gefunden. Bitte zuerst CPT-Einträge anlegen.', 'hcs'));
      }
      return createElement(
        'div',
        { className: 'hcs-slides-list' },
        slidesPosts.map(function (post) {
          var title = (post.title && post.title.rendered) ? post.title.rendered.replace(/<[^>]+>/g, '') : ('#' + post.id);
          return createElement(CheckboxControl, {
            key: post.id,
            label: title,
            checked: (attributes.slides || []).includes(post.id),
            onChange: function () { toggleSlide(post.id); }
          });
        })
      );
    }

    // Für Placeholder ausgewählte Titel ermitteln
    var selectedTitles = (slidesPosts || [])
      .filter(function (p) { return (attributes.slides || []).includes(p.id); })
      .map(function (p) { return (p.title && p.title.rendered) ? p.title.rendered.replace(/<[^>]+>/g, '') : ('#' + p.id); });

    function Info() {
      return createElement(Placeholder, {
        label: __('Hero / Bild Slider', 'hcs'),
        instructions: (attributes.slides && attributes.slides.length)
          ? __('Wird im Frontend gerendert. Ausgewählt:', 'hcs') + ' ' + (selectedTitles.join(', ') || attributes.slides.join(', '))
          : __('Bitte im rechten Panel mindestens einen Slide auswählen.', 'hcs')
      });
    }

    return createElement(
      'div',
      blockProps,
      createElement(
        InspectorControls,
        null,
        createElement(
          PanelBody,
          { title: __('Slides auswählen', 'hcs'), initialOpen: true },
          createElement(SlidesMultiSelect, null)
        ),
        createElement(
          PanelBody,
          { title: __('Darstellung', 'hcs'), initialOpen: false },
          createElement(SelectControl, {
            label: __('Modus', 'hcs'),
            help: __('„Hero“ mit Overlay/Countdown/CTA, „Bild“ = reiner Bild-Slider', 'hcs'),
            value: attributes.mode,
            options: [
              { label: 'Hero', value: 'hero' },
              { label: 'Bild', value: 'image' }
            ],
            onChange: function (v) { setAttributes({ mode: v }); }
          }),
          createElement(ToggleControl, {
            label: __('Volle Breite (100vw)', 'hcs'),
            help: __('Bricht aus dem Inhalts-Container aus (auch ohne Theme-Unterstützung).', 'hcs'),
            checked: !!attributes.fullWidth,
            onChange: function (v) { setAttributes({ fullWidth: !!v }); }
          }),
          createElement(SelectControl, {
            label: __('Höhenmodus', 'hcs'),
            value: attributes.heightMode,
            options: [
              { label: 'Adaptiv (clamp)', value: 'adaptive' },
              { label: 'Fix (vh/px)', value: 'fixed' }
            ],
            onChange: function (v) { setAttributes({ heightMode: v }); }
          }),
          attributes.heightMode === 'adaptive'
            ? [
              createElement(TextControl, { key: 'min', label: __('Min-Höhe', 'hcs'), value: attributes.minHeight, onChange: function (v) { setAttributes({ minHeight: v }); } }),
              createElement(TextControl, { key: 'vh', label: __('Ziel (vh)', 'hcs'), value: attributes.vhHeight, onChange: function (v) { setAttributes({ vhHeight: v }); } }),
              createElement(TextControl, { key: 'max', label: __('Max-Höhe', 'hcs'), value: attributes.maxHeight, onChange: function (v) { setAttributes({ maxHeight: v }); } })
            ]
            : createElement(TextControl, { label: __('Höhe (z. B. 60vh oder 600px)', 'hcs'), value: attributes.height, onChange: function (v) { setAttributes({ height: v }); } })
        ),
        createElement(
          PanelBody,
          { title: __('Navigation', 'hcs'), initialOpen: false },
          createElement(ToggleControl, { label: 'Pfeile', checked: !!attributes.showArrows, onChange: function (v) { setAttributes({ showArrows: !!v }); } }),
          createElement(ToggleControl, { label: 'Dots', checked: !!attributes.showDots, onChange: function (v) { setAttributes({ showDots: !!v }); } }),
          createElement(ToggleControl, { label: 'Loop', checked: !!attributes.loop, onChange: function (v) { setAttributes({ loop: !!v }); } }),
          createElement(ToggleControl, { label: 'Keyboard', checked: !!attributes.keyboard, onChange: function (v) { setAttributes({ keyboard: !!v }); } }),
          createElement(ToggleControl, { label: 'Swipe', checked: !!attributes.swipe, onChange: function (v) { setAttributes({ swipe: !!v }); } })
        ),
        createElement(
          PanelBody,
          { title: __('Autoplay', 'hcs'), initialOpen: false },
          createElement(ToggleControl, { label: 'Autoplay', checked: !!attributes.autoplay, onChange: function (v) { setAttributes({ autoplay: !!v }); } }),
          createElement(TextControl, {
            label: __('Verzögerung (ms)', 'hcs'),
            value: attributes.autoplayDelay,
            type: 'number',
            onChange: function (v) { setAttributes({ autoplayDelay: parseInt(v, 10) || 5000 }); }
          }),
          createElement(ToggleControl, { label: __('Pause bei Hover', 'hcs'), checked: !!attributes.pauseOnHover, onChange: function (v) { setAttributes({ pauseOnHover: !!v }); } })
        )
      ),
      createElement(Info, null)
    );
  }

  registerBlockType('hcs/hero-slider', {
    edit: Edit,
    save: function () { return null; } // dynamischer Block – Ausgabe via PHP
  });
})();
