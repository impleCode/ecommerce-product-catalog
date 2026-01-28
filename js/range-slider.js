/*!
 *  Range slider for size filters
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

function ic_apply_range_slider( field ) {
    if ( typeof field.ionRangeSlider !== "function" ) {
        return;
    }
    var step = 1;
    var unit = field.data( "unit" );
    var min = field.data( "min" );
    var max = field.data( "max" );
    var current_min = field.data( "current-min" );
    var current_max = field.data( "current-max" );
    if ( ( max - min ) < 2 ) {
        step = 0.1;
    }
    if (current_min === current_max) {
        if (current_min === min) {
            current_max = current_max + step;
        } else {
            current_min = current_min - step;
        }

    }
    field.ionRangeSlider( {
        type: "double",
        min: min,
        max: max,
        from: current_min,
        to: current_max,
        step: step,
        postfix: " " + unit,
        grid: true,
        hide_min_max: true,
        onFinish: function () {
            var form = field.closest( "form" );
            form.submit();
            field.closest( ".product_size_filter" ).addClass( "active" );
        }
    } );
}
jQuery( document ).ready( function ( $ ) {
    $( "body" ).on( "reload", ".ic-slider-container", function () {
        var container = $( this );
        container.removeClass( "toReload" );
        container.find( ".ic-range-slider" ).each( function () {
            var field = $( this );
            ic_apply_range_slider( field );
        } );
    } );
    $( ".ic-slider-container.toReload" ).trigger( "reload" );
} );