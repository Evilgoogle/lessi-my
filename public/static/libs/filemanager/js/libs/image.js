import { close_modal, update_elements } from './basic.js';

export function panel(model, path_file, cropper) {
    // Просмотр картины
    $(document).on('click', '#file_manager .right .act .js_view', function (event) {
        var file = event.target.dataset.file;

        $('#file_manager .view_block').addClass('active');
        $('#file_manager .overlay_modal').css('display', 'block');
        
        let view = $('#file_manager .view_block .view');
        view.html('');
        view.attr('style', 'background: url(/static/'+path_file+'/'+file+')');
    });

    // Открыть окно crop
    $(document).on('click', '#file_manager .right .act .js_crop', function (event) {
        var id = event.target.dataset.id,
            type = event.target.dataset.type,
            file = event.target.dataset.file;

        $('#file_manager .cropper_block').addClass('active');
        $('#file_manager .overlay_modal').css('display', 'block');
        $('#file_manager .cropper_block .crop .left img').attr('src','/static/'+path_file+'/'+file);

        setTimeout(function () {
            cropper.init(id, type);
        }, 200);
    });

    // Открыть окно resize
    $(document).on('click', '#file_manager .right .act .js_resize', function (event) {
        var width = event.target.dataset.width;
        var height = event.target.dataset.height;
        var file = event.target.dataset.file;
        var path = event.target.dataset.path;

        $('#file_manager .resize_block').addClass('active');
        $('#file_manager .overlay_modal').css('display', 'block');
        $('#file_manager .resize_block .resize .js_width').html(width+'px');
        $('#file_manager .resize_block .resize .js_heigth').html(height+'px');
        $('#file_manager #js_resizend [name="id"]').val($(this).data('id'));
        $('#file_manager #js_resizend [name="image"]').val(file);
        $('#file_manager #js_resizend [name="path"]').val(path);
        $('#file_manager #js_resizend [name="model"]').val(model);
    });
}

export function create_panel(act, data) {

    if(act == 'act') {

        return '<div class="file_title">Handlung</div>' +
            '<div>' +
            '<button class="btn btn-primary js_view" data-file="'+data.file+'">Aussehen</button>' +
            '<button class="btn btn-default js_crop" data-id="'+data.id+'" data-file="'+data.file+'" data-type="'+data.type+'">Trimmen</button>' +
            '<button class="btn btn-default js_resize" data-id="'+data.id+'" data-width="'+data.width+'" data-height="'+data.height+'" data-file="'+data.file+'" data-path="'+data.path+'">Größe ändern</button>' +
            '<button class="btn btn-default js_replace" data-id="'+data.id+'">Ersetzen</button>' +
            '</div>';
    } else if(act == 'monitor') {

        return '<div class="file_monitor">' +
            '<div class="file_title">Monitor</div>' +
            '<div>' +
            '<div class="param"><b>Format:</b> '+typeFile_text(data.type)+'</div>' +
            '<div class="param"><b>Breite:</b> <span class="js_monitor_w">'+data.width+'</span>px</div>' +
            '<div class="param"><b>Höhe:</b> <span class="js_monitor_h">'+data.height+'</span>px</div>' +
            '<div class="param"><b>Die Größe:</b> <span class="js_monitor_s">'+data.mass+'</span></div>' +
            '<div class="param"><b>Datum:</b> '+data.created_at+'</div>' +
            '</div>' +
            '</div>';
    } else if(act == 'info') {

        return '<div class="file_altblock">' +
            '<div class="file_title">Die Info</div>' +
            '<form id="filemanager_name_alt" data-type="image">' +
            '<div class="col-sm-12">' +
            '<div class="form-group">' +
            '<div class="form-line">' +
            '<input type="hidden" name="id" value="'+data.id+'">' +
            '<input type="text" class="form-control" name="name" placeholder="Üerschrift..." value="'+data.title+'">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-sm-12">' +
            '<div class="form-group">' +
            '<div class="form-line">' +
            '<input type="text" class="form-control" name="alt" placeholder="Alt..." value="'+data.alt+'">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<button type="submit" class="btn btn-primary">Speichern</button>' +
            '</form>' +
            '</div>';
    } else if(act == 'del') {

        return '<div class="file_title">Lösche Bild</div>' +
            '<button class="btn btn-danger js_delete" data-id="'+data.id+'">Löschen</button>';
    }
}

/* -> Cropper */
export class imageCropper
{
    constructor(path_file) {
        this.cropper = null;
        this.file_id;
        this.file_format;
        this.path_file = path_file;
        
        this.cropper_zoom = 0;
        this.cropper_move_x = 0;
        this.cropper_move_y = 0;
        this.cropper_rotate = 0;
        this.cropper_scale_x = true;
        this.cropper_scale_y = true;
    }
    
    init(id, format) {
        this.destroy();
        
        this.file_id = id;
        this.file_format = format;
        
        var image = document.getElementById('file_cropImage');

        this.cropper = new Cropper(image, {
            viewMode: 2,
            ready() {
                this.cropper.clear();
            },
            crop(event) {
                $('#file_manager .crop_monitor_w').text(Math.round(event.detail.width));
                $('#file_manager .crop_monitor_h').text(Math.round(event.detail.height));
                $('#file_manager .crop_monitor_r').text(Math.round(event.detail.rotate));
            },
        });
        
        this.control();
    }
    
