/* 
 * | ---------------
 * | Erfassung Kundenhinweise
 * | --------------- 
 * */

// save
$(document).on('click', '.js_kundenhinweise_save', function(e) {
    
    var kundenhinweise_id = e.target.dataset.kundenhinweise_id;
    
    $.ajax({
        url: '/checker/step/kundenannahme/kundenhinweise/js/save',
        type: 'POST',
        data: { 'id': kundenhinweise_id },
        success: function(msg) {
            var label = $('#kundenhinweise_item_edit_'+kundenhinweise_id).find('.panel-footer .label');
     
            if(msg.status == 'success') {
                label.html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else {
                label.html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }
            
            $('#kundenhinweise_modal').modal('hide');
        }
    });
});
 
/* 
 * | ---------------
 * | Erfassung sonstige Infos
 * | --------------- 
 * */

function infos_ajax(id, desc, sonstige = false) {
   $.ajax({
        url: '/checker/step/kundenannahme/infos/js/set',
        type: 'POST',
        data: { 'id': id, 'desc': desc, 'sonstige': (sonstige) ? 1 : 0 },
        success: function(msg) {
            if(sonstige === false) {
                var button = $('#kundenannahme_infos_b_'+msg.query.id);
                if(button.hasClass('btn-primary')) {
                    button.removeClass('btn-primary');
                    button.addClass('btn-danger');  
                } else {
                    button.removeClass('btn-danger');
                    button.addClass('btn-primary');
                }

                button.attr('data-desc', msg.query.desc);

                if(msg.query.set) {
                    $('.infos_block .insert_tags').append(msg.templete);
                } else {
                    $('#kundenannahme_infos_'+id).remove();
                }

                if(msg.query.tag == 'Warteort' || msg.query.tag == 'Verkaufsberatung erwünscht') {
                    $('#kundenannahme_infos_'+id).find('.panel-body').text(desc);
                }
            } else {
                
                $('.infos_block .insert_sonstige').append(msg.templete);
                $('#kundenannahme_infos_sonstige').modal('hide');
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
}
$('.js_infotag_set').click(function(e) {
    
    var id = e.target.dataset.id;
    var desc = e.target.dataset.desc;
    var modal = e.target.dataset.modal;

    if(modal === undefined) {
        infos_ajax(id, desc);
    } else {
        var modal = $('#kundenannahme_infos_modal');
        modal.find('.modal-title').text($(e.target).text());
        modal.find('.js_infotag_set_modal').attr('data-id', id);
        modal.find('textarea').val(desc);
        
        modal.modal('show');
    }
});
$(document).on('click', '.js_infotag_set_modal', function(e) {
    
    var id = e.target.dataset.id;
    var desc = $(e.target).closest('.modal').find('textarea').val();
    
    $('#kundenannahme_infos_modal').modal('hide');
    
    infos_ajax(id, desc);
});

$('.js_kundenannahme_sonstige_add').click(function(e) {
    
    $('.js_infos_sonstige_set_modal').attr('data-auftragnr', e.target.dataset.auftragnr);
    $('#kundenannahme_infos_sonstige').modal('show');
});
$(document).on('click', '.js_infos_sonstige_set_modal', function(e) {
    
    var auftragnr = e.target.dataset.auftragnr;
    var desc = $(e.target).closest('.modal').find('textarea').val();
    
    infos_ajax(auftragnr, desc, true);
});
$(document).on('click', '.js_kundenannahme_sonstige_remove', function(e) {
    $.ajax({
        url: '/checker/step/kundenannahme/infos/js/remove',
        type: 'POST',
        data: { 'id': e.target.dataset.id },
        success: function(msg) {
            if(msg == 'ok') {
                $(e.target).closest('.tag').remove();
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

/* 
 * | ---------------
 * | Save Status
 * | --------------- 
 * */

$('.js_kundenannahme_frzg').click(function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var type_frzg = e.target.dataset.type;
    var step = e.target.dataset.step;
    var step_child = e.target.dataset.step_child;
    
    $.ajax({
        url: '/checker/status/save',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'step': step, 'step_child': step_child, 'type_frzg': type_frzg },
        success: function(msg) {
            // child
            var status = $(e.target).closest('.steps_block').find('.execute .main_status');
 
            if(msg.child.status == 'success') {
                status.html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
            } else {
                status.html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            }
            
            
            var button = $(e.target);
            button.closest('.switch_button').find('button').removeClass('active');
            button.closest('.switch_button').find('button').removeClass('btn-danger');
            button.closest('.switch_button').find('button').addClass('btn-default');
            button.addClass('btn-danger');
            button.addClass('active');
            
            // step
            var step_menu = $('#navPageMenu #step_'+step);
            if(msg.step_status.status != 'notcompleted') {

                var time = new Date(msg.step_status.success_time);
                        
                var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                var time = myRe.exec(msg.step_status.success_time);

                step_menu.find('a').html('<i class="fa fa-check"></i> '+msg.step_status.title+'<p>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +'</p>');
                step_menu.find('a').css('background', '#bdffa7');
            } else {
                
                step_menu.find('a').html('<i class="fa fa-times"></i> '+msg.step_status.title);
                step_menu.find('a').removeAttr('style');
            }
        }
    });
});
