( function ( wp ) {
  const { __ } = wp.i18n;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl } = wp.components;
  const { useEffect, useState } = wp.element;
  const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  wp.blocks.registerBlockType('hcs/hero-slider', {
    title: __('Hero Slider', 'hcs'),
    icon: 'images-alt2',
    category: 'widgets',
    attributes: {
      slides: {
        type: 'array',
        default: [],
      },
    },

    edit: (props) => {
      const { attributes: { slides }, setAttributes } = props;
      const blockProps = useBlockProps();

      const [allSlides, setAllSlides] = useState([]);

      // Slides laden
      useEffect(() => {
        wp.apiFetch({ path: '/wp/v2/hcs_slide?per_page=100' })
          .then((res) => setAllSlides(res || []))
          .catch(() => setAllSlides([]));
      }, []);

      const options = allSlides.map((slide) => ({
        label: slide.title?.rendered || `Slide #${slide.id}`,
        value: slide.id,
      }));

      return (
        <div {...blockProps}>
          <InspectorControls>
            <PanelBody title={__('Slides auswählen', 'hcs')}>
              <SelectControl
                multiple
                label={__('Slides', 'hcs')}
                value={slides}
                options={options}
                onChange={(newSlides) =>
                  setAttributes({ slides: newSlides.map((id) => parseInt(id)) })
                }
              />
            </PanelBody>
          </InspectorControls>

          {/* Vorschau nur mit Hintergrundbild */}
          {slides.length > 0 ? (
            <ServerSideRender
              block="hcs/hero-slider"
              attributes={props.attributes}
            />
          ) : (
            <p>{__('Bitte wähle mindestens einen Slide aus.', 'hcs')}</p>
          )}
        </div>
      );
    },

    save: () => null, // Render via PHP
  });
})(window.wp);
