maruti = {	
	
	// === Initialize the fullCalendar and external draggable events === //
	init: function(calendar_event_url) {	

		var pid = pid;

		var calendar = $('#fullcalendar').fullCalendar({
			lang:'zh-cn',
			editable:false,
			header: {
				left: 'prev,next today',
				center: 'title',
				right: ''
			},
			events: function(start, end, timezone, callback) {
		        $.post(calendar_event_url, 
		        	{start: start.unix(),end: end.unix()},
		        
		            function(result) {
		            	if(!result) return;
			              	var events = [];

			                for (var i in result) {
			                	//alert(result[i].desc);
			                	events.push(
			                		{
			                			title:'<p>成本价：'+result[i].prime_price+'元</p><p>零售价：'+result[i].retail_price+'元</p><p>原价：'+result[i].cost_price+'</p><p>库存：'+result[i].stock+'</p><p>说明：'+result[i].desc+'</p>',
			               				start:result[i].date,
			                		}
			                	);
			                }
			                callback(events);
		            }
		        );
		    },
			/*events: [
				{
					title: '<p>价格：333元</p><p>库存:323</p><p>说明：儿童价</p>',
					start: new Date(2014, 3, 1)
				},
			],
			*/
			eventRender: function(event, element) {
		        element.html(event.title);
		    },
		    dayClick: function(date) {
		    	var curr = date.format('YYYY-MM-DD');

		    	$('#priceform_date_text').html(curr);
		    	$('#priceform_date').val(curr);

		    	maruti.showSingleForm();
    		},
    		eventClick:function(evt) {
		    	var curr = evt.start.format('YYYY-MM-DD');

		    	$('#priceform_date_text').html(curr);
		    	$('#priceform_date').val(curr);
		        
		        $.post(calendar_event_url, 
		        	{start: evt.start.unix()},
		        
		            function(result) {
		            	if(!result) {
		            		BootstrapDialog.alert('加载错误，请重试');
		            	};

		            	var part = result[0];
		            	for(var i in part) {
		            		if($('#priceform_'+i)) {
		            			$('#priceform_'+i).val(part[i]);
		            		}
		            	}
		            }
		        );

		    	maruti.showSingleForm();

    		},

		});
	},
	
	// === Adds an event if name is provided === //
	add_event: function(){
		if($('#event-name').val() != '') {
			var event_name = $('#event-name').val();
			$('#external-events .panel-content').append('<div class="external-event label label-inverse">'+event_name+'</div>');
			this.external_events();
			$('#modal-add-event').modal('hide');
			$('#event-name').val('');
		} else {
			this.show_error();
		}
	},

	
	// === Initialize the draggable external events === //
	external_events: function(){
		/* initialize the external events
		-----------------------------------------------------------------*/
		$('#external-events div.external-event').each(function() {		
			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			// it doesn't need to have a start or end
			var eventObject = {
				title: $.trim($(this).text()) // use the element's text as the event title
			};
				
			// store the Event Object in the DOM element so we can get to it later
			$(this).data('eventObject', eventObject);
					
		});		
	},
	showSingleForm:function() {
		this.clearEventForm();

		$('.op_single').show();
		$('.op_batch').hide();
		$('#priceform_start_time').val('');
		$('#priceform_end_time').val('');
		$('#modal-add-event').modal();
	},
	showBatchForm:function() {
		this.clearEventForm();
		$('.op_single').hide();
		$('.op_batch').show();
		$('#priceform_date').val('');
		$('#modal-add-event').modal();
	},
    clearEventForm:function() {
    	$('#price_form').find('input[type=text],textarea').val('');
    },
	
	// === Show error if no event name is provided === //
	show_error: function(){
		$('#modal-error').remove();
		$('<div style="border-radius: 5px; top: 70px; font-size:14px; left: 50%; margin-left: -70px; position: absolute;width: 140px; background-color: #f00; text-align: center; padding: 5px; color: #ffffff;" id="modal-error">Enter event name!</div>').appendTo('#modal-add-event .modal-body');
		$('#modal-error').delay('1500').fadeOut(700,function() {
			$(this).remove();
		});
	}
	
	
};
