export function create_panel(act, data) {

    if(act == 'act') {

        return '<div class="file_title">Handlung</div>' +
            '<div>' +
            '<a class="btn btn-primary" href="/static/'+data.path+'/'+data.file+'" target="_blank">Offen</a>'+
            '<button class="btn btn-default js_replace" data-id="'+data.id+'">Ersetzen</button>' +
            '</div>';
    } else if(act == 'monitor') {

        return '<div class="file_monitor">' +
            '<div class="file_title">Monitor</div>' +
            '<div>' +
            '<div class="param"><b>Format:</b> '+typeFile_text(data.type)+'</div>' +
            '<div class="param"><b>Die Größe:</b> <span class="js_monitor_s">'+data.mass+'</span></div>' +
            '<div class="param"><b>Datum:</b> '+data.created_at+'</div>' +
            '</div>' +
            '</div>';
    } else if(act == 'info') {

        return '<div class="file_altblock">' +
            '<div class="file_title">Die Info</div>' +
            '<form id="filemanager_name_alt" data-type="file">' +
                '<div class="col-sm-12">' +
                    '<div class="form-group">' +
                        '<div class="form-line">' +
                            '<input type="hidden" name="id" value="'+data.id+'">' +
                            '<input type="text" class="form-control" name="name" placeholder="Üerschrift..." value="'+data.title+'">' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<button type="submit" class="btn btn-primary">Speichern</button>' +
            '</form>' +
            '</div>';
    } else if(act == 'del') {

        return '<div class="file_title">Eine Datei löschen</div>' +
            '<button class="btn btn-danger js_delete" data-id="'+data.id+'">Löschen</button>';
    }
}

function typeFile_text($text) {

    if($text == 'application/pdf') {
        return 'pdf';
    } else if($text == 'application/msword') {
        return 'doc';
    } else if($text == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        return 'docx';
    } else if($text == 'text/plain') {
        return 'txt';
    } else if($text == 'video/mp4') {
        return 'mp4';
    }
}