/**
 * Editor-Skripte für die EC-Blöcke (ec/hero, ec/card).
 * Bewusst ohne Build-Schritt geschrieben (kein JSX, kein Webpack) -
 * nutzt direkt die von WordPress bereitgestellten wp.* Globals.
 *
 * Die Editor-Vorschau baut hier direkt in JS auf, mit denselben CSS-Klassen
 * wie das Frontend (das Theme-Stylesheet ist per add_editor_style() auch im
 * Editor geladen). Bewusst KEIN ServerSideRender: dessen asynchrones
 * Nachladen/Ersetzen des DOM-Knotens hat im Zusammenspiel mit dem
 * Block-Editor zu einem Absturz beim Einfügen geführt ("Cannot read
 * properties of null, reading 'addEventListener'"). Eine rein synchrone,
 * lokale Vorschau umgeht das komplett.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var useSelect = wp.data.useSelect;
	var Button = wp.components.Button;
	var __ = wp.i18n.__;

	function setter( setAttributes, key ) {
		return function ( value ) {
			var next = {};
			next[ key ] = value;
			setAttributes( next );
		};
	}

	function imageField( label, attributes, setAttributes ) {
		return el(
			Fragment,
			{},
			el(
				MediaUploadCheck,
				{},
				el( MediaUpload, {
					onSelect: function ( media ) {
						setAttributes( { imageId: media.id, imageUrl: media.url } );
					},
					allowedTypes: [ 'image' ],
					value: attributes.imageId,
					render: function ( obj ) {
						return el(
							Button,
							{ variant: 'secondary', onClick: obj.open, style: { marginBottom: '8px' } },
							attributes.imageId ? __( 'Bild ändern', 'ec-nordheide-v2' ) : label
						);
					},
				} )
			),
			attributes.imageId
				? el(
						Button,
						{
							variant: 'link',
							isDestructive: true,
							onClick: function () {
								setAttributes( { imageId: 0, imageUrl: '' } );
							},
						},
						__( 'Bild entfernen', 'ec-nordheide-v2' )
				  )
				: null
		);
	}

	function formatNewsDate( dateString ) {
		if ( ! dateString ) {
			return '';
		}
		try {
			return new Date( dateString ).toLocaleDateString();
		} catch ( e ) {
			return dateString;
		}
	}

	function stripHtml( html ) {
		var div = document.createElement( 'div' );
		div.innerHTML = html || '';
		return div.textContent || div.innerText || '';
	}

	function trimWords( text, count ) {
		var words = text.trim().split( /\s+/ ).filter( Boolean );
		if ( words.length <= count ) {
			return words.join( ' ' );
		}
		return words.slice( 0, count ).join( ' ' ) + '…';
	}

	registerBlockType( 'ec/hero', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var hasImage = !! ( a.imageUrl || a.imageId );
			var blockProps = useBlockProps( {
				className: 'ec-hero ' + ( hasImage ? 'ec-hero--photo' : 'ec-hero--placeholder' ),
				style: hasImage ? { '--ec-hero-image': 'url(' + a.imageUrl + ')' } : {},
			} );

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Text', 'ec-nordheide-v2' ), initialOpen: true },
						el( TextControl, { label: __( 'Kicker', 'ec-nordheide-v2' ), value: a.kicker, onChange: setter( setAttributes, 'kicker' ) } ),
						el( TextControl, { label: __( 'Titel', 'ec-nordheide-v2' ), value: a.title, onChange: setter( setAttributes, 'title' ) } ),
						el( TextControl, { label: __( 'Unterzeile', 'ec-nordheide-v2' ), value: a.subtitle, onChange: setter( setAttributes, 'subtitle' ) } )
					),
					el(
						PanelBody,
						{ title: __( 'Buttons', 'ec-nordheide-v2' ), initialOpen: false },
						el( TextControl, { label: __( 'Button 1: Beschriftung', 'ec-nordheide-v2' ), value: a.primaryLabel, onChange: setter( setAttributes, 'primaryLabel' ) } ),
						el( TextControl, { label: __( 'Button 1: Link', 'ec-nordheide-v2' ), value: a.primaryUrl, onChange: setter( setAttributes, 'primaryUrl' ) } ),
						el( TextControl, { label: __( 'Button 2: Beschriftung', 'ec-nordheide-v2' ), value: a.secondaryLabel, onChange: setter( setAttributes, 'secondaryLabel' ) } ),
						el( TextControl, { label: __( 'Button 2: Link', 'ec-nordheide-v2' ), value: a.secondaryUrl, onChange: setter( setAttributes, 'secondaryUrl' ) } )
					),
					el(
						PanelBody,
						{ title: __( 'Hintergrundbild', 'ec-nordheide-v2' ), initialOpen: false },
						el( 'p', {}, __( 'Ohne Bild erscheint ein Marken-Platzhalter.', 'ec-nordheide-v2' ) ),
						imageField( __( 'Bild auswählen', 'ec-nordheide-v2' ), a, setAttributes )
					)
				),
				el(
					'section',
					blockProps,
					el(
						'div',
						{ className: 'ec-container ec-hero__content' },
						a.kicker ? el( 'p', { className: 'ec-kicker' }, a.kicker ) : null,
						el( 'h1', { className: 'ec-hero__title' }, a.title || __( '(Titel eingeben)', 'ec-nordheide-v2' ) ),
						a.subtitle ? el( 'p', { className: 'ec-hero__subtitle' }, a.subtitle ) : null,
						a.primaryLabel || a.secondaryLabel
							? el(
									'div',
									{ className: 'ec-button-row' },
									a.primaryLabel ? el( 'span', { className: 'ec-button' }, a.primaryLabel ) : null,
									a.secondaryLabel ? el( 'span', { className: 'ec-button ec-button--ghost' }, a.secondaryLabel ) : null
							  )
							: null
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	var CARD_TYPE_LABELS = {
		audience: __( 'Zielgruppe', 'ec-nordheide-v2' ),
		age: __( 'Altersgruppe', 'ec-nordheide-v2' ),
		people: __( 'Team / Person', 'ec-nordheide-v2' ),
	};

	function cardPreview( a ) {
		if ( 'people' === a.cardType ) {
			return el(
				'div',
				{ className: 'ec-person-card' },
				a.imageUrl
					? el( 'div', { className: 'ec-person-card__photo' }, el( 'img', { src: a.imageUrl, alt: '' } ) )
					: el(
							'div',
							{ className: 'ec-person-card__photo' },
							el( 'span', { className: 'ec-person-card__initial' }, ( a.title || '?' ).substring( 0, 1 ).toUpperCase() )
					  ),
				el( 'h3', {}, a.title || __( '(Titel)', 'ec-nordheide-v2' ) ),
				a.text ? el( 'p', {}, a.text ) : null,
				a.linkLabel ? el( 'span', { className: 'ec-text-link' }, a.linkLabel + ' →' ) : null
			);
		}
		if ( 'age' === a.cardType ) {
			return el(
				'div',
				{ className: 'ec-age-card' },
				a.ageRange ? el( 'span', { className: 'ec-age-card__age' }, a.ageRange ) : null,
				el( 'h3', {}, a.title || __( '(Titel)', 'ec-nordheide-v2' ) ),
				a.text ? el( 'p', {}, a.text ) : null,
				a.linkLabel ? el( 'span', { className: 'ec-card-link' }, a.linkLabel + ' →' ) : null
			);
		}
		var variant = [ 'leaf', 'paper', 'soft', 'dark' ].indexOf( a.variant ) !== -1 ? a.variant : 'leaf';
		return el(
			'div',
			{ className: 'ec-audience-card ec-card--' + variant },
			a.badge ? el( 'span', { className: 'ec-card-number' }, a.badge ) : null,
			el( 'h3', {}, a.title || __( '(Titel)', 'ec-nordheide-v2' ) ),
			a.text ? el( 'p', {}, a.text ) : null,
			a.linkLabel ? el( 'span', { className: 'ec-card-link' }, a.linkLabel + ' →' ) : null
		);
	}

	registerBlockType( 'ec/card', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'ec-block-card-editor-wrap' } );
			var typeFields = [];

			if ( 'audience' === a.cardType ) {
				typeFields.push(
					el( SelectControl, {
						key: 'variant',
						label: __( 'Kartenfarbe', 'ec-nordheide-v2' ),
						value: a.variant,
						options: [
							{ label: __( 'Blattgrün', 'ec-nordheide-v2' ), value: 'leaf' },
							{ label: __( 'Hell', 'ec-nordheide-v2' ), value: 'paper' },
							{ label: __( 'Grau', 'ec-nordheide-v2' ), value: 'soft' },
							{ label: __( 'Dunkel', 'ec-nordheide-v2' ), value: 'dark' },
						],
						onChange: setter( setAttributes, 'variant' ),
					} ),
					el( TextControl, { key: 'badge', label: __( 'Nummer (optional)', 'ec-nordheide-v2' ), value: a.badge, onChange: setter( setAttributes, 'badge' ) } )
				);
			}

			if ( 'age' === a.cardType ) {
				typeFields.push(
					el( TextControl, { key: 'ageRange', label: __( 'Altersspanne', 'ec-nordheide-v2' ), value: a.ageRange, onChange: setter( setAttributes, 'ageRange' ) } )
				);
			}

			if ( 'people' === a.cardType ) {
				typeFields.push(
					el( 'p', { key: 'photoHint' }, __( 'Ohne Foto erscheint der Anfangsbuchstabe des Titels.', 'ec-nordheide-v2' ) ),
					el( 'div', { key: 'photo' }, imageField( __( 'Foto auswählen', 'ec-nordheide-v2' ), a, setAttributes ) )
				);
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Kartentyp', 'ec-nordheide-v2' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Verwendung', 'ec-nordheide-v2' ),
							value: a.cardType,
							options: [
								{ label: CARD_TYPE_LABELS.audience, value: 'audience' },
								{ label: CARD_TYPE_LABELS.age, value: 'age' },
								{ label: CARD_TYPE_LABELS.people, value: 'people' },
							],
							onChange: setter( setAttributes, 'cardType' ),
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Inhalt', 'ec-nordheide-v2' ), initialOpen: true },
						el( TextControl, { label: __( 'Titel', 'ec-nordheide-v2' ), value: a.title, onChange: setter( setAttributes, 'title' ) } ),
						el( TextareaControl, { label: __( 'Text', 'ec-nordheide-v2' ), value: a.text, onChange: setter( setAttributes, 'text' ) } ),
						typeFields,
						el( TextControl, { label: __( 'Link-Beschriftung', 'ec-nordheide-v2' ), value: a.linkLabel, onChange: setter( setAttributes, 'linkLabel' ) } ),
						el( TextControl, { label: __( 'Link-Ziel', 'ec-nordheide-v2' ), value: a.url, onChange: setter( setAttributes, 'url' ) } )
					)
				),
				el( 'div', blockProps, cardPreview( a ) )
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ec/news', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'ec-newsfeed-grid-editor-wrap' } );

			var categories = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'taxonomy', 'category', { per_page: -1 } );
			}, [] );

			var posts = useSelect(
				function ( select ) {
					var query = {
						per_page: a.postsPerPage || 3,
						status: 'publish',
						_embed: true,
					};
					if ( a.categoryId ) {
						query.categories = a.categoryId;
					}
					return select( 'core' ).getEntityRecords( 'postType', 'post', query );
				},
				[ a.postsPerPage, a.categoryId ]
			);

			var categoryOptions = [ { label: __( 'Alle Kategorien', 'ec-nordheide-v2' ), value: 0 } ];
			if ( categories ) {
				categories.forEach( function ( term ) {
					categoryOptions.push( { label: term.name, value: term.id } );
				} );
			}

			var showCategory = false !== a.showCategory;
			var metaSize = a.metaFontSize || 0.78;
			var titleSize = a.titleFontSize || 1.2;
			var excerptSize = a.excerptFontSize || 0.95;
			var cardMaxWidth = a.cardMaxWidth || 0;
			var metaColor = a.metaColor || '';
			var titleColor = a.titleColor || '';
			var excerptColor = a.excerptColor || '';

			var body;
			if ( null === posts ) {
				body = el( 'p', {}, __( 'Beiträge werden geladen …', 'ec-nordheide-v2' ) );
			} else if ( 0 === posts.length ) {
				body = el( 'p', {}, __( 'Keine Beiträge gefunden.', 'ec-nordheide-v2' ) );
			} else {
				body = el(
					'div',
					{ className: 'ec-newsfeed-grid' },
					posts.map( function ( post ) {
						var media = post._embedded && post._embedded[ 'wp:featuredmedia' ] && post._embedded[ 'wp:featuredmedia' ][ 0 ];
						var imageUrl = media && media.source_url ? media.source_url : '';
						var terms = post._embedded && post._embedded[ 'wp:term' ] ? post._embedded[ 'wp:term' ][ 0 ] : [];
						var catName = terms && terms[ 0 ] ? terms[ 0 ].name : '';
						var excerpt = trimWords( stripHtml( post.content && post.content.rendered ), 22 );
						var metaStyle = { fontSize: metaSize + 'rem' };
						if ( metaColor ) { metaStyle.color = metaColor; }
						var titleStyle = { fontSize: titleSize + 'rem' };
						if ( titleColor ) { titleStyle.color = titleColor; }
						var excerptStyle = { fontSize: excerptSize + 'rem' };
						if ( excerptColor ) { excerptStyle.color = excerptColor; }
						var cardStyle = cardMaxWidth ? { maxWidth: cardMaxWidth + 'px' } : {};

						return el(
							'div',
							{
								className: 'ec-newsfeed-card' + ( imageUrl ? '' : ' ec-newsfeed-card--no-image' ),
								style: cardStyle,
								key: post.id,
							},
							imageUrl
								? el( 'div', { className: 'ec-newsfeed-card__media' }, el( 'img', { src: imageUrl, alt: '' } ) )
								: null,
							el(
								'div',
								{ className: 'ec-newsfeed-card__body' },
								el(
									'div',
									{ className: 'ec-newsfeed-card__meta', style: metaStyle },
									el( 'span', {}, formatNewsDate( post.date ) ),
									showCategory && catName ? el( 'span', {}, catName ) : null
								),
								el( 'h3', { className: 'ec-newsfeed-card__title', style: titleStyle }, stripHtml( post.title && post.title.rendered ) ),
								el( 'p', { className: 'ec-newsfeed-card__excerpt', style: excerptStyle }, excerpt ),
								el( 'span', { className: 'ec-newsfeed-card__readmore' }, __( 'Weiterlesen', 'ec-nordheide-v2' ) + ' →' )
							)
						);
					} )
				);
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Einstellungen', 'ec-nordheide-v2' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Anzahl der Beiträge', 'ec-nordheide-v2' ),
							value: a.postsPerPage,
							onChange: setter( setAttributes, 'postsPerPage' ),
							min: 1,
							max: 9,
						} ),
						el( SelectControl, {
							label: __( 'Kategorie', 'ec-nordheide-v2' ),
							value: a.categoryId,
							options: categoryOptions,
							onChange: function ( value ) {
								setAttributes( { categoryId: parseInt( value, 10 ) || 0 } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Kategorie auf der Karte anzeigen', 'ec-nordheide-v2' ),
							checked: showCategory,
							onChange: setter( setAttributes, 'showCategory' ),
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Textgrößen', 'ec-nordheide-v2' ), initialOpen: false },
						el( RangeControl, {
							label: __( 'Meta-Zeile (Datum/Kategorie)', 'ec-nordheide-v2' ),
							value: metaSize,
							onChange: setter( setAttributes, 'metaFontSize' ),
							min: 0.6,
							max: 1.4,
							step: 0.02,
						} ),
						el( RangeControl, {
							label: __( 'Titel', 'ec-nordheide-v2' ),
							value: titleSize,
							onChange: setter( setAttributes, 'titleFontSize' ),
							min: 0.9,
							max: 2.2,
							step: 0.05,
						} ),
						el( RangeControl, {
							label: __( 'Textanfang', 'ec-nordheide-v2' ),
							value: excerptSize,
							onChange: setter( setAttributes, 'excerptFontSize' ),
							min: 0.7,
							max: 1.6,
							step: 0.05,
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Kartenbreite', 'ec-nordheide-v2' ), initialOpen: false },
						el( RangeControl, {
							label: __( 'Maximale Kartenbreite (0 = kein Limit)', 'ec-nordheide-v2' ),
							value: cardMaxWidth,
							onChange: setter( setAttributes, 'cardMaxWidth' ),
							min: 0,
							max: 600,
							step: 10,
						} )
					),
					el( PanelColorSettings, {
						title: __( 'Textfarben', 'ec-nordheide-v2' ),
						initialOpen: false,
						colorSettings: [
							{
								value: metaColor,
								onChange: setter( setAttributes, 'metaColor' ),
								label: __( 'Meta-Zeile (Datum/Kategorie)', 'ec-nordheide-v2' ),
							},
							{
								value: titleColor,
								onChange: setter( setAttributes, 'titleColor' ),
								label: __( 'Titel', 'ec-nordheide-v2' ),
							},
							{
								value: excerptColor,
								onChange: setter( setAttributes, 'excerptColor' ),
								label: __( 'Textanfang', 'ec-nordheide-v2' ),
							},
						],
					} )
				),
				el( 'div', blockProps, body )
			);
		},
		save: function () {
			return null;
		},
	} );

	/**
	 * "EC Sektion": eine Gruppe, die schon richtig eingestellt ist -
	 * Hintergrund geht über die volle Bildschirmbreite (Breite: "Volle Breite"),
	 * der Inhalt bleibt zentriert in der festen Inhaltsbreite aus theme.json
	 * (Layout: "Eingegrenzt"). Genau das Muster, das auch Hero, Karten-Bereiche
	 * usw. auf der Startseite verwenden - hier als ein-Klick-Baustein, damit
	 * man es beim freien Bauen nicht von Hand in der Seitenleiste einstellen muss.
	 */
	if ( wp.blocks.registerBlockVariation ) {
		wp.blocks.registerBlockVariation( 'core/group', {
			name: 'ec-section',
			title: __( 'EC Sektion (volle Breite, zentrierter Inhalt)', 'ec-nordheide-v2' ),
			description: __(
				'Hintergrund über die volle Fensterbreite, Inhalt darin zentriert mit fester Maximalbreite - wie die Bereiche auf der Startseite. Hintergrundfarbe danach über "Stile" (EC Papier/Dunkel/Akzent) wählen.',
				'ec-nordheide-v2'
			),
			icon: 'align-wide',
			scope: [ 'inserter', 'transform' ],
			attributes: {
				align: 'full',
				layout: { type: 'constrained' },
			},
			innerBlocks: [
				[ 'core/heading', { level: 2, className: 'ec-heading', placeholder: __( 'Überschrift', 'ec-nordheide-v2' ) } ],
				[ 'core/paragraph', { className: 'ec-copy', placeholder: __( 'Text', 'ec-nordheide-v2' ) } ],
			],
		} );
	}
} )( window.wp );
