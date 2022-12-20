/*var lDEopts =
	{
	    'useGrouping': true,
	    'maximumFractionDigits': 2,
	}
var lDEopts2 =
	{
	    'useGrouping': true,
	    'maximumFractionDigits': 2,
	    'style': 'currency',
	    'currency': 'EUR',

	}




function top_fixed_block() {
    var st = $(window).scrollTop()
    var ofTop = $('.tab-content').offset().top
    if (st > ofTop) {
	$('#navPageMenu').css('top', 0);
    } else {
	$('#navPageMenu').css('top', ofTop - st);
    }
}
$(function () {
    $('.navbar-minimalize').on('click', function () {
	if ($('body').hasClass('mini-navbar')) {
	    $('#navPageMenu').css('width', 'calc(100% - 80px)')
	    $('#navPageMenu').css('left', '70px')
	} else {
	    $('#navPageMenu').css('width', 'calc(100% - 240px)')
	    $('#navPageMenu').css('left', '230px')
	}
	top_fixed_block()
    })
})
function default_scroll() {
    var st = $(window).scrollTop()
    var ofTop = $('.tab-content').offset().top
//    if (ofTop < st)
    $(window).scrollTop(ofTop)
}


$(function () {
    moment.locale('de')
    //$(window).on('scroll', top_fixed_block)

    //top_fixed_block()
    //default_scroll()

    //$('.tab-content').css('padding-top', $('#navPageMenu').height() + 5)


    $('.radioBtn a:not([disabled])').on('click', function () {
	var sel = $(this).data('title');
	var tog = $(this).data('toggle');
	$(this).parent().siblings('[name="' + tog + '"]')
		.val(sel)
		.change()
	$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
	$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
    })

})







function save(status) {
    saving.save(status)
}

function save_status(step_id, status) {
    let auftrag_id = $('[name=auftrag_id]').val()
    var data = {

	'step': step_id,
	'status': status
    }

    $.post(`/checker/${auftrag_id}/js/setStepStatus`, data)
	    .then(() => {
		window.location.reload()
	    })
}



$(function () {

    $('.button-checkbox').each(function () {

	// Settings
	var $widget = $(this),
		$button = $widget.find('button'),
		$checkbox = $widget.find('input:checkbox'),
		color = $button.data('color'),
		settings = {
		    on: {
			icon: 'glyphicon glyphicon-check'
		    },
		    off: {
			icon: 'glyphicon glyphicon-unchecked'
		    }
		};

	// Event Handlers
	$button.on('click', function () {
	    $checkbox.prop('checked', !$checkbox.is(':checked'));
	    $checkbox.triggerHandler('change');
	    updateDisplay();
	});
	$checkbox.on('change', function () {
	    updateDisplay();
	});

	// Actions
	function updateDisplay() {
	    var isChecked = $checkbox.is(':checked');

	    // Set the button's state
	    $button.data('state', (isChecked) ? "on" : "off");

	    // Set the button's icon
	    $button.find('.state-icon')
		    .removeClass()
		    .addClass('state-icon ' + settings[$button.data('state')].icon);

	    // Update the button's color
	    if (isChecked) {
		$button
			.removeClass('btn-default')
			.addClass('btn-' + color + ' active');
	    } else {
		$button
			.removeClass('btn-' + color + ' active')
			.addClass('btn-default');
	    }
	}

	// Initialization
	function init() {

	    updateDisplay();

	    // Inject the icon if applicable
	    if ($button.find('.state-icon').length == 0) {
		$button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
	    }
	}

	init();
    });



    positions.draw()

});
*/

