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
	var useBlockProps = wp.blockEditor.useBlockProps;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
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
} )( window.wp );
