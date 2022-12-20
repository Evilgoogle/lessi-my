$('.datepicker').datetimepicker({
    format: 'DD.MM.YYYY'
});

$('.js_type').click(function(e) {
    var auftragnr = e.target.dataset.auftragnr;
    var type = e.target.dataset.type;
    var button = $(e.target).closest('.action').find('.loader');
    
    var active = 1;
    if($(e.target).hasClass('active')) {
        $(e.target).removeClass('active');
        $(e.target).find('input').prop('checked', false);
        
        if(type == 'vip') {
            $(e.target).closest('.action').find('label[data-type="app"]').removeClass('active');
            $(e.target).closest('.action').find('label[data-type="app"]').find('input').prop('checked', false);
        }
        
        active = 0;
    } else {
        $(e.target).addClass('active');
        $(e.target).find('input').prop('checked', true);
        
        if(type == 'vip') {
            $(e.target).closest('.action').find('label[data-type="app"]').addClass('active');
            $(e.target).closest('.action').find('label[data-type="app"]').find('input').prop('checked', true);
        }
        
        active = 1;
    }
    
    $.ajax({
        url: '/checker/termin/js/type',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'attach_id': auftragnr, 'type': type, 'active': active },
        beforeSend: function() {
            button.html('<span class="load"></span>');
            $(e.target).closest('.action').find('input').attr('disabled', 'disabled');
        },
        success: function(msg) {
            button.html('<i class="fas fa-check"></i>');
            $(e.target).closest('.action').find('input').removeAttr('disabled');
        },
        error: function (data) {
            button.html('<i class="fas fa-check"></i>');
            $(e.target).closest('.action').find('input').removeAttr('disabled');
            console.log(data);
        }
    });
});

$('.js_type_contact').change(function (e) {
    var type = $(e.target).val();
    var auftragnr = e.target.dataset.auftragnr;
    var button = $(e.target).closest('.controller').find('.loader');
    
    $.ajax({
        url: '/checker/termin/js/type-contact',
        type: 'POST',
        data: { 'auftragnr': auftragnr, 'type': type },
        beforeSend: function() {
            button.html('<span class="load"></span>');
        },
        success: function(msg) {
            button.html('<i class="fas fa-check"></i>');
        },
        error: function (data) {
            button.html('<i class="fas fa-check"></i>');
            console.log(data);
        }
    });
});