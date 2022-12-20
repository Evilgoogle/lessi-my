CKEDITOR.editorConfig = function( config ) {
    config.language = 'de';
    CKEDITOR.config.allowedContent = true;
    config.toolbar = [
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source'] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
        { name: 'links', items: [ 'Link', 'Unlink' ] },
        { name: 'insert', items: [ 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'lessi_filemanager' ] },
        '/',
        { name: 'styles', items: [ 'Styles', 'Format', 'FontSize' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
    ];

    config.extraPlugins = 'lessi_filemanager';
    config.youtube_responsive = true;
    config.colorButton_colors = '000000,FFFFFF,004c93,e54d14,f6f6f6,b8b8b8';
    config.contentsCss = ["/static/css/faq/text_editor.css"];
    config.enterMode = CKEDITOR.ENTER_P;
    config.height = 300;
    
    config.model = '\\App\\Models\\Faq\\Filemanager';
    config.path = 'files/faq';
};