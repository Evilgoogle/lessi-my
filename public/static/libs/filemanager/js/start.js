import * as LIBS_BASIC from './libs/basic.js';
import * as LIBS_IMAGE from './libs/image.js';
import * as LIBS_FILE from './libs/file.js';

/* ------->
 * SETTING
 * 
/ --------> */

// Выбранный файл
var selected_file = {};
var selected_file_id = 0;

// Текстовый контейнер ckeditor
var ckeditor = false;

// Индентификатор файл манеджера
var basic_id;

// SQL Conditions for where
var conds = [];

// Model
var model = false;

// Путь к папке
var path_file = 'files';

// Cropper
var cropper;

// Upload file types
var formats = [
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml', 
    'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain',
    'video/mp4', 'application/msword'
];

var external_id = false;
var temp_id = false;
var insert_page = 'off'; // id | file
var in_modal;

// Paginator
var file_inProgress = false; //флаг для отслеживания того, происходит ли в данный момент ajax-запрос
var file_count = 2; //начинать пагинацию со 2 страницы, т.к. первая выводится сразу
var file_count_paginate = 2; //начинать пагинацию со 2 страницы, т.к. первая выводится сразу

/* ------->
 * WORK
 * 
/ --------> */

// Вызовы модалки для загрузки файлов
$(document).on('click', '.filemanager_open', function () {

    basic_id = $(this).data('basic_id');
    
    model = $(this).data('model');
    path_file = $(this).data('path');
    
    var collect = [];
    for(var item in $(this).data()) {
        if(item != 'basic_id' && item != 'model' && item != 'path' && item != 'insert_page' && item != 'external_id' && item != 'temp_id' && item != 'in_modal') {
            collect.push([item, '=', $(this).data()[item]]);
        }
    }
    conds = collect;

    // Вызов модалки
    ckeditor = false;
    if($(this).data('insert_page') !== undefined) {
        insert_page = $(this).data('insert_page');
        
        temp_id = $(this).data('temp_id');
        if($(this).data('external_id') !== undefined) {
            external_id = $(this).data('external_id');
        }
    }
 
    // модалка открыта в модалке
    in_modal = $(this).data('in_modal');
    if(in_modal !== undefined) {
        var other_modal = $(in_modal);

        setTimeout(function () {
            $('#file_manager').modal('show');
        }, 1000);
        other_modal.modal('hide');
    } else {
        $('#file_manager').modal('show');
    }
    
    file_inProgress = false;
    file_count = 2;
    file_count_paginate = 2;
    
    if(model) {
        LIBS_BASIC.reload(model, conds, path_file, insert_page, external_id);
    
        start();
    }
});

$('#file_manager').on('hidden.bs.modal', function (e) {
    if(in_modal !== undefined) {
         $(in_modal).modal('show');
    }
});

// Вызовы модалки для загрузки файлов в ckeditor
window.call_filemanager = function (editor) {

    model = editor.config.model;
    path_file = editor.config.path;

    // Вызов модалки
    ckeditor = editor;
    insert_page = 'off';
    $('#file_manager').modal('show');
    
    file_inProgress = false;
    file_count = 2;
    file_count_paginate = 2;

    if(model) {
        LIBS_BASIC.reload(model, conds, path_file, insert_page, external_id);
    
        start();
    }
};

function start() {
    cropper = new LIBS_IMAGE.imageCropper(path_file);
    LIBS_BASIC.container(model, conds, path_file, insert_page, external_id);
    LIBS_IMAGE.panel(model, path_file, cropper);
    
    if(!ckeditor) {
        if(insert_page == 'id' || insert_page == 'file') {
            $('.js_file_set').text('Sparen');
        } else {
            $('.js_file_set').remove();
        }
    }
}

