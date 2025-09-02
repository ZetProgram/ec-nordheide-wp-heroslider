(function () {
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n;
  const { createElement } = wp.element;
  const { InspectorControls, useBlockProps } = wp.blockEditor;
  const {
    PanelBody, ToggleControl, SelectControl, TextControl, Spinner, CheckboxControl
  } = wp.components;
  const { useSelect } = wp.data;

  // ServerSideRender (WP < 6.5 fallback über wp.components)
  const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  const CPT_SLUG = 'hcs_slide'; // ggf. anpassen

  function Edit(props) {
    const { attributes, setAttributes } = props;
    const blockProps = useBlockProps();

    // CPT-Slides laden
    const slidesPosts = useSelect(function (select) {
      return select('core').getEntityRecords('postType', CPT_SLUG, {
        per_page: -1, status: 'publish', _embed: true, orderby: 'title', order: 'asc'
      });
    }, []);
    const isLoading = (slidesPosts === undefined);

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

    return createElement(
      'div',
      blockProps,
      // Inspector
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

      // 🔥 Live-Vorschau: fragt deine render.php an und fügt das HTML in den Editor ein
      createElement(ServerSideRender, {
        block: 'hcs/hero-slider',
        attributes: attributes
      })
    );
  }

  registerBlockType('hcs/hero-slider', {
    edit: Edit,
    save: function () { return null; } // dynamischer Block – Ausgabe via PHP
  });
})();
