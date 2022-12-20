/* 
 * | ---------------
 * | Erfassung Kundenhinweise
 * | --------------- 
 * */

// Add kundenhinweise
var kundenhinweise_count = 0;
$(document).on('click', '.js_kundenhinweise_add_f', function(e) {
    kundenhinweise_count++;
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/js/modal',
        type: 'POST',
        data: { 'id': kundenhinweise_count, 'step': 'fahrzeugannahme'  },
        success: function(msg){
            $('.kundenhinweise_block .insert').prepend(msg);
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});
//
$(document).on('submit', '#js_kundenhinweise_add_complete_f', function(e) {
    e.preventDefault();
    
    var auftragnr = $('input[name="auftragnr"]').val();
    var title = $(e.target).find('input[type="text"]').val();
    var step = $(e.target).find('input[type="hidden"]').val();

    var picked = $(e.target).closest('.item').find('.panel-heading form select').val();
    var status_child = $('.kundenhinweise_block').find('.execute .main_status');
 
    if(picked != 0) {
        $.ajax({
            url: '/checker/step/fahrzeugannahme/kundenhinweise/js/add',
            type: 'POST',
            data: { 'auftragnr': auftragnr, 'title': title, 'type': picked, 'step': step },
            success: function(msg){
                var item = $(e.target).closest('.item');

                item.find('.panel-heading').find('.select_type').attr('data-id', msg.kundenhinweise.id);
                item.find('.panel-heading').find('.js_remove_kundenhinweise_f').attr('data-id', msg.kundenhinweise.id);
                item.find('.panel-heading').find('.js_remove_kundenhinweise_f').removeAttr('data-first_create');
                item.find('.panel-heading').find('form').addClass('js_kundenhinweise_select_type');
                item.find('.panel-heading').find('form').append('<button type="submit" class="btn btn-primary">ОК</button>');
                item.find('.panel-heading').find('.form-group').removeClass('has-error');
                item.find('.panel-heading').find('.select_info').remove();
                
                item.find('.panel-heading .row').html(msg.head);
                item.find('.panel-body').html(msg.body);

                item.find('.panel-footer .status_block').html(
                    'status '+
                    '<span class="label label-default"> <i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span></span>'
                );

                item.attr('id', 'kundenhinweise_item_edit_'+msg.kundenhinweise.id);
                
                $('#head_hinweise_success').text(msg.count_success);
                $('#head_hinweise_notcompleted').text(msg.count_notcompleted);
                
                let step = $('#step_fahrzeugannahme');
                step.find('a i').addClass('fa-check');
                step.find('a i').addClass('fa-times');
                step.find('a').removeAttr('style');
                step.find('p').empty();
                
                status_child.html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            },
            error: function (data) {
                alertifyError(data);
            }
        });
    }
});

// edit
$(document).on('submit', '.js_kundenhinweise_edit_f', function(e) {
    e.preventDefault();
    
    var id = e.target.dataset.id;
    var title = $(e.target).find('input[type="text"]').val();
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/js/edit',
        type: 'POST',
        data: { 'id': id, 'title': title },
        success: function(msg){
            if(msg == 'ok') {
                Swal.fire({
                    title: 'Geändert!',
                    text: 'Bearbeitung war erfolgreich',
                    icon: 'success',
                    confirmButtonColor: '#1ab394'
                })
            }
        }
    });
});

// Remove
$(document).on('click', '.js_remove_kundenhinweise_f', function(e) {
    
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
            if(e.target, e.target.dataset.first_create !== undefined) {
                $(e.target).closest('.item').remove();
            } else {
                var auftragnr = $('input[name="auftragnr"]').val();
                
                $.ajax({
                    url: '/checker/step/fahrzeugannahme/kundenhinweise/js/remove',
                    type: 'POST',
                    data: { 'id': e.target.dataset.id, 'auftragnr': auftragnr },
                    success: function(msg){
                        $(e.target).closest('.item').remove();
                            
                        $('#head_hinweise_success').text(msg.count_success);
                        $('#head_hinweise_notcompleted').text(msg.count_notcompleted);

                        Swal.fire({
                            title: 'Gelöscht!',
                            text: 'Entfernung war erfolgreich',
                            icon: 'success',
                            confirmButtonColor: '#1ab394'
                        });
                    },
                    error: function (data) {
                        alertifyError(data);
                    }
                });
            }
        }
    })
});