// Загрузка файлов на сервер
document.getElementById('select_init').addEventListener('change', select_init, false);
function select_init(evt, drop = false) {

    var alerts = $('#file_manager .alerts'),
        load = $('#file_manager #upload_block .load'),
        blue = $('#file_manager .ajax_content .bn .rubber'),
        content = $('#file_manager .ajax_content .flex');

    // clear
    alerts.empty();

    // init
    if(drop === false) {
        var files = evt.target.files;
    } else if(drop === true) {
        var files = evt.originalEvent.dataTransfer.files;
    }
    [].forEach.call(files, function(file, i) {
        if (!LIBS_BASIC.inArray(file.type, formats)) {
            alerts.append('<button class="alert alert-danger js_alert" role="alert"><span class="badge">'+i+'</span> '+ file.name + ' - ist keine Datei des Formats:(jpg, png, gif, svg, pdf, doc, docx, txt, mp4)</button>');
            LIBS_BASIC.close_modal();
        } else {
            var reader = new FileReader();
            reader.onload = function(e) {

                $.ajax({
                    url: '/filemanager/upload',
                    method: 'POST',
                    data: { model: model, name: file.name, file: e.target.result, size: file.size, type: file.type, conds: conds, path: path_file, insert_page: insert_page, external_id: external_id },
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    },
                    // выполнить до отправки запроса
                    beforeSend: function() {
                        load.addClass('loading');
                    },
                    // Ответ от сервера
                    success: function(msg){
                        content.prepend(msg);
                    },
                    // Ошибка AJAX
                    error: function(result){
                        alerts.append('<button class="alert alert-danger js_alert" role="alert"><span class="badge">'+i+'</span> '+ file.name +' - '+result.responseJSON+'</button>');
                        load.removeClass('loading');
                        LIBS_BASIC.close_modal();
                    }
                    // сразу после выполнения запроса
                }).done(function(data){
                    load.removeClass('loading');
                    blue.removeClass('blue');
                    LIBS_BASIC.close_modal();
                });
            };
            reader.readAsDataURL(file);
        }
    });
}

// Загрузка файлов на сервер с Drop 
$('#drop_zone').on('dragover', e => {
    return false;
});
$('#drop_zone').on('dragleave', e => {
    return false;
});
$('#drop_zone').on('drop', e => {
    e.preventDefault();

    select_init(e, true);
});

// Замена файлов
document.getElementById('replace_init').addEventListener('change', replace_init, false);
function replace_init(evt) {

    var alerts = $('#file_manager .alerts'),
        load = $('#file_manager #replace_block .load');

    // clear
    alerts.empty();

    //init
    var files = evt.target.files;
    if (!LIBS_BASIC.inArray(files[0].type, formats)) {
        alerts.append('<button class="alert alert-danger js_alert" role="alert"><span class="badge">'+i+'</span> '+ files[0].name + ' - ist keine Datei des Formats:(jpg, png, gif, svg, pdf, doc, docx, txt, mp4)</button>');
        LIBS_BASIC.close_modal();
    } else {
        var reader = new FileReader();
        reader.onload = function(e) {

            $.ajax({
                url: '/filemanager/replace',
                method: 'POST',
                data: { model: model, id: selected_file_id, file: e.target.result, size: files[0].size, type: files[0].type, path: path_file },
                // выполнить до отправки запроса
                beforeSend: function() {
                    load.addClass('loading');
                },
                // Ответ от сервера
                success: function(msg){

                    LIBS_BASIC.update_elements(msg, path_file+'/', msg.rand);
                    $('#file_manager .ajax_content .bn .column [data-id="'+selected_file_id+'"]').trigger('click');
                    LIBS_BASIC.close_modal();
                },
                // Ошибка AJAX
                error: function(result){
                    alerts.append('<button class="alert alert-danger js_alert" role="alert">'+ files[0].name +' - '+result.responseJSON+'</button>');
                    load.removeClass('loading');
                    LIBS_BASIC.close_modal();
                }
                // сразу после выполнения запроса
            }).done(function(data){
                load.removeClass('loading');
                LIBS_BASIC.close_modal();
            });
        };
        reader.readAsDataURL(files[0]);
    }
}