    control() {
        let cropper = this.cropper;
        
        let cropper_zoom = 0;
        let cropper_move_x = 0;
        let cropper_move_y = 0;
        let cropper_rotate = 0;
        let cropper_scale_x = true;
        let cropper_scale_y = true;
        
        // frame
        $(document).on('click', '.cropper_frame_set', function () {
            if(cropper !== null) {
                cropper.crop();
            }
        });
        $(document).on('click', '.cropper_frame_remove', function () {
            if(cropper !== null) {
                cropper.clear();
            }
        });
        // zoom
        $(document).on('click', '.cropper_zoom_plus', function () {
            if(cropper !== null) {
                cropper.zoom(cropper_zoom + 0.1);
            }
        });
        $(document).on('click', '.cropper_zoom_minus', function () {
            if(cropper !== null) {
                cropper.zoom(cropper_zoom - 0.1);
            }
        });
        // arrow
        $(document).on('click', '.cropper_arr_left', function () {
            if(cropper !== null) {
                cropper.move(cropper_move_x + 4, cropper_move_y);
            }
        });
        $(document).on('click', '.cropper_arr_up', function () {
            if(cropper !== null) {
                cropper.move(cropper_move_x, cropper_move_y + 4);
            }
        });
        $(document).on('click', '.cropper_arr_right', function () {
            if(cropper !== null) {
                cropper.move(cropper_move_x - 4, cropper_move_y);
            }
        });
        $(document).on('click', '.cropper_arr_down', function () {
            if(cropper !== null) {
                cropper.move(cropper_move_x, cropper_move_y - 4);
            }
        });
        // rotate
        $(document).on('click', '.cropper_rotate_undo', function () {
            if(cropper !== null) {
                cropper.rotate(cropper_rotate + 45);
            }
        });
        $(document).on('click', '.cropper_rotate_next', function () {
            if(cropper !== null) {
                cropper.rotate(cropper_rotate - 45);
            }
        });
        // scale
        $(document).on('click', '.cropper_arrows_h', function () {
            if(cropper !== null) {
                if(cropper_scale_x === true) {
                    cropper_scale_x = false;
                    cropper.scale(-1, 1);
                } else if(cropper_scale_x === false) {
                    cropper_scale_x = true;
                    cropper.scale(1, 1);
                }

            }
        });
        $(document).on('click', '.cropper_arrows_v', function () {
            if(cropper !== null) {
                if(cropper_scale_y === true) {
                    cropper_scale_y = false;
                    cropper.scale(1, -1);
                } else if(cropper_scale_y === false) {
                    cropper_scale_y = true;
                    cropper.scale(1, 1);
                }
            }
        });
    }
    
    save(model) {
        var image = this.cropper.getCroppedCanvas().toDataURL(this.file_format, 1);
        var button = $('#file_manager .js_cropend');
        var alerts = $('#file_manager .alerts');
        var file_id = this.file_id;
        var path_file = this.path_file;

        $.ajax({
            url: '/filemanager/crop',
            method: 'POST',
            data: { model: model, 'id': file_id, 'image': image, path: path_file },
            // выполнить до отправки запроса
            beforeSend: function() {
                button.html('<div class="load loading"></div>');
            },
            // Ответ от сервера
            success: function (msg) {

                update_elements(msg, msg.path, msg.rand);
                $('#file_manager .ajax_content .bn .column [data-id="'+ file_id +'"]').trigger('click');
                close_modal();
            },
            // Ошибка AJAX
            error: function (result) {

                alerts.append('<button class="alert alert-danger js_alert" role="alert">'+result.responseJSON+'</button>');
                button.html('Сохранить');
            }
        }).done(function(data){
            button.html('Сохранить');
        });
    }
    
    destroy() {
        if(this.cropper !== null) {
            this.cropper.destroy();
        }
    }
}

function typeFile_text($text) {

    if($text == 'image/jpeg') {
        return 'jpeg';
    } else if($text == 'image/png') {
        return 'png';
    } else if($text == 'image/gif') {
        return 'gif';
    } else if($text == 'image/svg+xml') {
        return 'svg';
    }
}

/* -> Resize */
var js_resizend = $("#js_resizend");
js_resizend.submit(function(e){
    e.preventDefault();

    var button = $('#file_manager #js_resizend button');
    var alerts = $('#file_manager .alerts');

    $.ajax({
        url: '/filemanager/resize',
        method: 'POST',
        data: {'data': JSON.stringify(js_resizend.serializeArray())},
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        // выполнить до отправки запроса
        beforeSend: function() {
            button.html('<div class="load loading"></div>');
        },
        // Ответ от сервера
        success: function (msg) {
            update_elements(msg, msg.path, msg.rand);
            $('#file_manager .ajax_content .bn .column [data-id="'+msg.id+'"]').trigger('click');
            close_modal();
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