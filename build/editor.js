( function ( wp ) {
  const { __ } = wp.i18n;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl, ToggleControl, TextControl, Placeholder, __experimentalNumberControl: NumberControl } = wp.components;
  const { useEffect, useState } = wp.element;

  wp.blocks.registerBlockType('hcs/hero-slider', {
    title: __('Hero Slider', 'hcs'),
    icon: 'images-alt2',
    category: 'design',
    attributes: {
      slides: { type: 'array', default: [] },
      mode: { type: 'string', default: 'hero' },
      fullWidth: { type: 'boolean', default: false },
      autoplay: { type: 'boolean', default: true },
      autoplayDelay: { type: 'number', default: 5000 },
      pauseOnHover: { type: 'boolean', default: true },
      showArrows: { type: 'boolean', default: true },
      showDots: { type: 'boolean', default: true },
      loop: { type: 'boolean', default: true },
      heightMode: { type: 'string', default: 'adaptive' },
      vhHeight: { type: 'string', default: '60vh' },
      minHeight: { type: 'string', default: '320px' },
      maxHeight: { type: 'string', default: '720px' },
      height: { type: 'string', default: '60vh' },
      keyboard: { type: 'boolean', default: true },
      swipe: { type: 'boolean', default: true },
    },

    edit: (props) => {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      // Fetch slider terms for selection UI if needed (kept minimal here)
      const [slides, setSlides] = useState(attributes.slides || []);
      useEffect(() => { setSlides(attributes.slides || []); }, [attributes.slides]);

      const Info = () => (
        <Placeholder
          label={__('Hero / Bild Slider', 'hcs')}
          instructions={
            slides.length
              ? __('Wird im Frontend gerendert. Ausgewählt:', 'hcs') + ' ' + slides.join(', ')
              : __('Bitte im rechten Panel mindestens einen Slide auswählen.', 'hcs')
          }
        />
      );

      return (
        wp.element.createElement('div', blockProps,
          wp.element.createElement(InspectorControls, {},
            wp.element.createElement(PanelBody, { title: __('Darstellung', 'hcs'), initialOpen: true },
              wp.element.createElement(SelectControl, {
                label: __('Modus', 'hcs'),
                help: __('„Hero“ mit Overlay/Countdown/CTA, „Bild“ = reiner Bild-Slider', 'hcs'),
                value: attributes.mode,
                options: [
                  { label: 'Hero', value: 'hero' },
                  { label: 'Bild', value: 'image' },
                ],
                onChange: (v)=> setAttributes({ mode: v })
              }),
              wp.element.createElement(ToggleControl, {
                label: __('Volle Breite (100vw)', 'hcs'),
                help: __('Bricht aus dem Inhalts-Container aus (auch ohne Theme-Unterstützung).', 'hcs'),
                checked: !!attributes.fullWidth,
                onChange: (v)=> setAttributes({ fullWidth: !!v })
              }),
              wp.element.createElement(SelectControl, {
                label: __('Höhenmodus', 'hcs'),
                value: attributes.heightMode,
                options: [
                  { label: 'Adaptiv (clamp)', value: 'adaptive' },
                  { label: 'Fix (vh/px)', value: 'fixed' },
                ],
                onChange: (v)=> setAttributes({ heightMode: v })
              }),
              attributes.heightMode === 'adaptive' ? [
                wp.element.createElement(TextControl, { key:'min', label: __('Min-Höhe', 'hcs'), value: attributes.minHeight, onChange: (v)=> setAttributes({ minHeight: v }) }),
                wp.element.createElement(TextControl, { key:'vh', label: __('Ziel (vh)', 'hcs'), value: attributes.vhHeight, onChange: (v)=> setAttributes({ vhHeight: v }) }),
                wp.element.createElement(TextControl, { key:'max', label: __('Max-Höhe', 'hcs'), value: attributes.maxHeight, onChange: (v)=> setAttributes({ maxHeight: v }) }),
              ] : wp.element.createElement(TextControl, { label: __('Höhe (z. B. 60vh oder 600px)', 'hcs'), value: attributes.height, onChange: (v)=> setAttributes({ height: v }) }),
            ),
            wp.element.createElement(PanelBody, { title: __('Navigation', 'hcs'), initialOpen: false },
              wp.element.createElement(ToggleControl, { label: 'Pfeile', checked: !!attributes.showArrows, onChange: (v)=> setAttributes({ showArrows: !!v }) }),
              wp.element.createElement(ToggleControl, { label: 'Dots', checked: !!attributes.showDots, onChange: (v)=> setAttributes({ showDots: !!v }) }),
              wp.element.createElement(ToggleControl, { label: 'Loop', checked: !!attributes.loop, onChange: (v)=> setAttributes({ loop: !!v }) }),
              wp.element.createElement(ToggleControl, { label: 'Keyboard', checked: !!attributes.keyboard, onChange: (v)=> setAttributes({ keyboard: !!v }) }),
              wp.element.createElement(ToggleControl, { label: 'Swipe', checked: !!attributes.swipe, onChange: (v)=> setAttributes({ swipe: !!v }) }),
            ),
            wp.element.createElement(PanelBody, { title: __('Autoplay', 'hcs'), initialOpen: false },
              wp.element.createElement(ToggleControl, { label: 'Autoplay', checked: !!attributes.autoplay, onChange: (v)=> setAttributes({ autoplay: !!v }) }),
              wp.element.createElement(NumberControl || TextControl, { label: __('Verzögerung (ms)', 'hcs'), value: attributes.autoplayDelay, onChange: (v)=> setAttributes({ autoplayDelay: parseInt(v,10)||5000 }) }),
              wp.element.createElement(ToggleControl, { label: __('Pause bei Hover', 'hcs'), checked: !!attributes.pauseOnHover, onChange: (v)=> setAttributes({ pauseOnHover: !!v }) }),
            )
          ),
          wp.element.createElement(Info, null)
        )
      );
    },

    save: () => null, // dynamic render (PHP)
  });
} )( window.wp );