// Set type from kundenhinweise
$(document).on('submit', '.js_kundenhinweise_select_type', function(e) {
    e.preventDefault();
    
    var picked = $(e.target).find('select').val();
    var id = e.target.dataset.id;
    var method = e.target.dataset.method;

    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/js/set',
        type: 'POST',
        data: { 'id': id, 'type': picked },
        success: function(msg){
            $('#kundenhinweise_item_'+method+'_'+id+' .panel-heading .form-group').removeClass('has-error');
            $('#kundenhinweise_item_'+method+'_'+id+' .panel-heading .form-group .select_info').remove();
            
            var button = $('#kundenhinweise_item_'+method+'_'+id+' .panel-heading .form-group button');
            button.removeClass('btn-danger');
            button.addClass('btn-primary');
            
            $('#kundenhinweise_item_'+method+'_'+id+' .panel-body').html(msg);
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

// Open modal proccess kundenhinweise
$(document).on('click', '.js_kundenhinweise_modal', function(e) {
    var type = e.target.dataset.type;
    var kundenhinweise_id = e.target.dataset.kundenhinweise_id;
    var auftragnr = e.target.dataset.auftragnr;

    var modal = $('#kundenhinweise_modal');

    if(type == 'angebot') {
        
        modal.find('.modal-title').text('Angebot');
        modal.find('.modal-body').html('<p>1 Here API Angebot Angebot</p>'+'<p>2 Here API Angebot Angebot</p>'+'<p>N Here API Angebot Angebot</p>');
    } else if(type == 'technische') {
        
        modal.find('.modal-title').text('Technische Hinweise');
        $.ajax({
            url: '/checker/step/fahrzeugannahme/kundenhinweise/tasks/technische/index',
            type: 'POST',
            data: {'kundenhinweise_id': kundenhinweise_id, 'auftragnr': auftragnr},
            success: function(msg) {
                modal.find('.modal-body').html(msg);
            }
        });
    } else if(type == 'fahrzeugnutzung') {
        
        modal.find('.modal-title').text('Hinweise zum Fahrzeugnutzung');
        modal.find('.modal-body').html('<p>insert a check mark that is familiar</p>');
    } else if(type == 'serviceberater') {
        
        modal.find('.modal-title').text('Serviceberater');
        modal.find('.modal-body').html('<p>code</p>');
    }

    modal.find('.js_kundenhinweise_save').attr('data-kundenhinweise_id', kundenhinweise_id);
    modal.find('.js_kundenhinweise_save').attr('data-type', type);
    
    modal.modal('show');
});

// save
$(document).on('click', '.js_kundenhinweise_save', function(e) {
    var hinweise_type = e.target.dataset.type;
    var kundenhinweise_id = e.target.dataset.kundenhinweise_id;
    var auftragnr = $(this).closest('.modal-content').find('input[name="auftragnr"]').val();
    var insert_task_type = e.target.dataset.task_type_insert;
    
    if(insert_task_type !== undefined) {
        insert_task_type = {};
        
        insert_task_type.task_type = $(e.target).closest('.modal-content').find('input[name="type"]').val();
        insert_task_type.message = $(e.target).closest('.modal-content').find('textarea[name="message"]').val();
        
        insert_task_type.select = null;
        if(insert_task_type.task_type == 'nachterminierung') {
            insert_task_type.select = $(e.target).closest('.modal-content').find('select[name="select"]').val();
        }
    } else {
        insert_task_type = 'empty';
    }

    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/js/save',
        type: 'POST',
        data: { 'kundenhinweise_id': kundenhinweise_id, 'auftragnr': auftragnr, 'hinweise_type': hinweise_type, 'insert_task_type': insert_task_type },
        beforeSend: function() {
            $(e.target).attr('disabled', 'disabled');
        },
        success: function(msg) {
            if(insert_task_type !== 'empty') {
                $('#kundenhinweise_modal .modal-body').html(msg.result.template);

                if(msg.result.status.status == 'success') {
                    $('#navPageMenu li').removeClass('active');
                    $('#step_'+$('input[name="step"]').val()).addClass('active');
                }
                
                if(insert_task_type.task_type == 'diagnose') {
                    var step_menu = $('#navPageMenu #step_reparatur');
                        step_menu.find('a').html('<i class="fa fa-times"></i> '+msg.result.status.title);
                        step_menu.find('a').removeAttr('style');
                }
                
                var title = $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.work_block input[type="text"]').val();
                $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.work_block .title').html('<div class="title">'+title+'</div>');

                var type = 'Behoben (Fehlerfrei/Bedienfehler)';
                if (msg.result.item.task == 'fehlerfrei_bedienfehler') {
                    type = 'Behoben (Fehlerfrei/Bedienfehler)';
                } else if (msg.result.item.task == 'durch_auftragspositionen_gelöst') {
                    type = 'Reparatur (durch Auftragspositionen gelöst)';
                } else if (msg.result.item.task == 'diagnose') {
                    type = 'Diagnose';
                } else if (msg.result.item.task == 'nachterminierung') {
                    type = 'Nachterminierung';
                }
                console.log(msg);
                var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                var time = myRe.exec(msg.result.item.set_time);
                $('#kundenhinweise_item_edit_'+kundenhinweise_id+' .panel-body').append('<hr>'+
                    '<div class="work_block">'+
                        '<div class="w_block">'+
                            '<u>'+type+'</u>: '+insert_task_type.message+ ' | Administrator | '+ time[3]+'.'+time[2]+'.'+time[1]+' '+time[4]+':'+time[5] +
                        '</div>'+
                    '</div>'+
                '');
            }
            
            var panel = $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.panel-footer');
            if(msg.status == 'success') {
                panel.removeClass('not_notcompleted_hinweise'); panel.addClass('not_success_hinweise');
                panel.find('.label').html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else {
                panel.removeClass('not_success_hinweise'); panel.addClass('not_notcompleted_hinweise');
                panel.find('.label').html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }

            setTimeout(function() {
                $('#kundenhinweise_modal').modal('hide');
                
                $('#head_hinweise_success').text(msg.count_success);
                $('#head_hinweise_notcompleted').text(msg.count_notcompleted);
                
                $(e.target).removeAttr('disabled');
                $(e.target).removeAttr('data-task_type_insert');
            }, 1100);
        }
    });
    
    //
    /*var type = $(e.target).closest('.panel').find('input[name="type"]').val();
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
    }*/
});

/* 
 * | ---------------
 * | Erfassung Reparaturhinweise für Mechaniker
 * | --------------- 
 * */

// Add
/*  $('.js_reparaturhinweise_modal').click(function() { 
    var modal = $('#reparaturhinweise_modal');
    
    modal.find('.modal-header').text('Add - Erfassung Reparaturhinweise für Mechaniker');
    modal.find('.control').text('add');
    modal.find('.control').removeClass('js_reparaturhinweise_update');
    modal.find('.control').addClass('js_reparaturhinweise_add');
    
    modal.find('textarea').val('');
    modal.modal('show');
});
$(document).on('click', '.js_reparaturhinweise_add', function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var title = $('#reparaturhinweise_modal').find('textarea').val();
    
   $.ajax({
        url: '/checker/step/fahrzeugannahme/reparaturhinweise/js/add',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'title': title },
        success: function(msg) {
            $('.reparaturhinweise_block .insert').prepend(msg);
            
            $('#reparaturhinweise_modal').modal('hide');
        }
    });
});
// Edit
$(document).on('click', '.js_reparaturhinweise_edit', function(e) {
    var id = e.target.dataset.id;
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/reparaturhinweise/js/edit',
        type: 'POST',
        data: { 'id': id },
        success: function(msg) {
            var modal = $('#reparaturhinweise_modal');
            
            modal.find('.modal-header').text('Edit - Erfassung Reparaturhinweise für Mechaniker');
            modal.find('.control').text('update');
            modal.find('.control').removeClass('js_reparaturhinweise_add');
            modal.find('.control').addClass('js_reparaturhinweise_update');
            modal.find('.control').attr('data-id', msg.id);
            
            modal.find('textarea').val(msg.title);
            modal.modal('show');
        }
    });
});
$(document).on('click', '.js_reparaturhinweise_update', function(e) {
    var id = e.target.dataset.id;
    var title = $('#reparaturhinweise_modal').find('textarea').val();
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/reparaturhinweise/js/update',
        type: 'POST',
        data: { 'id': id, 'title': title },
        success: function(msg) {
            $('#reparaturhinweise_modal').modal('hide');
            
            $('#reparaturhinweise_item_'+msg.id).find('.panel-body').text(msg.title);
        }
    });
});
// Remove
$(document).on('click', '.js_reparaturhinweise_remove', function(e) {
    var id = e.target.dataset.id;
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/reparaturhinweise/js/remove',
        type: 'POST',
        data: { 'id': id },
        success: function(id) {
            $('#reparaturhinweise_item_'+id).remove();
        }
    });
});*/

// Add Auftrag
$(document).on('submit', '#auftrag_add', function(e) {
    e.preventDefault();
    
    var arr = [];
    var picks = $(e.target).find('input[type="checkbox"]');
    picks.each(function(e, v) {
        if(v.checked) {
            arr.push($(v).val());
        }
    });
    
    var button = $('#auftrag_button');
    var auftrag_id = $('input[name="auftrag_id"]').val();
    
    $('#auftrag_errors').empty();

    $.ajax({
        url: '/checker/step/fahrzeugannahme/auftrag/add',
        type: 'POST',
        data: { 'pick': JSON.stringify(arr), 'auftrag_id': auftrag_id },
        beforeSend : function() {
            button.html('<span class="loader"></span>');
            button.attr('disabled','disabled');
        },
        success: function(msg) {
            button.text('Hinzufügen');
            button.removeAttr('disabled');
            
            location.reload();
        },
        error: function(msg) {
            var errors = msg.responseJSON;
            
            button.text('Hinzufügen');
            button.removeAttr('disabled');

            for(var e in errors) {
                $('#auftrag_errors').append('<div class="alert alert-danger" role="alert">'+errors[e]+'</div>');
            }
        }
    });
});