(function(wp){
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment } = wp.element;
  const { InspectorControls } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl, ToggleControl, TextControl } = wp.components;

  const terms = Array.isArray(window.HCS_TERMS) ? window.HCS_TERMS : [];

  registerBlockType('we/hero-countdown-slider', {
    edit: (props) => {
      const { attributes, setAttributes } = props;

      const groupOptions = [{ label: '— wählen —', value: 0 }]
        .concat(terms.map(t => ({ label: t.name, value: t.id })));

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Slider-Auswahl', initialOpen: true },
            el(SelectControl, {
              label: 'Slider (Gruppe)',
              value: attributes.sliderGroup || 0,
              options: groupOptions,
              onChange: (v) => setAttributes({ sliderGroup: parseInt(v,10) || 0 })
            })
          ),
          el(PanelBody, { title: 'Optionen', initialOpen: true },
            el(ToggleControl, {
              label: 'Autoplay',
              checked: attributes.autoplay,
              onChange: (v)=> setAttributes({ autoplay: v })
            }),
            el(TextControl, {
              type: 'number',
              label: 'Autoplay-Verzögerung (ms)',
              value: attributes.autoplayDelay,
              onChange: (v)=> setAttributes({ autoplayDelay: parseInt(v||'0',10)||0 })
            }),
            el(ToggleControl, {
              label: 'Dots anzeigen',
              checked: attributes.showDots,
              onChange: (v)=> setAttributes({ showDots: v })
            }),
            el(ToggleControl, {
              label: 'Pfeile anzeigen',
              checked: attributes.showArrows,
              onChange: (v)=> setAttributes({ showArrows: v })
            }),
            el(TextControl, {
              label: 'Höhe (z. B. 60vh)',
              value: attributes.height,
              onChange: (v)=> setAttributes({ height: v })
            }),
            el(TextControl, {
              label: 'Eckenradius (z. B. 24px)',
              value: attributes.borderRadius,
              onChange: (v)=> setAttributes({ borderRadius: v })
            })
          )
        ),
        el('div', { className: 'components-placeholder' },
          el('div', { style: { padding: '12px' } },
            el('strong', {}, 'Hero Countdown Slider'),
            el('div', {}, attributes.sliderGroup
              ? 'Slider gewählt: ' + (groupOptions.find(o=>o.value===attributes.sliderGroup)?.label || attributes.sliderGroup)
              : 'Bitte einen Slider auswählen.')
          )
        )
      );
    },
    save: () => null
  });
})(window.wp);
