$(document).ready(function () {
    $('#yesRepeat').click(function () {
        $('#repeatFields').slideDown();
    });
    $('#noRepeat').click(function () {
        $('#repeatFields').slideUp();
    });
    $('#typeContext').click(function () {
	$('#conInfo').slideDown();
	$('#catInfo').slideUp();
	$('#eventTitle').prop('required', false);
    });
    $('#typeEvent').click(function () {
	$('#conInfo').slideUp();
	$('#catInfo').slideDown();
	$('#eventTitle').prop('required', true);		
    });
    $('#calEventDialog').dialog({
          resizable: false,
          autoOpen: false,
          width: '50%'
    });
    $('#calEventDialog').dialog('open');
    $('#calEventDialog').dialog('close');

    function reset(){
          $('#addForm')[0].reset();
	  $('#conInfo').slideUp();
	  $('#catInfo').slideUp();	
          $("input[type='radio'][name='eventType']").removeAttr('disabled');
	  $("input[type='radio'][name='eventRepeat']").filter("[value='false']").prop('checked', true);
	  $('#repeatFields').slideUp();
    }
    var repeatEnd = "";
    var category = cat;
    var context = con;

    $('#eventRepeatEnd').removeClass('hasDatePicker').datepicker({
        dateFormat: 'mm-dd-yy',
        inline: true,
        onSelect: function () {
            repeatEnd = $("#eventRepeatEnd").datepicker('getDate');
            repeatEnd = repeatEnd.getTime() / 1000;
        }
    });
    $myCalendar = $('#myCalendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        theme: true,
        selectable: true,
        selectHelper: true,
//        defaultView: 'agendaWeek',
        select: function (start, end, allDay) {
            var titlefield = $('#eventTitle');
            var desc = $('#eventDesc');
            var loc = $('#eventLocation');
            var url = $('#eventURL');
            var cat = $('#eventCategory');
            var type = "event";
            var color = "";
            var context = $('#contextOption');
            var repeat = "false";
            var repeatLength = $('#eventRepeatLength');
            var repeatFreq = $('#eventRepeatFreq');
            var id = $.now()%2147483647;
            var emailReminder = "";
            var popupReminder = "";
	    var title = "";

            $('#calEventDialog').dialog({
                resizable: false,
                autoOpen: false,
                title: 'Add Time Block',
                width: '50%',
                buttons: {
                    Save: function () {
			type = $("input[type='radio'][name='eventType']:checked").val();
			repeat = $("input[type='radio'][name='eventRepeat']:checked").val();
			if (type=='event') {
                            $.ajax({
				url:'../fullcalendar/calendar/getColor.php', 
				type:'post',
				data: { type:'category', id:cat.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				color = "#"+ result;
				},
			    });
			    title=titlefield.val();
			    emailReminder = $('#eventEmailReminder').val();
			    popupReminder = $('#eventPopupReminder').val();
			} else {
                           $.ajax({
				url:'../fullcalendar/calendar/getColor.php', 
				type:'post',
				data: { type:'context', id:context.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				color = "#"+ result;
				},
			    });
			  $.ajax({
				url:'../fullcalendar/calendar/getContextName.php', 
				type:'post',
				data: { id:context.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				title = "Context: " + result;
				},
			    });
			    emailReminder = $('#contextEmailReminder').val();
			    popupReminder = $('#contextPopupReminder').val();
			}
                        if (title !== '') {
                            $myCalendar.fullCalendar('renderEvent', {
                                    id: id,
                                    title: title,
                                    start: start,
                                    end: end,
                                    allDay: allDay,
                                    loc: loc.val(),
                                    url: url.val(),
                                    category: cat.val(),
                                    context: context.val(),
                                    type: type,
                                    color: color,
                                    repeat: repeat,
                                    repeatLength: repeatLength.val(),
                                    repeatFreq: repeatFreq.val(),
                                    repeatEnd: repeatEnd,
                                    emailReminder: emailReminder,
                                    popupReminder: popupReminder,
                                    description: desc.val(),
                                }, true // make the event "stick"
                            );
			    if (type == "event") {
			    //alert(emailReminder + "::" + popupReminder);
                            $.post("../fullcalendar/calendar/addEvent.php", {
                                    id: id,
                                    //change date to unix time
                                    start: start.getTime() / 1000,
                                    end: end.getTime() / 1000,
                                    title: title,
                                    allDay: allDay.toString(),
                                    loc: loc.val(),
                                    url: url.val(),
                                    category: cat.val(),
                                    repeat: repeat,
                                    repeatLength: repeatLength.val(),
                                    repeatFreq: repeatFreq.val(),
                                    repeatEnd: repeatEnd,
                                    emailReminder: emailReminder,
                                    popupReminder: popupReminder,
                                    description: desc.val()
                                },
                                function (result){
					}
                            );
			    } else {
				$.post("../fullcalendar/calendar/addContext.php", {
                                    id: id,
                                    //change date to unix time
                                    start: start.getTime() / 1000,
                                    end: end.getTime() / 1000,
                                    contextID: context.val(),
				    allDay: allDay.toString(),
                                    repeat: repeat,
                                    repeatLength: repeatLength.val(),
                                    repeatFreq: repeatFreq.val(),
                                    repeatEnd: repeatEnd,
                                    emailReminder: emailReminder,
                                    popupReminder: popupReminder
                                },
                                function (result){
					}
                            	);
			    }
                        }
                        $myCalendar.fullCalendar('unselect');
			if(repeat=='true') {
				$myCalendar.fullCalendar('removeEvents', id);
				$myCalendar.fullCalendar('refetchEvents');
			}
                        $(this).dialog('close');
                    },
                    Cancel: function () {
                        $(this).dialog('close');
                    }
                },
		beforeClose: function () {
			reset();
		}
            });
            $('#calEventDialog').dialog('open');

        },
        eventClick: function (calEvent, jsEvent, view) {
	    var titlefield = $('#eventTitle');
            var desc = $('#eventDesc');
            var loc = $('#eventLocation');
            var url = $('#eventURL');
            var cat = $('#eventCategory');
            var type = "event";
            var color = "";
            var context = $('#contextOption');
            var repeat = "false";
            var repeatLength = $('#eventRepeatLength');
            var repeatFreq = $('#eventRepeatFreq');
            var emailReminder = "";
            var popupReminder = "";
	    var title = "";
		window.currentid = calEvent.id;
	    if(calEvent.url){
			var w= window.open(calEvent.url, '_blank');
		}

	    if (calEvent.repeat == 'true') {
		$("input[type='radio'][name='eventRepeat']").filter("[value='true']").prop('checked', true);
		$('#repeatFields').slideDown();
		repeatLength.val(calEvent.repeatLength);
		repeatFreq.val(calEvent.repeatFreq);
		$('#eventRepeatEnd').datepicker("setDate", calEvent.repeatEnd);
	    } else {
		$('#repeatFields').slideUp();
		$("input[type='radio'][name='eventRepeat']").filter("[value='true']").prop('checked', false);
	    }
	    if (calEvent.type == 'context') {
		$('#typeContext').trigger('click');
		$("input[type='radio'][name='eventType']").filter("[value='context']").prop('checked', true);
		$("input[type='radio'][name='eventType']").prop('disabled', true);
		context.val(calEvent.context);
		$('#contextEmailReminder').val(calEvent.emailReminder);
		$('#contextPopupReminder').val(calEvent.popupReminder);
	    } else {
		$('#typeEvent').trigger('click');
		titlefield.val(calEvent.title);
	        loc.val(calEvent.loc);
	        url.val(calEvent.url);
		cat.val(calEvent.category);
		$("input[type='radio'][name='eventType']").filter("[value='event']").prop('checked', true);
		$("input[type='radio'][name='eventType']").prop('disabled', true);
		$('#eventEmailReminder').val(calEvent.emailReminder);
		$('#eventPopupReminder').val(calEvent.popupReminder);
	    	desc.val(calEvent.description);
	    }
	    
            $("#calEventDialog").dialog("option", "buttons", [{
                text: "Save",
                click: function () {
			type = $("input[type='radio'][name='eventType']:checked").val();
			repeat = $("input[type='radio'][name='eventRepeat']:checked").val();
			if (type=='event') {
                            $.ajax({
				url:'../fullcalendar/calendar/getColor.php', 
				type:'post',
				data: { type:'category', id:cat.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				color = "#"+ result;
				},
			    });
			    title=titlefield.val();
			    emailReminder = $('#eventEmailReminder').val();
			    popupReminder = $('#eventPopupReminder').val();
			} else {
                           $.ajax({
				url:'../fullcalendar/calendar/getColor.php', 
				type:'post',
				data: { type:'context', id:context.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				color = "#"+ result;
				},
			    });
			  $.ajax({
				url:'../fullcalendar/calendar/getContextName.php', 
				type:'post',
				data: { id:context.val() },
				dataType: 'json',
				async: false,
				success: function(result){
				title = "Context: " + result;
				},
			    });
			    emailReminder = $('#contextEmailReminder').val();
			    popupReminder = $('#contextPopupReminder').val();
			}
                    calEvent.title = title;
                    calEvent.description = desc.val();
                    calEvent.loc = loc.val();
                    calEvent.url = url.val();
                    calEvent.type = type;
                    calEvent.color = color;
                    calEvent.category = cat.val();
		    calEvent.context = context.val();
                    calEvent.repeat = repeat;
                    calEvent.repeatLength = repeatLength.val();
                    calEvent.repeatFreq = repeatFreq.val();
                    calEvent.repeatEnd = repeatEnd;
                    calEvent.emailReminder = emailReminder;
                    calEvent.popupReminder = popupReminder;
		    calEvent.description = desc.val();
                    $myCalendar.fullCalendar('updateEvent', calEvent);
		    var e="";
		    if(calEvent.end != null){
		         e=calEvent.end.getTime()/1000;
			}
		    if (type == 'event') {
		    	$.post("../fullcalendar/calendar/updateEvent.php", {
			       id: calEvent.id,
			       category: cat,
                               start: calEvent.start.getTime() / 1000,
                               end: e,
                               title: title,
                               allDay: calEvent.allDay,
                               loc: loc.val(),
                               url: url.val(),
                               category: cat.val(),
                               repeat: repeat,
                               repeatLength: repeatLength.val(),
                               repeatFreq: repeatFreq.val(),
                               repeatEnd: repeatEnd,
                               emailReminder: emailReminder,
                               popupReminder: popupReminder,
                               description: desc.val()
                    	       },
                    	       function (result) {
				}
                    	);   
		   } else {
			$.post("../fullcalendar/calendar/updateContext.php", {
                               id: calEvent.id,
                               //change date to unix time
                               start: calEvent.start.getTime() / 1000,
                               end: e,
                               contextID: context.val(),
			       allDay: calEvent.allDay,
                               repeat: repeat,
                               repeatLength: repeatLength.val(),
                               repeatFreq: repeatFreq.val(),
                               repeatEnd: repeatEnd,
                               emailReminder: emailReminder,
                               popupReminder: popupReminder
                               },
                               function (result){
				}
			);
		   }

			$(this).dialog("close");
                }

            }, {
                text: "Delete",
                click: function () {
		    type = $("input[type='radio'][name='eventType']:checked").val();
                    $myCalendar.fullCalendar('removeEvents', calEvent.id);
                    $(this).dialog("close");
		    if(type=='event'){
                    $.post("../fullcalendar/calendar/deleteEvent.php", {
                            id: calEvent.id
                        },
                        function (a, b, c) {}
                        );
		    }
		   else{
			    $.post("../fullcalendar/calendar/deleteContext.php", {
                            id: calEvent.id,
			    contextID: context.val()
                        },
                        function (a, b, c) {}
                        );

			}
                }
            }, {
                text: "Cancel",
                click: function () {
                    $(this).dialog("close");
                }
            }]);
	    $("#calEventDialog").dialog("option", "width", "50%");
            $("#calEventDialog").dialog("option", "title", "Edit Block");
	    $("#calEventDialog").on( "dialogbeforeclose", function( event, ui ) {reset();} );
            $("#calEventDialog").dialog('open');
	    return false;
        },
        editable: true,
        eventResize: function (event, dayDelta, minuteDelta, revetFunc) {
		
            $.post("../fullcalendar/calendar/resizeEvent.php", {
                    id: event.id,
                    start: event.start.getTime() / 1000,
                    end: event.end.getTime() / 1000
                },
                function (a, b, c) {}
            );
        },

	eventDrop: function (event, dayDelta, minuteDelta, revetFunc) {
            $.post("../fullcalendar/calendar/resizeEvent.php", {
                    id: event.id,
                    start: event.start.getTime() / 1000,
                    end: event.end.getTime() / 1000
                },
                function (a, b, c) {}
            );
        },

        events: {
		 // url:'../fullcalendar/calendar/repeatToJson.php',
		  url:'../fullcalendar/calendar/repeatTesting.php',
              	  type: 'POST',
              	  data: {
			   category: category,
			   context: context
			}
		},

        eventRender: function (event, element) {
        }

    });
});

