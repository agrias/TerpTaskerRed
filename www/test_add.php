

<script src="jquery-1.10.2.min.js">
</script>
<script>


sendCall();

function sendCall() {
        var conversations = new Array(2);
	conversations[1] =
	{
            contactName: "test call",
	    phoneNum: "5555555555",
	    textContent: "testText1",
	    type: "2"
     	     
        };
	conversations[2] = 
	   {contactName: "test call2",
	    phoneNum: "3333333333",
	     textContent: "testText2",
	    type: "2"
	};

	alert(conversations);
	conversations = JSON.stringify(conversations);
	alert(conversations);
        $.ajax({
            url: 'add_tojson.php',
            type: 'post',
            dataType: 'json',
  	    data:  { 'conversations' : conversations},
            success: function (data) {
              //  $('#target').html(data.msg);
            },
	
        });
    }
</script>