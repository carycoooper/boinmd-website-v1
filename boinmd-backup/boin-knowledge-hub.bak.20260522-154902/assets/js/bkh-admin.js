/* Boin Knowledge Hub — admin repeater (jQuery) */
( function( $ ) {
    'use strict';
    $( document ).on( 'click', '.bkh-repeater .bkh-add', function( e ) {
        e.preventDefault();
        var $rep = $( this ).closest( '.bkh-repeater' );
        var name = $rep.data( 'name' );
        var $items = $rep.find( '.bkh-rep-items' );
        var $first = $items.children( '.bkh-rep-item' ).first();
        if ( ! $first.length ) return;
        var $clone = $first.clone( true, true );
        // clear values
        $clone.find( 'input[type=text],input[type=url],input[type=number],input[type=date],textarea' ).val( '' );
        $clone.find( 'input[type=checkbox]' ).prop( 'checked', false );
        // re-index name attributes
        var nextIdx = $items.children( '.bkh-rep-item' ).length;
        $clone.find( '[name]' ).each( function() {
            var n = $( this ).attr( 'name' );
            // replaces only the first numeric index after the field key
            $( this ).attr( 'name', n.replace( /\[\d+\]/, '[' + nextIdx + ']' ) );
        } );
        $items.append( $clone );
    } );

    $( document ).on( 'click', '.bkh-repeater .bkh-remove', function( e ) {
        e.preventDefault();
        var $rep = $( this ).closest( '.bkh-repeater' );
        var $items = $rep.find( '.bkh-rep-items' );
        if ( $items.children( '.bkh-rep-item' ).length <= 1 ) {
            // keep one empty
            $( this ).closest( '.bkh-rep-item' ).find( 'input[type=text],input[type=url],input[type=number],input[type=date],textarea' ).val( '' );
            $( this ).closest( '.bkh-rep-item' ).find( 'input[type=checkbox]' ).prop( 'checked', false );
            return;
        }
        $( this ).closest( '.bkh-rep-item' ).remove();
    } );
} )( jQuery );