/* -> Выборка */
$(document).on('click', '#file_manager .ajax_content .bn .js_select', function (event) {

    $('#file_manager .js_select').removeClass('active');
    $(this).addClass('active');

    var id = event.target.dataset.id,
        title = event.target.dataset.title,
        file = event.target.dataset.file,
        alt = event.target.dataset.alt,
        mass = event.target.dataset.mass,
        type = event.target.dataset.type,
        created_at = event.target.dataset.created_at;

    var act = $('#file_manager .right .act'),
        monitor = $('#file_manager .right .monitor'),
        info = $('#file_manager .right .info'),
        del = $('#file_manager .right .del');

    if(type == 'image/jpeg' || type == 'image/png' || type == 'image/gif' || type == 'image/svg+xml') {
        // Image type
        var size = new Image();
        size.onload = function () {

            var data = {
                'id' : id,
                'title' : title,
                'file' : file,
                'alt' : alt,
                'mass' : LIBS_BASIC.get_size(mass),
                'created_at' : created_at,
                'width' : Math.round(size.width),
                'height' : Math.round(size.height),
                'type' : type,
                'path': path_file
            };

            //panel
            act.empty();
            monitor.empty();
            info.empty();
            del.empty();

            act.html(LIBS_IMAGE.create_panel('act', data));
            monitor.html(LIBS_IMAGE.create_panel('monitor', data));
            info.html(LIBS_IMAGE.create_panel('info', data));
            del.html(LIBS_IMAGE.create_panel('del', data));

            selected_file_id = id;

            selected_file = {
                'file': file,
                'alt': alt,
                'width': data.width,
                'height': data.height,
                'type': data.type
            };
        };
        size.src = '/static/'+path_file+'/'+file;
    } else {
        // File type
        var data = {
            'id' : id,
            'title' : title,
            'file' : file,
            'alt' : alt,
            'mass' : LIBS_BASIC.get_size(mass),
            'created_at' : created_at,
            'type' : type,
            'path': path_file
        };

        //panel
        act.empty();
        monitor.empty();
        info.empty();
        del.empty();

        act.html(LIBS_FILE.create_panel('act', data));
        monitor.html(LIBS_FILE.create_panel('monitor', data));
        info.html(LIBS_FILE.create_panel('info', data));
        del.html(LIBS_FILE.create_panel('del', data));

        selected_file_id = id;

        selected_file = {
            'file': file,
            'type': data.type
        };
    }
});

/* -> Name-Alt */
$(document).on('submit', '#filemanager_name_alt', function (event) {
    event.preventDefault();

    var button = $(event.target[3]);
    var alerts = $('#file_manager .alerts');
    var type = event.target.dataset.type;

    $.ajax({
        url: '/filemanager/namealt',
        method: 'POST',
        data: {'data': JSON.stringify($(event.target).serializeArray()), 'type': type, model: model },
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        // выполнить до отправки запроса
        beforeSend: function() {
            button.html('<div class="load loading"></div>');
        },
        // Ответ от сервера
        success: function (msg) {

            var js_select = $('#file_manager .bn [data-id='+msg.id+']');
            js_select.attr('data-title', msg.title);
            js_select.attr('data-alt', msg.alt);
            $('#file_manager .bn [data-id='+msg.id+'] .title').text(msg.title);

            $(event.target[1]).val(msg.title);
            $(event.target[2]).val(msg.alt);

            $('#file_manager .ajax_content .bn .column [data-id="'+selected_file_id+'"]').trigger('click');
            LIBS_BASIC.close_modal();
        },
        // Ошибка AJAX
        error: function (result) {

            alerts.append('<button class="alert alert-danger js_alert" role="alert">'+result.responseJSON+'</button>');
            button.html('Сохранить');
        }
    }).done(function(data){
        button.html('Сохранить');
    });
});

