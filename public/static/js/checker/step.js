/* 
 * | ---------------
 * | Basic
 * | --------------- 
 * */

/* $('.js_hinweise_head_more').click(function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var type = e.target.dataset.type;
    var modal = $('#hinweise_head_modal');
    
    $.ajax({
        url: '/checker/step/fahrzeugannahme/kundenhinweise/js/more',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'type': type },
        success: function(msg) {
            modal.find('.modal-title').text((type == 'success') ? 'Abgeschlossen' : (type == 'notcompleted') ? 'Nicht vollständig' : '');
            modal.find('.modal-body').html(msg);
            
            modal.modal('show');
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});*/

$('.js_quests_modal').click(function(e) {
    var type = e.target.dataset.type;
    var step = e.target.dataset.step;
    var auftragnr = e.target.dataset.auftragnr;
    
    var modal = $('#quests_modal');
    var title = 'Al';
    if(type == 'success') {
        title = 'Abgeschlossen';
    } else if(type == 'notcompleted') {
        title = 'Nicht abgeschlossen';
    }
    modal.find('.modal-title').text(title);
    
    $.ajax({
        url: '/checker/head-quests',
        type: 'POST',
        data: { 'type': type, 'step': step, 'auftragnr': auftragnr },
        success: function(msg) {
            modal.find('.modal-body').html(msg);
            
            modal.modal('show');
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

$(document).on('click', '.js_status_child_save', function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var step = e.target.dataset.step;
    var step_child = e.target.dataset.step_child;
    
    var place = e.target.dataset.place;
    
    var button = $(e.target);
    var go_ajax = true;
    
    var tasks = {};
    if(button.hasClass('is_task')) {
        tasks.child_id = e.target.dataset.child_id;
        tasks.parent = e.target.dataset.parent;
        tasks.task_type = $(e.target).closest('form').find('input[name="task_type"]').val();
        tasks.kundenhinweise_id = e.target.dataset.kundenhinweise_id;

        if(step == 'fahrzeugannahme') {

            if(tasks.task_type == 'diagnose' || tasks.task_type == 'durch_auftragspositionen_gelöst' || tasks.task_type == 'fehlerfrei_bedienfehler') { // rep_empfehlung

                tasks.message = $(e.target).closest('form').find('textarea').val();
            } else if(tasks.task_type == 'nachterminierung') {
                
                tasks.select = $(e.target).closest('form').find('select[name="select"]').val();
                tasks.message = $(e.target).closest('form').find('textarea').val();
            }
        } else if(step == 'reparatur') {
            /*if(e.target.dataset.task_type == 'rep_empfehlung') {
                tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
                tasks.message = $(e.target).closest('form').find('textarea').val();
            }
            if(e.target.dataset.task_type == 'rep_anweisung') {
                tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
            }*/
            
            if(tasks.task_type == 'behebung_kundehunweise') {
                //tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
                tasks.select = $(e.target).closest('form').find('select[name="select"]').val();
                tasks.message = $(e.target).closest('form').find('textarea').val();
            }
            if(tasks.task_type == 'rep_anweisung') {
                tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
            }
        } else if(step == 'reparaturabnahme') {
            
            //tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
            tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
            tasks.select = $(e.target).closest('form').find('select[name="select"]').val();
        } else if(step == 'rechnungsstellung') {
            
            //tasks.check = $(e.target).closest('form').find('input[type="checkbox"]').is(":checked");
        }
    }

    if(tasks.check !== undefined) {
        go_ajax = tasks.check;
    }

    if(go_ajax) {
        $.ajax({
            url: '/checker/status/save',
            type: 'POST',
            data: { 'auftragnr': auftragnr, 'step': step, 'step_child': step_child, 'tasks': JSON.stringify(tasks) },
            async: true,
            beforeSend : function() {
                button.html('<span class="loader"></span>');
                button.attr('disabled','disabled');
            },
            success: function(msg) {
                $(e.target).text('Ausführen');
                button.removeAttr('disabled');

                function children() {
                    var status = $(e.target).closest('.execute').find('.main_status');

                    if(msg.child.status == 'success') {
                        status.html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');
                    } else {
                        status.html('<i class="fa fa-times"></i>&nbsp;<span> nicht abgeschlossen</span>');
                    }

                    if(step == 'fahrzeugannahme') {
                        if(step_child == 'vsc') {
                            if(msg.child.status == 'success') {
                                $(e.target).closest('.steps_block').find('.panel-body .result').html('<a href="'+msg.result.fahrzeugannahme+'" target="_blank" class="btn btn-danger">öffnen PDF</a>')
                            }
                        }
                    }

                    if(step == 'kundenannahme') {
                        if(step_child == 'ersatzwagen') {
                            if(msg.child.status == 'success') {
                                if(msg.result.kundenannahme !== null) {
                                    $(e.target).closest('.steps_block').find('.ersatzwagen_testing').html('<a href="'+msg.result.kundenannahme+'" target="_blank" class="btn btn-danger">öffnen PDF</a>');
                                }
                            }
                        }
                    }

                    var step_menu = $('#navPageMenu #step_'+step);
                    if(msg.step_status.status != 'notcompleted') {
                        var time = new Date(msg.step_status.success_time);
                        
                        var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                        var time = myRe.exec(msg.step_status.success_time);

                        step_menu.find('a').html('<i class="fa fa-check"></i> '+msg.step_status.title+'<p>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +'</p>');
                        step_menu.find('a').css('background', '#bdffa7');
                        
                        $('#navPageMenu #step_reparatur').find('a').html('<i class="fa fa-check"></i> '+msg.step_status.title+'<p>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +'</p>');
                        $('#navPageMenu #step_reparatur').find('a').css('background', '#bdffa7');
                    } else {

                        step_menu.find('a').html('<i class="fa fa-times"></i> '+msg.step_status.title);
                        step_menu.find('a').removeAttr('style');
                    }
                }
                
                function task() {
                    if(button.hasClass('is_task')) {
                        button.attr('data-child_id', msg.result.task_id);

                        if(step == 'fahrzeugannahme') {
                            if(tasks.task_type == 'diagnose' || tasks.task_type == 'durch_auftragspositionen_gelöst' || tasks.task_type == 'fehlerfrei_bedienfehler' || tasks.task_type == 'nachterminierung') {
                                if(msg.child.status == 'success') {
                                    $('.task_main_status').html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');

                                    if(tasks.child_id == 'null') {
                                        if(tasks.select == 'diagnose') { // if(tasks.select == 'durch Auftragspositionen gelöst') {
                                            var step_menu = $('#navPageMenu #step_reparatur');
                                            step_menu.find('a').html('<i class="fa fa-times"></i> Reparatur');
                                            step_menu.find('a').removeAttr('style');
                                        }
                                        
                                        $(e.target).closest('form').find('select').attr('disabled', 'disabled');
                                        $(e.target).closest('form').find('textarea').attr('disabled', 'disabled');
                                        
                                        var block = $(e.target).closest('form').find('.checker_kinds .panel-body .panel');
                                        $(e.target).closest('form').find('.dashed').html(block);
 
                                        //
                                        var time = new Date().toISOString();
                                        var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                                        var time = myRe.exec(time);
                                        
                                        var step_num = (step == 'fahrzeugannahme') ? 2 : 3;
                                        $(e.target).closest('form').find('.breadcrumb').append('<li class="active">'+
                                            '<div class="box">'+
                                            '<span> '+ step_num +' STEP ('+step+')</span>'+
                                            '<h5>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +' <span class="label label-default">Administrator</span></h5>'+
                                            '</div>'+
                                        '</li>');
                                    }
                                }
                            }
                        }
                        if(step == 'reparatur') {
                            if(tasks.task_type == 'behebung_kundehunweise') {
                                if(msg.child.status == 'success') {
                                    $('.task_main_status').html('<i class="fa fa-check"></i>&nbsp;<span> abgeschlossen</span>');

                                    if(tasks.child_id == 'null') {
                                        if(tasks.select == 'Rep-Erweiterung') {
                                            var step_menu = $('#navPageMenu #step_fahrzeugannahme');
                                            step_menu.find('a').html('<i class="fa fa-times"></i> Fahrzeugannahme');
                                            step_menu.find('a').removeAttr('style');
                                        }
                                        
                                        //
                                        $(e.target).closest('form').find('select').attr('disabled', 'disabled');
                                        $(e.target).closest('form').find('textarea').attr('disabled', 'disabled');
                                        
                                        //
                                        var time = new Date().toISOString();
                                        var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                                        var time = myRe.exec(time);
                                        
                                        var step_num = (step == 'fahrzeugannahme') ? 2 : 3;
                                        $(e.target).closest('form').find('.breadcrumb').append('<li class="active">'+
                                            '<div class="box">'+
                                            '<span> '+ step_num +' STEP ('+step+')</span>'+
                                            '<h5>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +' <span class="label label-default">Administrator</span></h5>'+
                                            '</div>'+
                                        '</li>');
                                    }
                                }
                            }
                            if(tasks.task_type == 'rep_anweisung') {
                                if(msg.child.status == 'success') {
                                    if(tasks.child_id == 'null') {
                                        $(e.target).closest('form').find('input[type="checkbox"]').attr('disabled', 'disabled');
                                        
                                        //
                                        var time = new Date().toISOString();
                                        var myRe = new RegExp('([0-9]{4})\-([0-9]{2})\-([0-9]{2}).([0-9]{2})\:([0-9]{2})', 'g');
                                        var time = myRe.exec(time);
                                        
                                        var step_num = (step == 'fahrzeugannahme') ? 2 : 3;
                                        $(e.target).closest('form').find('.breadcrumb').append('<li class="active">'+
                                            '<span> '+ step_num +' STEP ('+step+')</span>'+
                                            '<h5>'+ time[3]+'-'+time[2]+'-'+time[1]+' '+time[4]+':'+time[5] +' <span class="label label-default">Administrator</span></h5>'+
                                        '</li>');
                                    }
                                }
                            }
                        }
                    }
                }
                
                if(place !== undefined) {
                    location.reload();
                }
                
                children();
                task();
                
                $('#head_hinweise_success').text(msg.count_success);
                $('#head_hinweise_notcompleted').text(msg.count_notcompleted);
            },
            error: function (data) {
                $(e.target).text('Ausführen');
                button.removeAttr('disabled');
            }
        });
    }
});

/* 
 * | ---------------
 * | Garantie / Kulanzantrag
 * | --------------- 
 * */

$(document).on('click', '.js_garantie_modal', function(e) {
    var id = e.target.dataset.id;
    var method = e.target.dataset.method;
    var block = $('#garantie_add_block');

    function clear() {
        block.find('input:not([value="Hersteller"],[value="Garantieversicherung"],[value="genehmigt"],[value="abgelehnt"])').val('');
        block.find('textarea[name="desc"]').val('');
        block.find('input').removeAttr('disabled');
        
        block.find('.block_g').removeClass('active');
        block.find('#g_kostenübernahme').addClass('active');
        
        block.find('#filemanager_insert_info_1').html('');
        block.find('#filemanager_insert_pick_1').val('');
        
        block.find('input[name="trager_name"]').attr('disabled', 'disabled');
        block.find('input[name="trager_nr"]').attr('disabled', 'disabled');
        block.find('input[name="abgelehnt"]').attr('disabled', 'disabled');
        
        block.find('input[value="Hersteller"]').prop('checked', true);
        block.find('input[value="genehmigt"]').prop('checked', true);
    }
    clear();
    
    function date_convert(dates) {
        if(dates !== null) {
            var re = /([0-9]*)\-([0-9]*)\-([0-9]*)/i;
            var found = dates.match(re);

            return found[3]+'.'+found[2]+'.'+found[1];
        }
    }

    if(method == 'edit') {
        $('#form_garantie_kulanzantrag').attr('data-method', 'update');
        
        $.ajax({
            url: '/checker/garantie-kulanzantrag/edit',
            type: 'POST',
            data: { id: id },
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                $('#form_edit_g').html('<input type="hidden" name="id" value="'+data.id+'">');
                
                block.find('textarea[name="desc"]').val(data.desc);
                block.find('.filemanager_open').attr('data-external_id', data.id);
                
                var files = data.file !== null ? data.file.split(',') : [];
                block.find('#filemanager_insert_info_1').html('<div class="alert alert-info" role="alert">'+files.length+'</div>');
                block.find('#filemanager_insert_pick_1').val(data.file);
                
                if(data.garantietrager == 'Hersteller') {
                    block.find('input[value="Hersteller"]').prop('checked', true);
                } else {
                    block.find('input[value="Garantieversicherung"]').prop('checked', true);
                    block.find('#g_garantieversicherung').addClass('active');
                    
                    block.find('input[name="trager_name"]').removeAttr('disabled');
                    block.find('input[name="trager_nr"]').removeAttr('disabled');
                    
                    block.find('input[name="trager_name"]').val(data.trager_name);
                    block.find('input[name="trager_nr"]').val(data.trager_nr);
                }
                
                block.find('input[name="auftragsnummer"]').val(data.auftragsnummer);
                block.find('#garantie_search_input').val(data.auftragsnummer);
                block.find('input[name="anfrage_create"]').val(date_convert(data.anfrage_create));
    
                if(data.anfrage_type == "Genehmigt") {
                    block.find('input[value="genehmigt"]').prop('checked', true);
                    
                    block.find('#g_kostenübernahme').removeClass('active');
                    block.find('#g_abgelehnt').removeClass('active');
                    block.find('#g_kostenübernahme').addClass('active');
                    
                    block.find('input[name="genehmigt_am"]').removeAttr('disabled');
                    block.find('input[name="genehmigt_in"]').removeAttr('disabled');
                    
                    block.find('input[name="abgelehnt"]').attr('disabled', 'disabled');
                    
                    block.find('input[name="genehmigt_am"]').val(date_convert(data.genehmigt_am));
                    block.find('input[name="genehmigt_in"]').val(data.genehmigt_in);
                } else {
                    block.find('input[value="abgelehnt"]').prop('checked', true);
                    
                    block.find('#g_kostenübernahme').removeClass('active');
                    block.find('#g_abgelehnt').removeClass('active');
                    block.find('#g_abgelehnt').addClass('active');
                    
                    block.find('input[name="abgelehnt"]').removeAttr('disabled');
                    
                    block.find('input[name="genehmigt_am"]').attr('disabled', 'disabled');
                    block.find('input[name="genehmigt_in"]').attr('disabled', 'disabled');
                    
                    block.find('input[name="abgelehnt"]').val(date_convert(data.abgelehnt));
                }
                
                $('#garantie_add_block').modal('show');
            }
        });
    } else {
        $('#form_garantie_kulanzantrag').attr('data-method', 'insert');
        $('#garantie_add_block').modal('show');
    }
});

$(document).on('change', '.js_genehmigt', function(e) {
    var val = $(e.target).val();

    var g_kostenübernahme = $('#g_kostenübernahme');
    var g_abgelehnt = $('#g_abgelehnt');
    
    g_kostenübernahme.removeClass('active');
    g_abgelehnt.removeClass('active');
    
    g_kostenübernahme.find('input').attr('disabled', 'disabled');
    g_abgelehnt.find('input').attr('disabled', 'disabled');
    
    if(val == 'genehmigt') {
        g_kostenübernahme.addClass('active');
        g_kostenübernahme.find('input').removeAttr('disabled');
    } else {
        g_abgelehnt.addClass('active');
        g_abgelehnt.find('input').removeAttr('disabled');
    }
});

$(document).on('change', '.js_garantieträger', function(e) {
    var block = $('#g_garantieversicherung');
    var val = $(e.target).val();
    
    if(val == 'Garantieversicherung') {
        block.addClass('active');
        block.find('input').removeAttr('disabled');
    } else {
        block.removeClass('active');
        block.find('input').attr('disabled', 'disabled');
    }
});

// Search Auftrag for 
var garantieSearchLoader = $('#garantie_search_loader');
$('#garantie_search_input').typeahead({
    autoSelect: true,
    minLength: 1,
    delay: 400,
    source: function (query, process) {
        garantieSearchLoader.show();
        $.ajax({
            url: "/checker/insert/search",
            data: {string: query},
            dataType: 'json',
        })
        .done(function (response) {
            garantieSearchLoader.hide();

            var arr = [];
            response.forEach(function (el, t) {
                arr.push(
                    {
                        id: t,
                        name: 'Auftrag: '+el.AUFTRAGSNR+', Name: '+el.NAME1,
                        auftragsnummer: el.AUFTRAGSNR
                    }
                );
            });

            return process(arr);
        });
    }
});
$('#garantie_search_input').change(function () {
    var current = $('#garantie_search_input').typeahead("getActive");
    $('#garantie_search_input_hidden').val(current.auftragsnummer);
});

var request_garantie = $("#form_garantie_kulanzantrag");
request_garantie.submit(function(e){
    e.preventDefault();
    var method = e.target.dataset.method;
 
    var loader = $('#garantie_button');
    var formData = request_garantie.serialize();
    $.ajax({
        url: '/checker/garantie-kulanzantrag/'+method,
        type: 'POST',
        data: formData,
        dataType: 'json',
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            loader.addClass('loading').prop("disabled", true);
        },
        success: function(data) {
            if (data == 'ok') {
                location.reload();
            }
        },
        error: function (data) {
            loader.removeClass('loading').prop("disabled", false);

            var alert = data.responseJSON;
            var errors = [];
            for(var a in alert) {
                errors.push(alert[a][0]);
            }
            Swal.fire({
                title: 'Ошибка!',
                html: errors.join('<br>'),
                type: 'error',
                confirmButtonText: 'Закрыть',
                showClass: {
                    popup: 'animated fadeInDown'
                },
                hideClass: {
                    popup: 'animated fadeOutUp'
                },
            });
        }
    }).done(function() {
        loader.removeClass('loading').prop("disabled", false);
    });
});

/* 
 * | ---------------
 * | Others
 * | --------------- 
 * */

$(document).on('submit', '.task_form', function(e) {
    e.preventDefault();
});

$(document).on('click', '.js_task_fahrzeugannahme_select', function(e) {
    $(e.target).closest('.switch_button3').find('button').removeClass('btn-danger').addClass('btn-default');
    
    $(e.target).removeClass('btn-default').addClass('btn-danger');
    $(e.target).closest('.switch_button3').find('input[name="type"]').val($(e.target).text());
});

$(document).on('click', '.js_collapse_button', function(e) {
    var item = $(e.target).closest('.task_panel').find('.collapse_tasks');
    
    if(item.hasClass('active')) {
        item.removeClass('active');
        item.collapse('hide');
    } else {
        item.addClass('active');
        item.collapse('show');
    }
});

$(document).on('change', '.js_kundenfahrzeug_pick input', function(e) {
    var id = $(e.target).closest('label').data('id');
    var val = $(e.target).val();

    var target = $('#text_form_'+id);
    if(val == 'no') {
        target.html(
            '<form class="form-inline" data-id="314">'+
                '<div class="form-group">'+
                  '<input type="text" class="form-control">'+
                '</div>'+
                '<button type="submit" class="btn btn-default" style="width: auto"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>'+
            '</form>'
        );
    } else {
        target.html('');
    }
});

$('.datepicker').datetimepicker({
    format: 'DD.MM.YYYY'
});