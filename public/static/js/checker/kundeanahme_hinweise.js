/* 
 * | ---------------
 * | Erfassung Kundenhinweise
 * | --------------- 
 * */

// Add kundenhinweise
var kundenhinweise_count = 0;
$(document).on('click', '.js_kundenhinweise_add', function(e) {
    kundenhinweise_count++;

    var kundenhinweise_id = e.target.dataset.kundenhinweise_id;
    var in_modal = e.target.dataset.in_modal;
    
    $.ajax({
        url: '/checker/step/kundenannahme/kundenhinweise/js/modal',
        type: 'POST',
        data: { 'id': kundenhinweise_count, 'step': 'kundenannahme', 'in_modal':  in_modal === undefined ? false : true },
        success: function(msg){
            if(in_modal === undefined) {
                $('.kundenhinweise_block .insert').prepend(msg);
            } else {
                $('#quests_modal .insert').prepend(msg);
            }
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});
//
$(document).on('submit', '#js_kundenhinweise_add_complete', function(e) {
    e.preventDefault();
    
    var auftragnr = $('input[name="auftragnr"]').val();
    var title = $(e.target).find('input[type="text"]').val();
    var step = $(e.target).find('input[name="step"]').val();
    var in_modal = $(e.target).find('input[name="in_modal"]').val();;

    var picked = $(e.target).closest('.item').find('.panel-heading form select').val();
    var status_child = $('.kundenhinweise_block').find('.execute .main_status');

    if(picked != 0) {
        $.ajax({
            url: '/checker/step/kundenannahme/kundenhinweise/js/add',
            type: 'POST',
            data: { 'auftragnr': auftragnr, 'title': title, 'type': picked, 'step': step, 'in_modal':  in_modal === undefined ? false : true },
            success: function(msg){
                var item = $(e.target).closest('.item');

                item.find('.panel-heading').find('.js_remove_kundenhinweise').attr('data-id', msg.kundenhinweise.id);
                item.find('.panel-heading').find('.js_remove_kundenhinweise').removeAttr('data-first_create');

                item.find('.panel-heading .row').html(msg.head);
                item.find('.panel-body').html(msg.body);

                item.find('.panel-footer .status_block').html(
                    'status '+
                    '<span class="label label-default"> nicht abgeschlossen</span>'
                );

                item.attr('id', 'kundenhinweise_item_edit_'+msg.kundenhinweise.id);
                
                $('#head_hinweise_success').text(msg.count_success);
                $('#head_hinweise_notcompleted').text(msg.count_notcompleted);

                if(in_modal === undefined) {
                    let step = $('#step_kundenannahme');
                    step.find('a i').addClass('fa-check');
                    step.find('a i').addClass('fa-times');
                    step.find('a').removeAttr('style');
                    step.find('p').empty();
                } else {
                    let step = $('#step_fahrzeugannahme');
                    step.find('a i').addClass('fa-check');
                    step.find('a i').addClass('fa-times');
                    step.find('a').removeAttr('style');
                    step.find('p').empty();
                }
                
                setTimeout(function() {
                    if(in_modal !== undefined) {
                        location.reload();
                    }
                }, 600);

                status_child.html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
            },
            error: function (data) {
                alertifyError(data);
            }
        });
    }
});

// Remove
$(document).on('click', '.js_remove_kundenhinweise', function(e) {
    
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
                    url: '/checker/step/kundenannahme/kundenhinweise/js/remove',
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
                        })
                    },
                    error: function (data) {
                        alertifyError(data);
                    }
                });
            }
        }
    })
});