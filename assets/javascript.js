jQuery(document).ready(function($) {
  $(".chosen-select").chosen({
  	placeholder_text_multiple:"Search .. "

  	});
	//called when key is pressed in textbox
  $("#percentage").keypress(function (e) {
    //if the letter is not digit then display error and don't type anything
     if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
  	 //display error message
        $("#errmsg").html("Digits Only").show().fadeOut("slow");
               return false;
    }
   });
	//Hide all panels at start
	$('.mode-panel').hide();
	//When choosing mode ,display related panel
	$( '.pricing-mode-li' ).on('click',function(){
		  $( '.pricing-mode-li' ).removeClass( "active" );
		  $(this).addClass("active");
		  var box_id=$(this).attr('name');
		  $('.mode-panel').hide("slow");
		  $('#'+box_id).show("slow");
		  return false;		  

	});

	//Apply percentage actoin 

	$('.percentge-submit').on('click',function(){

		$('#loader').css('display','block');
		$('.submit').css('display','none');
		var type=$(this).attr('name');
	    var operation=$("li.active").attr('name');
		var percentage=$("#percentage").val();
		var values=new Array();
		if(operation=="specific_categories"){
			values=$("#add_categories").val();
		}

		else if(operation=="specific_products")
		{
			values=$("#add_products").val();
		}
			
		jQuery.ajax({
		type: "post",
		url: ajaxurl,
		data: { action: 'wbpp_apply_percentge' , operation: operation ,percentage:percentage,values:values,type:type}
		}).done(function( msg ) {
		$('#loader').css('display','none');
		$('.submit').css('display','block');
		$('.updated').css('display','block');
		$( "<p>"+msg.response+"</p>" ).appendTo( ".updated" );

		});
		return false;


	});

})