/* 
 * | ---------------
 * | Abarbeitung Auftragspositionen
 * | --------------- 
 * */

$('.js_auftragspositionen_set').click(function(e) {
    var id = e.target.dataset.id;

    $.ajax({
        url: '/checker/step/reparatur/auftragspositionen/js/status',
        type: 'POST',
        data: { 'id': id, 'type': 'auftragspositionen' },
        success: function(msg) {
            if(msg.status == 'success') {
                $(e.target).html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else if(msg.status == 'notcompleted') {
                $(e.target).html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }
        }
    });
});

/* 
 * | ---------------
 * | Rep-Empfehlung je Kundenbeanstandung (Freigabe SB notwendig)
 * | --------------- 
 * */

// Add
$('.js_kundenbeanstandung_modal').click(function() {
    var modal = $('#kundenbeanstandung_modal');
    
    modal.find('.modal-header').text('Hinzufügen - Rep-Empfehlung je Kundenbeanstandung');
    modal.find('.control').text('Hinzufügen');
    modal.find('.control').removeClass('js_kundenbeanstandung_update');
    modal.find('.control').addClass('js_kundenbeanstandung_add');
    
    modal.find('textarea').val('');
    modal.modal('show');
});
$(document).on('click', '.js_kundenbeanstandung_add', function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var title = $('#kundenbeanstandung_modal').find('textarea').val();
    
   $.ajax({
        url: '/checker/step/reparatur/kundenbeanstandung/js/add',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'title': title },
        success: function(msg) {
            $('.kundenbeanstandung_block .insert').prepend(msg);
            
            $('#kundenbeanstandung_modal').modal('hide');
        }
    });
});
// Edit
$(document).on('click', '.js_kundenbeanstandung_edit', function(e) {
    var id = e.target.dataset.id;
    
    $.ajax({
        url: '/checker/step/reparatur/kundenbeanstandung/js/edit',
        type: 'POST',
        data: { 'id': id },
        success: function(msg) {
            var modal = $('#kundenbeanstandung_modal');
            
            modal.find('.modal-header').text('Edit - Rep-Empfehlung je Kundenbeanstandung');
            modal.find('.control').text('Aktualisieren');
            modal.find('.control').removeClass('js_kundenbeanstandung_add');
            modal.find('.control').addClass('js_kundenbeanstandung_update');
            modal.find('.control').attr('data-id', msg.id);
            
            modal.find('textarea').val(msg.title);
            modal.modal('show');
        }
    });
});
$(document).on('click', '.js_kundenbeanstandung_update', function(e) {
    var id = e.target.dataset.id;
    var title = $('#kundenbeanstandung_modal').find('textarea').val();
    
    $.ajax({
        url: '/checker/step/reparatur/kundenbeanstandung/js/update',
        type: 'POST',
        data: { 'id': id, 'title': title },
        success: function(msg) {
            $('#kundenbeanstandung_modal').modal('hide');
            
            $('#kundenbeanstandung_item_'+msg.id).find('.panel-body').text(msg.title);
        }
    });
});
// Remove
$(document).on('click', '.js_kundenbeanstandung_remove', function(e) {
    
    Swal.fire({
      title: 'Wirklich löschen?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#1ab394',
      cancelButtonColor: '#f8ac59',
      confirmButtonText: 'Ja',
      cancelButtonText: 'Nein'
    }).then((result) => {
        if (result.isConfirmed) {
            
            var id = e.target.dataset.id;
            $.ajax({
                url: '/checker/step/reparatur/kundenbeanstandung/js/remove',
                type: 'POST',
                data: { 'id': id },
                success: function(id) {
                    $('#kundenbeanstandung_item_'+id).remove();
                    
                    Swal.fire({
                        title: 'Gelöscht!',
                        text: 'Entfernung war erfolgreich',
                        icon: 'success',
                        confirmButtonColor: '#1ab394'
                    })
                }
            });
        }
    })
});

/* 
 * | ---------------
 * | Vorschlag Rep-Erweiterung
 * | --------------- 
 * */
$('.js_vorschlag_set').click(function(e) {
    var id = e.target.dataset.id;
    
   $.ajax({
        url: '/checker/step/fahrzeugannahme/reparaturhinweise/js/status',
        type: 'POST',
        data: { 'id': id },
        success: function(msg) {
            if(msg.status == 'success') {
                $('#vorschlag_item_'+id+' .label').html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else if(msg.status == 'notcompleted') {
                $('#vorschlag_item_'+id+' .label').html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }
        }
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
        url: '/checker/step/reparatur/dokumentation/js/update',
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