/* 
 * | ---------------
 * | Technische FRZG-Abnahme
 * | --------------- 
 * */

$('.js_abnahme_set').click(function(e) {
    var id = e.target.dataset.id;

    $.ajax({
        url: '/checker/step/reparatur/auftragspositionen/js/status',
        type: 'POST',
        data: { 'id': id, type: 'abnahme' },
        success: function(msg) {
            if(msg.status_abnahme == 'success') {
                $(e.target).html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else if(msg.status_abnahme == 'notcompleted') {
                $(e.target).html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }
        }
    });
});

/* 
 * | ---------------
 * | Probefahrt
 * | --------------- 
 * */

$('.js_reparaturabnahme_probefahrt').click(function(e) {
    var auftragnr = e.target.dataset.auftragnr;
   
    $.ajax({
        url: '/checker/step/reparaturabnahme/probefahrt/js/status',
        type: 'POST',
        data: { 'auftragnr': auftragnr },
        success: function(msg) {
            if(msg.status == 'success') {
                $(e.target).closest('.switch_button').find('button').removeClass('active');
                $(e.target).addClass('active');
                
                $(e.target).closest('.switch_button').find('button').removeClass('btn-danger');
                $(e.target).closest('.switch_button').find('button').addClass('btn-default');
                $(e.target).addClass('switch_button').removeClass('btn-danger');
                $(e.target).addClass('switch_button').addClass('btn-danger');
            }
            
            $(e.target).closest('.row').find('.nutzer').text(msg.user);
            $(e.target).closest('.row').find('.wann').text(msg.date);
        }
    });
});

$('.js_erledigt').click(function(e) {
    var id = e.target.dataset.id;
    var text = $(e.target).closest('.flex').find('input[type="text"]').val();

    $.ajax({
        url: '/checker/step/reparaturabnahme/statusmeldung-kundenhinweis/js/erledigt',
        type: 'POST',
        data: { 'id': id, 'text': text }
    });
});

$('.js_kundenhinweis_kommentar_modal').click(function(e) {
    $('#kommentar_modal input').val(e.target.dataset.id);
    
    $('#kommentar_modal').modal('show');
});

$('.js_kundenhinweis_kommentar_add').click(function(e) {
    var kundenhinweise_id = $('#kommentar_modal input').val();
    var message = $('#kommentar_modal textarea').val();

    $.ajax({
        url: '/checker/step/reparaturabnahme/statusmeldung-kundenhinweis/js/kommentar/add',
        type: 'POST',
        data: { 'kundenhinweise_id': kundenhinweise_id, 'message': message },
        success: function(msg) {
            $('#comments_'+kundenhinweise_id).prepend(msg);
            $('#kommentar_modal').modal('hide');
        }
    });
});

$(document).on('click', '.js_kundenhinweis_kommentar_edit', function(e) {
    var id = $(e.target).data('id');
    var message = $(e.target).closest('.flex').find('textarea').val();

    $.ajax({
        url: '/checker/step/reparaturabnahme/statusmeldung-kundenhinweis/js/kommentar/edit',
        type: 'POST',
        data: { 'id': id, 'message': message },
    });
});

/* 
 * | ---------------
 * | Dokumentation Diverse
 * | --------------- 
 * */

$(document).on('submit', '#js_dokumentation_form', function(e) {
    e.preventDefault();
    
    var button = $(e.target).find('button');
    
    var id = $(e.target).find('input[name="id"]').val();
    var text = $(e.target).find('textarea[name="text"]').val();

    $.ajax({
        url: '/checker/step/reparaturabnahme/dokumentation/js/update',
        type: 'POST',
        data: { 'id': id, 'text': text },
        beforeSend: function() {
            button.html('<span class="loader"></span>');
        },
        success: function(msg){
            button.text('Speichern');
        },
        error: function (data) {
            console.log(data);
            button.text('Speichern');
        }
    });
});