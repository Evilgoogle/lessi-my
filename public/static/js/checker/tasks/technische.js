$(document).on('click', '.js_technische_select', function(e) {
    var pick = $(e.target).closest('.flex').find('select').val();
    var auftragnr = $(e.target).closest('.flex').find('input[name="auftragnr"]').val();
    var kundenhinweise_id = $(e.target).closest('.flex').find('input[name="kundenhinweise_id"]').val();
    var place = e.target.dataset.place;

    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/tasks/technische/forms',
        type: 'POST',
        data: { 'pick': pick, 'auftragnr': auftragnr, kundenhinweise_id: kundenhinweise_id, 'place': place },
        success: function(msg) {
            $(e.target).closest('.checker_kinds').find('.panel-body').html(msg);
            
            $('.js_kundenhinweise_save').attr('data-task_type_insert', 'ok');
        }
    });
});

/*$(document).on('click', '#task_block', function(e) {
    var type = $(e.target).closest('.panel').find('input[name="type"]').val();
    var auftragnr = $(e.target).closest('.panel').find('input[name="auftragnr"]').val();
    var kundenhinweise_id = $(e.target).closest('.panel').find('input[name="kundenhinweise_id"]').val();
    var message = $(e.target).closest('.panel').find('textarea[name="message"]').val();
    
    var select = null;
    if(type == 'nachterminierung') {
        select = $(e.target).closest('.panel').find('select[name="select"]').val();
    }

    function set_ajax() {
        $.ajax({
            url: '/checker/step/fahrzeugannahme/kundenhinweise/tasks/technische/add',
            type: 'POST',
            data: { 'type': type, 'auftragnr': auftragnr, kundenhinweise_id: kundenhinweise_id, message: message, select: select },
            success: function(msg) {
                $('#kundenhinweise_modal .modal-body').html(msg.template);

                if(msg.status.status == 'success') {
                    $('#navPageMenu li').removeClass('active');
                    $('#step_'+$('input[name="step"]').val()).addClass('active');
                }
                
                if(type == 'diagnose') {
                    var step_menu = $('#navPageMenu #step_reparatur');
                        step_menu.find('a').html('<i class="fa fa-times"></i> '+msg.status.title);
                        step_menu.find('a').removeAttr('style');
                }
                
                var title = $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.work_block input[type="text"]').val();
                $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.work_block .title').html('<div class="title">'+title+'</div>');

                var type = 'Behoben (Fehlerfrei/Bedienfehler)';
                if (msg.item.task == 'fehlerfrei_bedienfehler') {
                    type = 'Behoben (Fehlerfrei/Bedienfehler)';
                } else if (msg.item.task == 'durch_auftragspositionen_gelöst') {
                    type = 'Reparatur (durch Auftragspositionen gelöst)';
                } else if (msg.item.task == 'diagnose') {
                    type = 'Diagnose';
                } else if (msg.item.task == 'nachterminierung') {
                    type = 'Nachterminierung';
                }
                $('#kundenhinweise_item_edit_'+kundenhinweise_id+' .panel-body').append('<hr>'+
                    '<div class="work_block">'+
                        '<div class="w_block">'+
                            '<u>'+type+'</u>'+
                        '</div>'+
                    '</div>'+
                '');
            }
        });
    }
    
    if(type == 'diagnose') {
        
        if(message != '') {
            set_ajax();
        }
    } else {
        set_ajax();
    }
});*/