/* -> Удаление */
$(document).on('click', '#file_manager .js_delete', function (event) {

    var id = event.target.dataset.id;

    $('#file_manager .delete_block').addClass('active');
    $('#file_manager .delete_block .js_ok').attr('data-id', id);
    $('#file_manager .overlay_modal').css('display', 'block');
});
$(document).on('click', '#file_manager .delete_block .js_ok', function (event) {

    var button = $(event.target);
    var alerts = $('#file_manager .alerts');

    $.ajax({
        url: '/filemanager/remove',
        method: 'POST',
        data: { model: model, 'id': event.target.dataset.id, path: path_file },
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        // выполнить до отправки запроса
        beforeSend: function() {
            button.html('<div class="load loading"></div>');
        },
        // Ответ от сервера
        success: function (msg) {

            $('#file_manager .right .act').html(' --- ');
            $('#file_manager .right .monitor').html(' --- ');
            $('#file_manager .right .info').html(' --- ');
            $('#file_manager .right .del').html(' --- ');
            $('#file_manager #filemanager_search input').val('');

            $('#file_manager .bn [data-id='+event.target.dataset.id+']').closest('.bn').remove();
            LIBS_BASIC.close_modal();
        },
        // Ошибка AJAX
        error: function (result) {

            alerts.append('<button class="alert alert-danger js_alert" role="alert">'+result.responseJSON+'</button>');
            button.html('Да');
        }
    }).done(function(data){
        button.html('Да');
    });
});
$(document).on('click', '#file_manager .delete_block .js_no', function (event) {

    $('#file_manager .delete_block .js_ok').removeAttr('data-id');
    LIBS_BASIC.close_modal();
});

// Сохранения crop
$(document).on('click', '.js_cropend', function () {
    cropper.save(model);
});

// Paginator scroll версия
$('#file_manager .ajax_content').on('scroll', function () {

    var $this = $(this)[0],
        top = $this.scrollTop,
        height = $this.scrollHeight - $this.clientHeight,
        content = $('#file_manager .ajax_content .flex');
        
    if(height - top === 0) {
        var countPage = $('#id-paginate').attr("data-last-page");
        if(!file_inProgress && file_count<=countPage ) {
            $.ajax({
                url: '/filemanager/load',
                method: 'POST',
                data: { model: model, page: file_count, path: path_file, insert_page: insert_page, external_id: external_id },
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                // Ответ от сервера
                success: function (msg) {
                    content.append(msg.view);
                },
                // Ошибка AJAX
                error: function (result) {
                    console.log(result);
                }
                // сразу после выполнения запроса
            }).done(function (data) {
                file_inProgress = false;
                file_count++;
            });
        }
    }
});
// Paginator button версия
$(document).on('click', '#file_manager .ajax_content .pagination .page-link', function () {

    var item = $(this).text(),
        content = $('#file_manager .ajax_content .flex');
    file_count = Number(item) +1;

    $('#file_manager .ajax_content .bn').remove();

    var countPage = $('#id-paginate').attr("data-last-page");
    if(!file_inProgress && file_count_paginate<=countPage ) {
        $.ajax({
            url: '/filemanager/load',
            method: 'POST',
            data: { model: model, 'page': item, path: path_file, insert_page: insert_page, external_id: external_id },
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            // Ответ от сервера
            success: function (msg) {
                content.append(msg.view);
                set_paginator(msg.page, msg.total, item);
            },
            // Ошибка AJAX
            error: function (result) {
                console.log(result);
            }
            // сразу после выполнения запроса
        }).done(function (data) {
            file_inProgress = false;
        });
    }
});

/* Поиск */
$(document).on('submit', '#filemanager_search', function (event) {
    event.preventDefault();

    var button = $(event.target[1]);
    var alerts = $('#file_manager .alerts');
    var content = $('#file_manager .ajax_content .flex');

    $.ajax({
        url: '/filemanager/search',
        method: 'POST',
        data: { data: JSON.stringify($(event.target).serializeArray()), model: model, conds: conds, path: path_file },
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        // выполнить до отправки запроса
        beforeSend: function() {
            button.html('<div class="load loading"></div>');
        },
        // Ответ от сервера
        success: function (html) {

            $('#file_manager .ajax_content .flex').empty();
            content.html(html);
        },
        // Ошибка AJAX
        error: function (result) {

            alerts.append('<button class="alert alert-danger js_alert" role="alert">'+result.responseJSON+'</button>');
            button.html('Suche!');
        }
    }).done(function(data){
        button.html('Suche!');
    });
});

