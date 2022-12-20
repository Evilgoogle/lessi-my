export function container(model, conds, path, insert_page, external_id) {
    // Загрузка файлов
    $(document).on('click', '#file_manager .upload_file', function () {

        $('#file_manager #upload_block').addClass('active');
        $('#file_manager .overlay_modal').css('display', 'block');
    });
    
    // Закрыть панель подсказок
    $(document).on('click', '#file_manager .js_alert', function () {
        $(this).remove();
    });
    
    // Замена файла
    $(document).on('click', '#file_manager .right .js_replace', function () {
        var id = event.target.dataset.id;

        $('#file_manager #replace_block').addClass('active');
        $('#file_manager .overlay_modal').css('display', 'block');
    });

    /* -> Обновление */
    $('#file_manager .js_reload').click(function () {

        reload(model, conds, path, insert_page, external_id);
    });
}

// Чистка
export function clear() {
    $('#file_manager #upload_block').removeClass('active');
    $('#file_manager .view_block').removeClass('active');
    $('#file_manager .cropper_block').removeClass('active');
    $('#file_manager .resize_block').removeClass('active');
    $('#file_manager .delete_block').removeClass('active');
    $('#file_manager .replace_block').removeClass('active');
    $('#file_manager .overlay_modal').css('display', 'none');
}
// Обновление
export function reload(model, conds, path, insert_page, external_id) {
    var content = $('#file_manager .ajax_content .flex');
    var load = $('#file_manager .ajax_content .load');

    $('#file_manager .right .act').html(' --- ');
    $('#file_manager .right .monitor').html(' --- ');
    $('#file_manager .right .info').html(' --- ');
    $('#file_manager .right .del').html(' --- ');
    $('#file_manager #filemanager_search input').val('');

    $('#file_manager .ajax_content .bn').remove();
    $('#file_manager .ajax_content .pagination').remove();
    $('#file_manager .ajax_content .load').addClass('loading');

    $.ajax({
        url: '/filemanager/load',
        method: 'POST',
        data: { model: model, conds: conds, path: path, insert_page: insert_page, external_id: external_id },
        // выполнить до отправки запроса
        beforeSend: function() {
            load.addClass('loading');
        },
        // Ответ от сервера
        success: function(msg){
            content.prepend(msg.view);
            set_paginator(msg.page, msg.total);
        },
        // Ошибка AJAX
        error: function(result){
            console.log(result);
        }
        // сразу после выполнения запроса
    }).done(function(data){
        load.removeClass('loading');
    });
}

// Установка пагинатора
export function set_paginator(page, total, active) {

    if(active === undefined) {
        active = 1;
    }
    var paginate_block = $('#file_manager .ajax_content .paginate_block');

    $.ajax({
        url: '/filemanager/paginator',
        method: 'POST',
        data: { page: page, total: total, active: active },
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        // Ответ от сервера
        success: function (msg) {
            paginate_block.html(msg);
        },
        // Ошибка AJAX
        error: function (result) {
            console.log(result);
        }
    });
}

export function update_elements(msg, path_image, rand) {
    var js_select = $('#file_manager .bn [data-id='+msg.id+']');

    // Select bn
    $('#file_manager .ajax_content .bn [data-id='+msg.id+'] .image').attr('style', 'background-image: url(/static/'+path_image+msg.file+'?id='+rand+')');
    js_select.attr('data-file', msg.file+'?id='+rand);
    js_select.attr('data-type', msg.type);
    js_select.attr('data-mass', msg.size);
    js_select.find('.image').empty();
    
    var arr = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain',
        'video/mp4', 'application/msword'];
    
    if(inArray(msg.type, arr)) {
        if (msg.type == 'application/pdf') {
            
            js_select.find('.image').html('<span class="pdf"></span>');
        } else if (msg.type == 'application/msword') {
            
            js_select.find('.image').html('<span class="doc"></span>');
        } else if (msg.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            
            js_select.find('.image').html('<span class="docx"></span>');
        } else if(msg.type == 'text/plain') {
            
            js_select.find('.image').html('<span class="txt"></span>');
        } else if (msg.type == 'video/mp4') {
            
            js_select.find('.image').html('<span class="mp4"></span>');
        }
    }

    // data
    var resize = $('#file_manager .js_resize');
    resize.attr('data-width', Math.round(msg.width));
    resize.attr('data-height', Math.round(msg.height));
    resize.attr('data-file', msg.file+'?id='+rand);

    // Показатели
    $('#file_manager .file_monitor .js_monitor_w').text(Math.round(msg.width));
    $('#file_manager .file_monitor .js_monitor_h').text(Math.round(msg.height));
    $('#file_manager .file_monitor .js_monitor_s').text(get_size(msg.size));
}

// Закрывает модал
export function close_modal() {
    $('#file_manager #upload_block').removeClass('active');
    $('#file_manager .cropper_block').removeClass('active');
    $('#file_manager .resize_block').removeClass('active');
    $('#file_manager .delete_block').removeClass('active');
    $('#file_manager .replace_block').removeClass('active');
    $('#file_manager .overlay_modal').css('display', 'none');
}

export function get_size(size) {

    if(String(size).length > 6) {

        var get = (size/1024)/1024;
        return get.toFixed(1)+'Мб';
    } else if(String(size).length <= 6) {

        var get = size/1024;
        return get.toFixed(1)+'Кб';
    }
}

export function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}