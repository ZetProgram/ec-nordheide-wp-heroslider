( function ( wp ) {
  const { __ } = wp.i18n;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl, Placeholder } = wp.components;
  const { useEffect, useState } = wp.element;

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
      const blockProps = useBlockProps({ className: 'hcs-editor-block' });

      const [allSlides, setAllSlides] = useState([]);

      // Slides aus REST laden (CPT muss show_in_rest => true haben)
      useEffect(() => {
        wp.apiFetch({ path: '/wp/v2/hcs_slide?per_page=100&orderby=menu_order&order=asc' })
          .then((res) => setAllSlides(res || []))
          .catch(() => setAllSlides([]));
      }, []);

      const options = allSlides.map((slide) => ({
        label: slide.title?.rendered || `Slide #${slide.id}`,
        value: slide.id,
      }));

      const selected = slides
        .map((id) => options.find((o) => o.value === id)?.label)
        .filter(Boolean);

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
                  setAttributes({ slides: (newSlides || []).map((id) => parseInt(id)) })
                }
                help={__('Tipp: Die Reihenfolge kannst du per Menü-Reihenfolge im Slide festlegen.', 'hcs')}
              />
            </PanelBody>
          </InspectorControls>

          {/* Keine Live-Vorschau im Editor */}
          <Placeholder
            icon="images-alt2"
            label={__('Hero Slider', 'hcs')}
            instructions={
              selected.length
                ? __('Wird im Frontend gerendert. Ausgewählt:', 'hcs') + ' ' + selected.join(', ')
                : __('Bitte im rechten Panel mindestens einen Slide auswählen.', 'hcs')
            }
          />
        </div>
      );
    },

    save: () => null, // Render via PHP
  });
})(window.wp);
