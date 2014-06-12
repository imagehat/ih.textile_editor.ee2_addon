$(document).ready(function($) {
    
    // Initialize all current textile fields
	$("textarea[name*=\'field_id_\']").each(function(){

		var canvas = $(this).attr("name");
		if( ! $(this).attr('id'))
		{
			$(this).attr('id', canvas);
		}
		
		if(typeof(EE.teh_options.fields) != "undefined" && typeof(EE.teh_options.fields[canvas]) != "undefined") {
			
			var id = EE.teh_options.fields[canvas];
			
			if($("#markItUpField_id_"+id).length) {
		        $("#"+canvas).markItUpRemove();
	        }
	
			$("#"+canvas).TextileEditor(EE.teh_options);
			teh_filebrowser(canvas);
		}
	});	
	

    // Toggle TEH toolbar and default formatting buttons (if needed) 
	// when formatting menu is changed.
	$("select[name*=\'field_ft_\']").each(function(){

	    var id = $(this).attr("name").substring(9);
	    var canvas = "field_id_"+id;
		var toolbar = $("#textile-toolbar-"+canvas);
		var eebuttons = (typeof(EE.publish.markitup.fields) != "undefined" && typeof(EE.publish.markitup.fields[canvas]) != "undefined") ? true : false; // flag if the field set to display default formatting buttons?
		
		$(this).change(function() { 
		    toolbar = $("#textile-toolbar-"+canvas); // update
			if ($(this).val() == "textile") {
			    if(eebuttons && $("#markItUpField_id_"+id).length) {
			        $("#"+canvas).markItUpRemove();
			    }
				if (toolbar.length == 0) {
				    $("#"+canvas).TextileEditor(EE.teh_options); 
				    teh_filebrowser(canvas);
			    }
			} else {
				if (toolbar.length > 0) {
				    toolbar.remove();
				}
				if(eebuttons && !$("#markItUpField_id_"+id).length) {
				    $("#"+canvas).markItUp(mySettings);
				}
			}
		});
	});
	
	// Intercept write mode and add textile editor or restore default html buttons
	// Currently EE always adds formatting buttons to write mode regardless of 
	// field settings so we will too.
	$(".write_mode_trigger").click(function() {
		if($(this).parents(".publish_field").find(".textile-toolbar").length) {
			if(!$("#textile-toolbar-write_mode_textarea").length) {
				$("#write_mode_textarea").markItUpRemove();
				$("#write_mode_textarea").TextileEditor(EE.teh_options);
				teh_filebrowser("write_mode_textarea");
			}
			$("#write_mode_textarea").focus();
		} else {
			if($("#textile-toolbar-write_mode_textarea").length) {
				$("#textile-toolbar-write_mode_textarea").remove();
			}
			if(!$("#markItUpWrite_mode_textarea").length) {
				$("#write_mode_textarea").markItUp(myWritemodeSettings);
			}
		}
		return false;
	});
	
});
