
var lDEopts2 =
	{
	    'useGrouping': true,
	    'maximumFractionDigits': 2,
	    'style': 'currency',
	    'currency': 'EUR',

	}
var positions = {
    summ: 0.0,
    add: function () {
	var data = {
	    'type': $('[name=newpos_type]').val(),
	    'title': $('[name=newpos_title]').val(),
	    'price_netto': $('[name=newpos_price_netto]').val(),
	    'price_brutto': $('[name=newpos_price_brutto]').val(),
	    'class': $('[name=newpos_class]').val(),
	    'zclass': $('[name=newpos_zclass]').val(),
	    'lclass': $('[name=newpos_lclass]').val(),
	    'auftrag_id': $('[name=auftrag_id]').val(),
	}
	$.post('/dispo/js/create_custom_pos', data, function (r) {
	    $('[data-id=newpos] input').val('');
	    auftrag_pos.draw()
	}, 'json')
    },
    del: function (id) {
	let auftrag_id = $('[name=auftrag_id]').val()
	$.post(`/checker/${auftrag_id}/js/deletePosition`, {'position': id}, function (r) {
	    positions.draw()
	})
    },
    draw: function () {
	let auftrag_id = $('[name=auftrag_id]').val()


	$.get(`/checker/${auftrag_id}/js/getPositions`, function (r) {



	    $('#auftragpos').html('')
	    let allSum = $.map(r, item => {
	
		$tr = $('<tr/>', {
		    'data-id': item.id,
		    'data-type': item.type,
		    'class': (item.original_deleted ? 'alert-danger' : '')
		})



		$tr.append($('<td/>', {'text': item.type_text}))
		$tr.append($('<td/>', {'html': item.title}))
		$tr.append($('<td/>', {'text': parseFloat(item.betrag).toLocaleString('de', lDEopts2)}))


		$tr.append($('<td/>').append(
			$('<div/>', {'class': 'btn btn-warning btn-sm', 'text': 'Löschen'})
			.on('click', () => {
			    positions.del(item.id)
			})
			))
		$tr.appendTo('#auftragpos')
		if (!item.original_deleted) {
		    return parseFloat(item.betrag)
		}
		return 0;
	    })
		    .reduce((pv, cv) => {
			return pv + (parseFloat(cv) || 0);
		    })
	    $('#auftrag_betrag').html(parseFloat(allSum).toLocaleString('de', lDEopts2))
	    $('#auftragpos select').selectpicker();
//	    toggle_pos_auf()
//	    draw_preis();
	}, 'json')
    }
}