// Отправка файлов по почте
$(document).on('click', '.js_sendmail', function(e) {
    var ids = [];
    var email = $(e.target).closest('.panel-body').find('input').val();
 
    $('input[name="picked_items"]:checked').each(function(k, v) {
        ids.push(v.dataset.id);
    });

    $.ajax({
        url: '/filemanager/sendmail',
        method: 'POST',
        data: { ids: ids, email: email },
        beforeSend: function() {
            $(e.target).html('<span></span>');
            $(e.target).attr('disabled', 'disabled');
        },
        success: function(msg){
            if(msg == 'ok') {
                $(e.target).html('<i class="fas fa-check"></i>&nbsp; Eingereicht');
                $(e.target).removeAttr('disabled');
            }
        },
        error: function(result){
            console.log(result);
            $(e.target).text('Senden');
            $(e.target).removeAttr('disabled');
        }
    });
});

/* Установить картину в сайт */
$(document).on('click', '#file_manager .js_file_set', function (event) {
    if(insert_page == 'id') {
        $.ajax({
            url: '/filemanager/getcount',
            method: 'POST',
            data: { model: model, conds: conds, insert_page: insert_page, external_id: external_id },
            success: function(msg){
                $('#filemanager_insert_info_'+basic_id).html('<div class="alert alert-info" role="alert">'+msg.count+'</div>');
                $('#filemanager_insert_pick_'+basic_id).val(msg.ids);
                
                $('#file_manager').modal('hide');
            }
        });
    } if(insert_page == 'file') { 
        var domain;
        if(location.port != '') {
            domain = location.protocol+'//'+location.hostname+':'+location.port;
        } else {
            domain = location.protocol+'//'+location.hostname;
        }
        
        var file = domain+'/static/'+path_file+'/'+selected_file.file.replace(/\?id\=.*/gi, '');
        
        $('#filemanager_insert_info_'+basic_id).html('<img src="'+file+'">');
        $('#filemanager_insert_pick_'+basic_id).val('/static/'+path_file+'/'+selected_file.file.replace(/\?id\=.*/gi, ''));

        $('#file_manager').modal('hide');
    } else {
        if(Object.keys(selected_file).length != 0) {
            if(ckeditor != 'insert_page') {
                var domain;
                if(location.port != '') {
                    domain = location.protocol+'//'+location.hostname+':'+location.port;
                } else {
                    domain = location.protocol+'//'+location.hostname;
                }

                var file = domain+'/static/'+path_file+'/'+selected_file.file.replace(/\?id\=.*/gi, '');

                if(selected_file.type == 'image/jpeg' || selected_file.type == 'image/png' || selected_file.type == 'image/gif' || selected_file.type == 'image/svg+xml') {

                    ckeditor.insertHtml('<p><img src="'+file+'" alt="'+selected_file.alt+'" style="width: '+selected_file.width+'px; height: '+selected_file.height+'px;"></p>');
                } else if(selected_file.type == 'video/mp4') {

                    ckeditor.insertHtml('<p><video contenteditable="false" controls="controls">'+
                        '<source src="'+file+'" type=\''+selected_file.type+'; codecs="avc1.42E01E, mp4a.40.2"\'>'+
                        'Video tag not supported. Download the video <a href="'+file+'">here</a>'+
                        '</video></p>'
                    );
                } else if(selected_file.type == 'application/pdf') {

                    ckeditor.insertHtml('<p><a href="'+file+'" class="ckeditor_insert_file" target="_blank"><span class="pdf">&nbsp</span></a></p>');
                } else if(selected_file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {

                    ckeditor.insertHtml('<p><a href="'+file+'" class="ckeditor_insert_file" target="_blank"><span class="docx">&nbsp</span></a></p>');
                } else if(selected_file.type == 'text/plain') {

                    ckeditor.insertHtml('<p><a href="'+file+'" class="ckeditor_insert_file" target="_blank"><span class="txt">&nbsp</span></a></p>');
                } else if(selected_file.type == 'application/msword') {

                    ckeditor.insertHtml('<p><a href="'+file+'" class="ckeditor_insert_file" target="_blank"><span class="doc">&nbsp</span></a></p>');
                }
            }

            $('#file_manager').modal('hide');
        } else {
            $('#file_manager .alerts').append('<button class="alert alert-danger js_alert" role="alert">Sie haben kein Bild ausgewählt!</button>');
        }
    }
});

/* Закрытие внутренных модалов и чистка */
$(document).on('click', '#file_manager .overlay_modal', function () { 
    LIBS_BASIC.clear();
    cropper.destroy();
});