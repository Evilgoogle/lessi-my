$('.js_type').click(function() {
    var type = $(this).data('type');
    
    if(type == 'start') {
        
        $('.top_block').removeClass('active');
    } else if(type == 'anufren') {
        
        $('.top_block').removeClass('active');
        $('.block_phone').addClass('active');
    } else if(type == 'mail') {
        
        $('.top_block').removeClass('active');
        $('.block_email').addClass('active');
    }
});

$(document).on('click', '.js_call', function(e) {
    var lead_id = e.target.dataset.lead_id;
    
    var agent = e.target.dataset.agent;
    var to = e.target.dataset.to;

    Swal.fire({
        title: 'Anruf?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1ab394',
        cancelButtonColor: '#f8ac59',
        confirmButtonText: 'Ja',
        cancelButtonText: 'Nein'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/leads-auto/called',
                    type: 'POST',
                    data: { 'lead_id': lead_id, 'agent': agent, 'to': to },
                    success: function(msg) {
                        if(msg.called) {
                            $('#status_phone').html('<div class="alert alert-success" role="alert">'+
                            '<span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>&nbsp;'+
                            '<span>completed</span>'+
                            '</div>');
                        } else {
                            $('#status_phone').html('<div class="alert alert-danger" role="alert">'+
                                '<span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>&nbsp;'+
                                '<span>not completed</span>'+
                            '</div>');
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }
    });
});

var myInterval_call;
$(document).on('click', '.js_call_test', function(e) {
    var lead_id = e.target.dataset.lead_id;
    
    var agent = e.target.dataset.agent;
    var to = e.target.dataset.to;

    Swal.fire({
        title: 'Anruf?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1ab394',
        cancelButtonColor: '#f8ac59',
        confirmButtonText: 'Ja',
        cancelButtonText: 'Nein'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/leads-auto/test/called',
                    type: 'POST',
                    data: { 'agent': agent, 'to': to },
                    success: function(msg) {
                        $('.call_wait').addClass('call_wait_active');

                        if(msg == 'ok') {
                            $('.call_wait .down .phone').text(to);
                            $('.call_wait .down').addClass('ok');
                            $('.call_wait .load').css('opacity', '0');

                            setTimeout(function () {
                                $('.call_wait .load').removeAttr('style');
                                $('.call_wait .down').removeClass('ok');
                                $('.call_wait').removeClass('call_wait_active');
                            }, 2500);

                            $('#status_phone').html('<div class="alert alert-success" role="alert">'+
                                '<span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>&nbsp;'+
                                '<span>completed</span>'+
                                '</div>');
                        } else {

                           myInterval_call = setInterval(function() {
                               call_wait(lead_id, agent, to);
                           }, 500);
                        }
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }
    });
});
var wait_score = 0;
function call_wait(lead_id, agent, to) {
    $.ajax({
        url: '/leads-auto/test/called-wait',
        type: 'POST',
        async: false,
        data: { 'lead_id': lead_id, 'agent': agent, 'to': to },
        success: function(msg) {
            if(msg == 'ok') {
                console.log(msg)
                clearInterval(myInterval_call);

                $('.call_wait .down .phone').text(to);
                $('.call_wait .down').addClass('ok');
                $('.call_wait .load').css('opacity', '0');

                setTimeout(function () {
                    $('.call_wait .load').removeAttr('style');
                    $('.call_wait .down').removeClass('ok');
                    $('.call_wait').removeClass('call_wait_active');
                }, 2500);

                $('#status_phone').html('<div class="alert alert-success" role="alert">'+
                '<span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>&nbsp;'+
                '<span>completed</span>'+
                '</div>');
            } else {
                $('.call_wait .down .phone').text('');
            }

            wait_score++;
            if(wait_score > 40) {
                clearInterval(myInterval_call);
                wait_score = 0;
            }
        },
        error: function (data) {
            console.log(data);
        }
    });
}

$(document).on('click', '.js_email_type', function(e) {
    var type = $(e.target).text();

    $.ajax({
        url: '/leads-auto/email-type',
        type: 'POST',
        data: { 'type': type },
        success: function(msg) {
           CKEDITOR.instances.text_email.setData(msg);
        },
        error: function (data) {
            alertifyError(data);
        }
    });
});

$(document).on('click', '.js_select_email', function(e) {
    var get = $(e.target).text();
    
    $('#input_email_an').val(get);
});

$(document).on('submit', '.js_email', function(e) {
    e.preventDefault();
    
    var lead_id = e.target.dataset.lead_id;
    var email_title = $(e.target).find('input[name="email_title"]').val();
    var email_from = $(e.target).find('input[name="email_from"]').val();
    var email_to = $(e.target).find('input[name="email_to"]').val();
    var email_text = $(e.target).find('textarea[name="email_text"]').val();
    
    var button = $(e.target).find('.button_send');

    $.ajax({
        url: '/leads-auto/written',
        type: 'POST',
        data: { 'lead_id': lead_id, 'email_title': email_title, 'email_from': email_from, 'email_to': email_to, 'email_text': email_text },
        beforeSend : function() {
            button.html('<span class="loader"></span>');
            button.attr('disabled','disabled');
        },
        success: function(msg) {
            button.text('Senden');
            button.removeAttr('disabled');
            
            $('#input_email_an').val('');
               $('textarea[name="email_text"]').val('');
               CKEDITOR.instances.text_email.setData('');
            
            if(msg.written) {
                 $('#status_email').html('<div class="alert alert-success" role="alert">'+
                 '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;'+
                 '<span>completed</span>'+
                 '</div>');
             } else {
                 $('#status_email').html('<div class="alert alert-danger" role="alert">'+
                     '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;'+
                     '<span>not completed</span>'+
                 '</div>');
             }
        },
        error: function (data) {
            button.text('Senden');
            button.removeAttr('disabled');
        }
    });
});

$(document).on('submit', '.js_email_test', function(e) {
    e.preventDefault();
    
    var lead_id = e.target.dataset.lead_id;
    var email_title = $(e.target).find('input[name="email_title"]').val();
    var email_from = $(e.target).find('input[name="email_from"]').val();
    var email_to = $(e.target).find('input[name="email_to"]').val();
    var email_text = $(e.target).find('textarea[name="email_text"]').val();
    
    var button = $(e.target).find('.button_send');

    $.ajax({
        url: '/leads-auto/test/written',
        type: 'POST',
        data: { 'lead_id': lead_id, 'email_title': email_title, 'email_from': email_from, 'email_to': email_to, 'email_text': email_text },
        beforeSend : function() {
            button.html('<span class="loader"></span>');
            button.attr('disabled','disabled');
        },
        success: function(msg) {
            button.text('Senden');
            button.removeAttr('disabled');
            
            $('#input_email_an').val('');
               $('textarea[name="email_text"]').val('');
               CKEDITOR.instances.text_email.setData('');
            
            if(msg.written) {
                 $('#status_email').html('<div class="alert alert-success" role="alert">'+
                 '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;'+
                 '<span>completed</span>'+
                 '</div>');
             } else {
                 $('#status_email').html('<div class="alert alert-danger" role="alert">'+
                     '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;'+
                     '<span>not completed</span>'+
                 '</div>');
             }
        },
        error: function (data) {
            button.text('Senden');
            button.removeAttr('disabled');
        }
    });
});

$(document).on('click', '.js_reload_history', function(e) { 
    var lead_id = e.target.dataset.lead_id;
   
    $.ajax({
        url: '/leads-auto/call-history',
        type: 'POST',
        data: { 'lead_id': lead_id },
        beforeSend : function() {
            $(e.target).html('<span class="loader"></span>');
            $(e.target).attr('disabled','disabled');
        },
        success: function(msg) {
            $(e.target).html('<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>');
            $(e.target).removeAttr('disabled');
            
            $('#call_history').html(msg);
        },
        error: function (data) {
            $(e.target).html('<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>');
            $(e.target).removeAttr('disabled');
        }
    });
});

$(document).on('click', '.js_reload_history_test', function(e) { 
    var lead_id = e.target.dataset.lead_id;
   
    $.ajax({
        url: '/leads-auto/test/call-history',
        type: 'POST',
        data: { 'lead_id': lead_id },
        beforeSend : function() {
            $(e.target).html('<span class="loader"></span>');
            $(e.target).attr('disabled','disabled');
        },
        success: function(msg) {
            console.log(msg);
            $(e.target).html('<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>');
            $(e.target).removeAttr('disabled');
            
            $('#call_history').html(msg);
        },
        error: function (data) {
            $(e.target).html('<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>');
            $(e.target).removeAttr('disabled');
        }
    });
});

$('.datepicker').datetimepicker({
    format: 'DD.MM.YYYY'
});

// follow up
$(document).on('click', '.js_follow_up', function(e) {
    $('.insert_lead').html('<input type="hidden" name="lead_id" value="'+e.target.dataset.lead_id+'">');
    
    $('#follow_up_modal').modal('show');
});

$(document).on('click', '.js_follow_day_select', function(e) {
    $('#follow_up_modal').find('.datepicker').val(e.target.dataset.date);
});

$(document).on('submit', '#follow_up_form', function(e) {
    e.preventDefault();
    var lead_id = $(e.target).find('input[name="lead_id"]').val();
    var date = $(e.target).find('input[name="date"]').val();
    
    $.ajax({
        url: '/leads-auto/follow-up-insert',
        type: 'POST',
        data: { 'lead_id': lead_id, 'date': date },
        beforeSend : function() {
            $(e.target).find('button[type="submit"]').attr('disabled','disabled');
        },
        success: function(msg) {
            if(msg == 'ok') {
                $(e.target).find('button[type="submit"]').removeAttr('disabled');
                location.reload();
            }
        },
        error: function (data) {
            $(e.target).find('button[type="submit"]').removeAttr('disabled');
        }
    });
});

$(document).on('submit', '#follow_up_form_test', function(e) {
    e.preventDefault();
    
    var lead_id = $(e.target).find('input[name="lead_id"]').val();
    var date = $(e.target).find('input[name="date"]').val();

    $.ajax({
        url: '/leads-auto/test/follow-up-insert',
        type: 'POST',
        data: { 'lead_id': lead_id, 'date': date },
        beforeSend : function() {
            $(e.target).find('button[type="submit"]').attr('disabled','disabled');
        },
        success: function(msg) {
            if(msg == 'ok') {
                $(e.target).find('button[type="submit"]').removeAttr('disabled');
                location.reload();
            }
        },
        error: function (data) {
            $(e.target).find('button[type="submit"]').removeAttr('disabled');
        }
    });
});

// complete
$(document).on('click', '.js_commplete', function(e) {
    var lead_id = e.target.dataset.lead_id;
    
    $.ajax({
        url: '/leads-auto/complete',
        type: 'POST',
        data: { 'lead_id': lead_id },
        success: function(msg) {
            if(msg.complete == 'completed') {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Lead erfolgreich abgeschlossen',
                    showConfirmButton: false,
                    timer: 2200
                 });

                setTimeout(function() {
                  location.reload();
                }, 2500);
                
                $('.panel-heading').removeClass('red');
                $('.panel-footer').removeClass('red');
                
                $('.panel-footer .panel-body strong').text('completed');
            }
        }
    });
});

