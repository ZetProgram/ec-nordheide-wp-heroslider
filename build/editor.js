( function ( wp ) {
  const { __ } = wp.i18n;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl, ToggleControl, TextControl, __experimentalNumberControl: NumberControl } = wp.components;
  const { useEffect, useState } = wp.element;
  const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;

  // --- Neue Mehrfachauswahl für Slides (statt Gruppen) ---
  function SlideSelect( { value, onChange } ) {
    const [slides, setSlides] = useState( [] );

    useEffect( () => {
      // Alle hcs_slide Beiträge holen
      wp.apiFetch( { path: '/wp/v2/hcs_slide?per_page=100&orderby=menu_order&order=asc' } )
        .then( (res) => setSlides( Array.isArray(res) ? res : [] ) )
        .catch( () => setSlides( [] ) );
    }, [] );

    const options = slides.map( s => ( { label: s?.title?.rendered || `Slide #${s.id}`, value: s.id } ) );

    return wp.element.createElement( SelectControl, {
      label: __('Slides auswählen','hcs'),
      multiple: true,
      value: Array.isArray(value) ? value : [],
      options,
      onChange: (vals) => {
        // SelectControl liefert Strings → in Zahlen umwandeln
        const ids = (vals || []).map(v => parseInt(v,10)).filter(Boolean);
        onChange(ids);
      },
      help: __('Wähle eine oder mehrere Slides. Die Reihenfolge im Frontend entspricht der Auswahlreihenfolge.','hcs')
    } );
  }

  function Edit( props ) {
    const { attributes, setAttributes } = props;
    const {
      // NEU: slideIds statt sliderGroup
      slideIds = [],
      autoplay, autoplayDelay, pauseOnHover, showDots, showArrows,
      heightMode, minHeight, vhHeight, maxHeight, height, borderRadius,
      showConfetti, endScreenMessage
    } = attributes;

    const blockProps = useBlockProps();

    return wp.element.createElement(
      'div',
      blockProps,
      [
        wp.element.createElement( InspectorControls, { key: 'inspector' },
          // Panel: Inhalte / Auswahl
          wp.element.createElement( PanelBody, { title: __('Inhalte','hcs'), initialOpen: true },
            wp.element.createElement( SlideSelect, {
              value: slideIds,
              onChange: (ids)=> setAttributes({ slideIds: ids })
            } ),
          ),

          // Panel: Slider-Optionen
          wp.element.createElement( PanelBody, { title: __('Slider','hcs'), initialOpen: true },
            wp.element.createElement( ToggleControl, {
              label: __('Autoplay','hcs'),
              checked: !!autoplay,
              onChange: (v)=>setAttributes({autoplay: !!v})
            } ),
            wp.element.createElement( NumberControl || TextControl, {
              label: __('Autoplay-Verzögerung (ms)','hcs'),
              value: autoplayDelay,
              onChange: (v)=> setAttributes({autoplayDelay: parseInt(v,10) || 0})
            } ),
            wp.element.createElement( ToggleControl, {
              label: __('Autoplay pausieren bei Hover','hcs'),
              checked: !!pauseOnHover,
              onChange: (v)=>setAttributes({pauseOnHover: !!v})
            } ),
            wp.element.createElement( ToggleControl, {
              label: __('Punkte anzeigen','hcs'),
              checked: !!showDots,
              onChange: (v)=>setAttributes({showDots: !!v})
            } ),
            wp.element.createElement( ToggleControl, {
              label: __('Pfeile anzeigen','hcs'),
              checked: !!showArrows,
              onChange: (v)=>setAttributes({showArrows: !!v})
            } ),
            wp.element.createElement( ToggleControl, {
              label: __('Hero Modus (volle Breite)','hcs'),
              checked: !!heroMode,
              onChange: (v)=> setAttributes({heroMode: !!v})
            } )
          ),

          // Panel: Höhe & Layout
          wp.element.createElement( PanelBody, { title: __('Höhe & Layout','hcs'), initialOpen: true },
            wp.element.createElement( SelectControl, {
              label: __('Höhenmodus','hcs'),
              value: heightMode || 'adaptive',
              options: [
                { label: __('Adaptiv (clamp)','hcs'), value: 'adaptive' },
                { label: __('Fix','hcs'), value: 'fixed' },
              ],
              onChange: (v)=> setAttributes({heightMode:v})
            } ),
            heightMode === 'adaptive' ? [
              wp.element.createElement( TextControl, { key:'min', label: __('min-height','hcs'), help: __('z. B. 320px','hcs'), value: minHeight, onChange: (v)=> setAttributes({minHeight:v}) }),
              wp.element.createElement( TextControl, { key:'vh', label: __('Höhe (vh)','hcs'), help: __('z. B. 60vh','hcs'), value: vhHeight, onChange: (v)=> setAttributes({vhHeight:v}) }),
              wp.element.createElement( TextControl, { key:'max', label: __('max-height','hcs'), help: __('z. B. 680px','hcs'), value: maxHeight, onChange: (v)=> setAttributes({maxHeight:v}) }),
            ] : wp.element.createElement( TextControl, { key:'fixed', label: __('Höhe','hcs'), help: __('z. B. 60vh oder 600px','hcs'), value: height, onChange: (v)=> setAttributes({height:v}) } ),
            wp.element.createElement( TextControl, { key:'radius', label: __('Border Radius','hcs'), value: borderRadius, onChange: (v)=> setAttributes({borderRadius:v}) } )
          ),

          // Panel: Countdown & Finale
          wp.element.createElement( PanelBody, { title: __('Countdown & Finale','hcs'), initialOpen: false },
            wp.element.createElement( ToggleControl, {
              label: __('Konfetti bei Ablauf','hcs'),
              checked: !!showConfetti,
              onChange: (v)=> setAttributes({showConfetti: !!v})
            } ),
            wp.element.createElement( TextControl, {
              label: __('Endscreen-Text','hcs'),
              value: endScreenMessage || '',
              onChange: (v)=> setAttributes({endScreenMessage: v})
            } )
          )
        ),

        // Live-Vorschau
        wp.element.createElement( 'div', { key:'preview' },
          wp.element.createElement( ServerSideRender, {
            block: 'we/hero-countdown-slider',
            attributes: { ...attributes, slideIds } // sicherstellen, dass slideIds drin sind
          } )
        )
      ]
    );
  }

  wp.blocks.registerBlockType( 'we/hero-countdown-slider', {
    edit: Edit,
    save: () => null,
  } );

} )( window.wp );
