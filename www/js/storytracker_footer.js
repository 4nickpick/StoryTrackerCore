/* Main Navigation Handling */
function subMenuShow(menu_name)
{
	$('.submenu:not('+menu_name+')').fadeOut('fast');
	$(menu_name).fadeIn();
}

function subMenuHide()
{
	$('.submenu').fadeOut('fast');
}

var timeoutToClose;
var timeoutToOpen;

/* Event handlers for navigation menu */

$('.nav ul li:not(.submenu ul li)').mouseenter(function() {
	clearTimeout(timeoutToClose);
	timeoutToOpen = setTimeout( function(li_id) {
		subMenuShow('#submenu_'+li_id);
	}, 125, $(this).attr("id"));
}).
mouseleave(function() {
	clearTimeout(timeoutToOpen);
	timeoutToClose = setTimeout( function() {
		subMenuHide('#submenu_'+$(this).attr("id"));
	}, 450);
});

$('.submenu').mouseenter(function() {
	clearTimeout(timeoutToClose);
});

$('.submenu ul').mouseenter(function() {
	clearTimeout(timeoutToClose);
}).
mouseleave(function() {
	clearTimeout(timeoutToOpen);
	timeoutToClose = setTimeout( function() {
		subMenuHide();
	}, 450);
});

//enableListClick - turn list_table rows into links

if(typeof enableListClick === 'function'){
    enableListClick();
}

//prevent backspace page changes
//toggle between keydown and keypress if code isn't working properly
$(document).on("keydown", function (e) {
    if (e.which === 8 && !$(e.target).is("input, textarea")) {
        e.preventDefault();
        console.log('backspace prevented from changing page.');
    }

    if (e.which === 13 && !$(e.target).is('input[type="text"]')) {
        e.preventDefault();
        console.log('enter prevented from submitting form.');
    }
});

var pathArray = window.location.pathname.split( '/' );
if( pathArray[1] == 'photos' || pathArray[1] == 'sticky-notes' )
    $('.bottom-center-menu ul li#inspiration').toggleClass('active');
if( pathArray[1].length > 0 )
    $('.bottom-center-menu ul li#'+pathArray[1]).toggleClass('active');

//fancybox
$(document).ready(function() {
    $(".fancybox").fancybox({type:'image'});
    loadPictures();
});

//bug report form init

var bugReportButtons = {
    "Submit Report" : function()
    {
        var form = document.getElementById('bug_report_form');

        return handleAjaxForm(form,
            function(resp) {
                AlertSet.addJSON(resp).show();
                $('#bug-report').dialog('destroy');
            },
            function(resp) {
                AlertSet.addJSON(resp).show();
            }
        );
    }
};

var onclose = function() {
    location.reload();
};

loadQuickForm("#bug-report" , bugReportButtons, onclose);
