/**
 * External dependencies
 */
import { ErrorBoundary } from 'react-error-boundary';
import { toInteger, mapValues } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { compose, pure } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getCardElementQueries, getCardRenderer } from '../../cards';
import CardUnknown from '../empty-states/card-unknown';
import CardCrash from '../empty-states/card-crash';
import { withProps } from '@ithemes/security-hocs';
import './style.scss';

function calculateElementQueryProps( config, style, gridWidth ) {
	const queries = getCardElementQueries( config );

	if ( ! queries ) {
		return {};
	}

	const size = {
		height: style.height ? toInteger( style.height.replace( 'px', '' ) ) : 0,
	};

	if ( style.width && style.width.endsWith( '%' ) ) {
		size.width = ( toInteger( style.width.replace( '%', '' ) ) * gridWidth ) / 100;
	} else {
		size.width = style.width ? toInteger( style.width.replace( 'px', '' ) ) : 0;
	}

	const props = {};

	for ( const query of queries ) {
		if ( ! size[ query.type ] ) {
			continue;
		}

		let pass = false;

		switch ( query.dir ) {
			case 'max':
				pass = size[ query.type ] <= query.px;
				break;
			case 'min':
				pass = size[ query.type ] >= query.px;
				break;
		}

		if ( ! pass ) {
			continue;
		}

		props[ `${ query.dir }-${ query.type }` ] = ( props[ `${ query.dir }-${ query.type }` ] || '' ) + query.px + 'px ';
	}

	return mapValues( props, ( str ) => str.trim() );
}

function Card( props ) {
	const { card, config, dashboardId, className, gridWidth, ...rest } = props;

	if ( card.card === 'unknown' ) {
		return (
			<article className={ classnames( className, 'itsec-card', 'itsec-card--unknown' ) } { ...rest }>
				<CardUnknown card={ card } dashboardId={ dashboardId } />
			</article>
		);
	}

	const CardRender = getCardRenderer( config );

	if ( ! CardRender ) {
		return (
			<article className={ classnames( className, 'itsec-card', 'itsec-card--no-rendered' ) } { ...rest }>
				<CardCrash card={ card } config={ config } />
			</article>
		);
	}

	const eqProps = calculateElementQueryProps( config, rest.style, gridWidth );

	return (
		<article className={ classnames( className, 'itsec-card' ) } id={ `itsec-card-${ card.id }` } { ...rest } { ...eqProps }>
			<ErrorBoundary FallbackComponent={ withProps( { card, config } )( CardCrash ) }>
				{ CardRender && <CardRender card={ card } config={ config } dashboardId={ dashboardId } eqProps={ eqProps } /> }
			</ErrorBoundary>
			{ props.children }
		</article>
	);
}

export default compose( [
	withSelect( ( select, props ) => ( {
		card: select( 'ithemes-security/dashboard' ).getDashboardCard( props.id ),
		config: select( 'ithemes-security/dashboard' ).getDashboardCardConfig( props.id ) || {},
	} ) ),
	pure,
] )( Card );
