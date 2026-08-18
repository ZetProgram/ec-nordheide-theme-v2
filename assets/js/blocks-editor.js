/**
 * Editor-Skripte für die EC-Blöcke (ec/hero, ec/card).
 * Bewusst ohne Build-Schritt geschrieben (kein JSX, kein Webpack) -
 * nutzt direkt die von WordPress bereitgestellten wp.* Globals.
 * Die eigentliche Darstellung übernimmt in beiden Fällen PHP
 * (ServerSideRender), damit Editor und Frontend immer gleich aussehen.
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
	var ServerSideRender = wp.serverSideRender ? ( wp.serverSideRender.default || wp.serverSideRender ) : null;

	function setter( setAttributes, key ) {
		return function ( value ) {
			var next = {};
			next[ key ] = value;
			setAttributes( next );
		};
	}

	function previewOrHint( blockName, attributes ) {
		if ( ServerSideRender ) {
			return el( ServerSideRender, { block: blockName, attributes: attributes } );
		}
		return el( 'p', {}, __( 'Vorschau nicht verfügbar.', 'ec-nordheide-v2' ) );
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
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Text', 'ec-nordheide-v2' ), initialOpen: true },
						el( TextControl, { label: __( 'Kicker', 'ec-nordheide-v2' ), value: attributes.kicker, onChange: setter( setAttributes, 'kicker' ) } ),
						el( TextControl, { label: __( 'Titel', 'ec-nordheide-v2' ), value: attributes.title, onChange: setter( setAttributes, 'title' ) } ),
						el( TextControl, { label: __( 'Unterzeile', 'ec-nordheide-v2' ), value: attributes.subtitle, onChange: setter( setAttributes, 'subtitle' ) } )
					),
					el(
						PanelBody,
						{ title: __( 'Buttons', 'ec-nordheide-v2' ), initialOpen: false },
						el( TextControl, { label: __( 'Button 1: Beschriftung', 'ec-nordheide-v2' ), value: attributes.primaryLabel, onChange: setter( setAttributes, 'primaryLabel' ) } ),
						el( TextControl, { label: __( 'Button 1: Link', 'ec-nordheide-v2' ), value: attributes.primaryUrl, onChange: setter( setAttributes, 'primaryUrl' ) } ),
						el( TextControl, { label: __( 'Button 2: Beschriftung', 'ec-nordheide-v2' ), value: attributes.secondaryLabel, onChange: setter( setAttributes, 'secondaryLabel' ) } ),
						el( TextControl, { label: __( 'Button 2: Link', 'ec-nordheide-v2' ), value: attributes.secondaryUrl, onChange: setter( setAttributes, 'secondaryUrl' ) } )
					),
					el(
						PanelBody,
						{ title: __( 'Hintergrundbild', 'ec-nordheide-v2' ), initialOpen: false },
						el( 'p', {}, __( 'Ohne Bild erscheint ein Marken-Platzhalter.', 'ec-nordheide-v2' ) ),
						imageField( __( 'Bild auswählen', 'ec-nordheide-v2' ), attributes, setAttributes )
					)
				),
				previewOrHint( 'ec/hero', attributes )
			);
		},
		save: function () {
			return null;
		},
	} );

	registerBlockType( 'ec/card', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();
			var typeFields = [];

			if ( 'audience' === attributes.cardType ) {
				typeFields.push(
					el( SelectControl, {
						key: 'variant',
						label: __( 'Kartenfarbe', 'ec-nordheide-v2' ),
						value: attributes.variant,
						options: [
							{ label: __( 'Blattgrün', 'ec-nordheide-v2' ), value: 'leaf' },
							{ label: __( 'Hell', 'ec-nordheide-v2' ), value: 'paper' },
							{ label: __( 'Grau', 'ec-nordheide-v2' ), value: 'soft' },
							{ label: __( 'Dunkel', 'ec-nordheide-v2' ), value: 'dark' },
						],
						onChange: setter( setAttributes, 'variant' ),
					} ),
					el( TextControl, { key: 'badge', label: __( 'Nummer (optional)', 'ec-nordheide-v2' ), value: attributes.badge, onChange: setter( setAttributes, 'badge' ) } )
				);
			}

			if ( 'age' === attributes.cardType ) {
				typeFields.push(
					el( TextControl, { key: 'ageRange', label: __( 'Altersspanne', 'ec-nordheide-v2' ), value: attributes.ageRange, onChange: setter( setAttributes, 'ageRange' ) } )
				);
			}

			if ( 'people' === attributes.cardType ) {
				typeFields.push(
					el( 'p', { key: 'photoHint' }, __( 'Ohne Foto erscheint der Anfangsbuchstabe des Titels.', 'ec-nordheide-v2' ) ),
					el( 'div', { key: 'photo' }, imageField( __( 'Foto auswählen', 'ec-nordheide-v2' ), attributes, setAttributes ) )
				);
			}

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Kartentyp', 'ec-nordheide-v2' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Verwendung', 'ec-nordheide-v2' ),
							value: attributes.cardType,
							options: [
								{ label: __( 'Zielgruppe', 'ec-nordheide-v2' ), value: 'audience' },
								{ label: __( 'Altersgruppe', 'ec-nordheide-v2' ), value: 'age' },
								{ label: __( 'Team / Person', 'ec-nordheide-v2' ), value: 'people' },
							],
							onChange: setter( setAttributes, 'cardType' ),
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Inhalt', 'ec-nordheide-v2' ), initialOpen: true },
						el( TextControl, { label: __( 'Titel', 'ec-nordheide-v2' ), value: attributes.title, onChange: setter( setAttributes, 'title' ) } ),
						el( TextareaControl, { label: __( 'Text', 'ec-nordheide-v2' ), value: attributes.text, onChange: setter( setAttributes, 'text' ) } ),
						typeFields,
						el( TextControl, { label: __( 'Link-Beschriftung', 'ec-nordheide-v2' ), value: attributes.linkLabel, onChange: setter( setAttributes, 'linkLabel' ) } ),
						el( TextControl, { label: __( 'Link-Ziel', 'ec-nordheide-v2' ), value: attributes.url, onChange: setter( setAttributes, 'url' ) } )
					)
				),
				previewOrHint( 'ec/card', attributes )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
