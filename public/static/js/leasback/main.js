$(function() {
    $.datepicker.setDefaults($.datepicker.regional["de"]);
    $("#datepicker").datepicker({
	"dateFormat": "d MM yy",
    });
    $('.lsBtn').each(function(){
	$(this).click(function(){
	    alert($(this).data('info'));

	});
    });
});

var leasback_id;
var get_fahrzeug;
$('.js_fahrzeug').click(function(e) {
    var id = e.target.dataset.id;
    leasback_id = id;
    get_fahrzeug = 'macs';
    
    if(e.target.dataset.db !== undefined) {
        if(e.target.dataset.db == 'junge') {
            get_fahrzeug = 'junge';
        }
    }
    
    $('#fahrzeug').modal('show');
});

var garantieSearchLoader = $('#fahrzeug_search_loader');
$('#fahrzeug_search_input').typeahead({
    autoSelect: true,
    minLength: 0,
    delay: 10,
    source: function (query, process) {
        garantieSearchLoader.show();
        $.ajax({
            url: "/leasback/search-fahrzeug",
            data: {string: query, get_fahrzeug: get_fahrzeug},
            dataType: 'json',
        })
        .done(function (response) {
            garantieSearchLoader.hide();

            var arr = [];
            response.forEach(function (el, t) {
                arr.push(
                    {
                        id: t,
                        name: 'FIN: '+el.FAHRGESTELLNUMMER+', Name: '+el.MODELLTEXT+', Number: '+el.POLKENNZEICHEN+'('+el.POLKENNZEICHEN_TEXT+') Kunde: '+el.KUNDENAME,
			FAHRZEUGID: el.FAHRZEUGID
                    }
                );
            });
	    
            return process(arr);
        });
    }
});
$('#fahrzeug_search_input').change(function () {
    var current = $('#fahrzeug_search_input').typeahead("getActive");
    $('#fahrzeug_search_input_hidden').val(current.FAHRZEUGID);

    $('#fahrzeug_button').prop("disabled", false);
});

var fahzeug_attach = $("#fahzeug_attach");
fahzeug_attach.submit(function(e){
    e.preventDefault();
    var id = $('#fahrzeug_search_input_hidden').val();
    var button = $('#fahrzeug_button');

    $.ajax({
        url: '/leasback/attach-fahrzeug',
        type: 'POST',
        data: { 'leasback_id': leasback_id ,'id': id, get_fahrzeug: get_fahrzeug },
	beforeSend: function() {
	    button.prop("disabled", true);
	    $("#fahzeug_attach").find('input[type="text"]').val('');
	},
        success: function(data) {
            if (data == 'ok') {
                location.reload();
            }
        },
        error: function (data) {
            button.prop("disabled", true);
        }
    });
});

var augtrag_leasback_id;
$('.js_auftrag').click(function(e) {
    var id = e.target.dataset.id;
    augtrag_leasback_id = id;
    
    $.ajax({
        url: '/leasback/get-auftrags',
        type: 'POST',
        data: { 'id': id },
        success: function(msg) {
            $('#auftrag').find('.modal-body').html(msg);
            
            $('#auftrag').modal('show');
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('click', '.js_attach_auftrag', function(e) {
    var auftrag_id = e.target.dataset.auftrag_id;
    
    $.ajax({
        url: '/leasback/attach-auftrag',
        type: 'POST',
        data: { 'leasback_id': augtrag_leasback_id ,'auftrag_id': auftrag_id },
	beforeSend: function() {
	    $(e.target).prop("disabled", true);
	},
        success: function(data) {
            if (data == 'ok') {
                location.reload();
            }
        },
        error: function (data) {
            $(e.target).prop("disabled", true);
        }
    });
});

$(document).on('click', '.js_save', function(e) {
    var leasback_id = e.target.dataset.leasback_id;
    var type = e.target.dataset.type;
    var step = e.target.dataset.step;
    
    $.ajax({
        url: '/leasback/status/save',
        type: 'POST',
        data: { 'leasback_id': leasback_id, 'type': type, 'step': step },
        success: function(msg) {
            if (msg == 'ok') {
                location.reload();
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('click', '.js_einladung_mail', function(e) {
    var leasback_id = e.target.dataset.leasback_id;
    var button = $(e.target);
    
    $.ajax({
        url: '/leasback/step/einladung/mail/js/send',
        type: 'POST',
        data: { 'leasback_id': leasback_id },
        beforeSend: function() {
            button.attr('disabled', 'disabled');
        },
        success: function(msg) {
            if(msg == 'success') {
                button.removeAttr('disabled');
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Mail erfolgreich gesendet',
                    showConfirmButton: false,
                    timer: 2000
                  })
            }
        },
        error: function (data) {
            alertifyError(data);
            button.removeAttr('disabled');
        }
    });
});

$(document).on('click', '.js_direktannahme_tag', function(e) {
    var leasback_id = e.target.dataset.leasback_id;
    var type = e.target.dataset.type;
    var tag = e.target.dataset.tag;
    var button = $(e.target);
    
    $.ajax({
        url: '/leasback/step/direktannahme/'+type+'/js/pick',
        type: 'POST',
        data: { 'leasback_id': leasback_id, 'tag': tag },
        beforeSend: function() {
            $('.damage_block .tag').removeClass('active');
        },
        success: function(msg) {
            $('#'+type+'_view').html(msg);
            button.addClass('active');
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('click', '.js_tag_keine', function(e) {
    var leasback_id = e.target.dataset.leasback_id;
    var type = e.target.dataset.type;
    var tag = e.target.dataset.tag;
    
    $.ajax({
        url: '/leasback/step/direktannahme/'+type+'/js/keine',
        type: 'POST',
        data: { 'leasback_id': leasback_id, 'tag': tag },
        success: function(msg) {
            if(msg == 'ok') {
                location.reload();
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('click', '.js_inspektion', function(e) {
    var leasback_id = e.target.dataset.leasback_id;
    var pick = e.target.dataset.pick;
    var button = $(e.target);
    
    $.ajax({
        url: '/leasback/step/direktannahme/inspektion/js/pick',
        type: 'POST',
        data: { 'leasback_id': leasback_id, 'pick': pick },
        success: function(msg) {
            if(msg.type == 'Wartungfrei') {
                var pick1 = $('#inspektion_select .pick1');
                pick1.removeClass('btn-default');
                pick1.addClass('btn-primary');
       
                $('#inspektion_select .pick2').removeClass('btn-primary');
                $('#inspektion_select .pick2').addClass('btn-default');
                
                $('.kosten_block').empty();
            } else {
                var pick2 = $('#inspektion_select .pick2');
                pick2.removeClass('btn-default');
                pick2.addClass('btn-primary');
                
                $('#inspektion_select .pick1').removeClass('btn-primary');
                $('#inspektion_select .pick1').addClass('btn-default');
                
                $('.kosten_block').html(msg.template);
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('submit', '#direktannahme_inspektion', function(e) {
    e.preventDefault();
    
    var id = e.target.dataset.id;
    var text = $(e.target).find('input[type="text"]').val();
    
    $.ajax({
        url: '/leasback/step/direktannahme/inspektion/js/form',
        type: 'POST',
        data: { 'id': id, 'kosten_text': text }
    });
});