$(document).on('click', '.js_commplete_test', function(e) {
    var lead_id = e.target.dataset.lead_id;
    
    $.ajax({
        url: '/leads-auto/test/complete',
        type: 'POST',
        data: { 'lead_id': lead_id },
        success: function(msg) {
            if(msg.complete == 'completed') {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Lead erfolgreich abgeschlossen',
                    showConfirmButton: false,
                    timer: 2200
                 });
                
                $('.panel-heading').removeClass('red');
                $('.panel-footer').removeClass('red');
                
                $('.panel-footer .panel-body strong').text('completed');
            }
        }
    });
});

// Timer
function getTimeRemaining(endtime) {
  var t = Date.parse(endtime) - Date.parse(new Date());
  var seconds = Math.floor((t / 1000) % 60);
  var minutes = Math.floor((t / 1000 / 60) % 60);
  var hours = Math.floor((t / (1000 * 60 * 60)) % 24);

  return {
    't': t,
    'h': hours,
    'i': minutes,
    's': seconds
  };
}
 
function initializeClock(endtime) {
  var clock = $('#timer');
  var hoursSpan = clock.find('.hours');
  var minutesSpan = clock.find('.minutes');
  var secondsSpan = clock.find('.seconds');
 
  function updateClock() {
    var t = getTimeRemaining(endtime);

    hoursSpan.text(('0' + t.h).slice(-2));
    minutesSpan.text(('0' + t.i).slice(-2));
    secondsSpan.text(('0' + t.s).slice(-2));
 
    if (t.t <= 0) {      
      clearInterval(timeinterval);

      setTimeout(function() {
          location.reload();
        }, 2000);
    }
  }
 
  updateClock();
  var timeinterval = setInterval(updateClock, 1000);
}

Date.prototype.addHours = function(h) {
  this.setTime(this.getTime() + (h*60*60*1000));
  return this;
}
Date.prototype.addMinutes = function(m) {
  this.setTime(this.getTime() + m*60000);
  return this;
}
Date.prototype.addSecondes = function(s) {
  this.setTime(this.getTime() + s*1000);
  return this;
}

var time = $('#timer').data('time');
if(time !== undefined) {
    var deadline = new Date().addHours(time.h).addMinutes(time.i).addSecondes(time.s);
    initializeClock(deadline);
}