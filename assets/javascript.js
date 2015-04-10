jQuery(document).ready(function($) {

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
	//Add tags to tags container
	$('.add-elements').on('click', function(){
		var select_id=$(this).attr('select');
		var elt = $('#'+select_id+'_input');
		elt.tagsinput({
		  itemValue: 'value',
		  itemText: 'text',
		 
		});
		var value=$('#'+select_id).val();
		
		var items = $("#"+select_id+" option:selected").map(function() {
			var t=$(this).text();
			var v=$(this).val();
		    return [[t,v]];
		}).get();

	$.each(items, function(index, array) {
		
		elt.tagsinput('add', { "value": array[1] , "text": array[0]  });

	});

	}); 

	//Apply percentage actoin 

	$('#percentge-submit').on('click',function(){

		$('#loader').css('display','block');
		$('.submit').css('display','none');
	    var operation=$("li.active").attr('name');
		var percentage=$("#percentage").val();
		var values=new Array();
		if(operation=="specific_categories"){
			values=$("#add_categories_input").val();
			values=values.split(',');
		}

		else if(operation=="specific_products")
		{
			values=$("#add_products_input").val();
			values=values.split(',');
		}
			
		jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		data: { action: 'wbpp_apply_percentge' , operation: operation ,percentage:percentage,values:values}
		}).done(function( msg ) {
		$('#loader').css('display','none');
		$('.submit').css('display','block');
		$('.updated').css('display','block');
		$( "<p>"+msg.response+"</p>" ).appendTo( ".updated" );

		});
		return false;


	});

})