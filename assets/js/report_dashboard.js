'use strict';

// App SCSS

require('jquery.transit');
import Chart from 'chart.js';

require('../css/report_dashboard.scss');
require('Hinclude/hinclude');

// wkhtmltopdf 0.12.5 crash fix.
// https://github.com/wkhtmltopdf/wkhtmltopdf/issues/3242#issuecomment-518099192
'use strict';
(function(setLineDash) {
    CanvasRenderingContext2D.prototype.setLineDash = function() {
        if(!arguments[0].length){
            arguments[0] = [1,0];
        }
        // Now, call the original method
        return setLineDash.apply(this, arguments);
    };
})(CanvasRenderingContext2D.prototype.setLineDash);
Function.prototype.bind = Function.prototype.bind || function (thisp) {
    var fn = this;
    return function () {
        return fn.apply(thisp, arguments);
    };
};

$(function() {

    // Sidebar Toggler
    function sidebarToggle(toogle) {
        var sidebar = $('#sidebar');
        var padder = $('.content-padder');
        if( toogle ) {
            $('.notyf').removeAttr( 'style' );
            sidebar.css({'display': 'block', 'x': -300});
            sidebar.transition({opacity: 1, x: 0}, 250, 'in-out', function(){
                sidebar.css('display', 'block');
            });
            if( $( window ).width() > 960 ) {
                padder.transition({marginLeft: sidebar.css('width')}, 250, 'in-out');
            }
        } else {
            $('.notyf').css({width: '90%', margin: '0 auto', display:'block', right: 0, left: 0});
            sidebar.css({'display': 'block', 'x': '0px'});
            sidebar.transition({x: -300, opacity: 0}, 250, 'in-out', function(){
                sidebar.css('display', 'none');
            });
            padder.transition({marginLeft: 0}, 250, 'in-out');
        }
    }

    $('#sidebar_toggle').click(function() {
        var sidebar = $('#sidebar');
        var padder = $('.content-padder');
        if( sidebar.css('x') == '-300px' || sidebar.css('display') == 'none' ) {
            sidebarToggle(true)
        } else {
            sidebarToggle(false)
        }
    });

    function resize()
    {
        var sidebar = $('#sidebar');
        var padder = $('.content-padder');
        padder.removeAttr( 'style' );
        if( $( window ).width() < 960 && sidebar.css('display') == 'block' ) {
            sidebarToggle(false);
        } else if( $( window ).width() > 960 && sidebar.css('display') == 'none' ) {
            sidebarToggle(true);
        }
    }

    if($( window ).width() < 960) {
        sidebarToggle(false);
    }

    $( window ).resize(function() {
        resize()
    });

    $('.content-padder').click(function() {
        if( $( window ).width() < 960 ) {
            sidebarToggle(false);
        }
    });


/*    $('.chartjs-wrapper').each(function() {
        debugger;
        var ctx = $(this).find('.chartjs').get(0).getContext('2d');

        let chartData = $(this).attr('data-chart');
        chartData = JSON.parse(chartData);

        new Chart(ctx, chartData);
    });*/
})

window.onload = function() {

    $('.chartjs-wrapper').each(function() {
        debugger;
        var ctx = $(this).find('.chartjs').get(0).getContext('2d');

        let chartData = $(this).attr('data-chart');
        chartData = JSON.parse(chartData);

        new Chart(ctx, chartData);
    });
};