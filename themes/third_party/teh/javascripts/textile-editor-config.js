// -------------------------------------------------------------
//  Standard TEH buttons
// -------------------------------------------------------------
//  Delete or comment out the ones you want to disable.

var teButtons = jQuery.TextileEditor.buttons;

teButtons.push(new TextileEditorButton('ed_strong', 'bold.png', '*', '*', 'b', 'Bold','s'));
teButtons.push(new TextileEditorButton('ed_emphasis', 'italic.png', '_', '_', 'i', 'Italicize','s'));
teButtons.push(new TextileEditorButton('ed_underline', 'underline.png', '+', '+', 'u', 'Underline','s'));
teButtons.push(new TextileEditorButton('ed_strike', 'strikethrough.png', '-', '-', 's', 'Strikethrough','s'));
teButtons.push(new TextileEditorButton('ed_ol', 'list_numbers.png', '# ', '\n', ',', 'Numbered List'));
teButtons.push(new TextileEditorButton('ed_ul', 'list_bullets.png', '* ', '\n', '.', 'Bulleted List'));
teButtons.push(new TextileEditorButton('ed_p', 'paragraph.png', 'p', '\n', 'p', 'Paragraph'));
teButtons.push(new TextileEditorButton('ed_h1', 'h1.png', 'h1', '\n', '1', 'Header 1'));
teButtons.push(new TextileEditorButton('ed_h2', 'h2.png', 'h2', '\n', '2', 'Header 2'));
teButtons.push(new TextileEditorButton('ed_h3', 'h3.png', 'h3', '\n', '3', 'Header 3'));
teButtons.push(new TextileEditorButton('ed_h4', 'h4.png', 'h4', '\n', '4', 'Header 4'));
teButtons.push(new TextileEditorButton('ed_block', 'blockquote.png', 'bq', '\n', 'q', 'Blockquote'));
teButtons.push(new TextileEditorButton('ed_outdent', 'outdent.png', ')', '\n', ']', 'Outdent'));
teButtons.push(new TextileEditorButton('ed_indent', 'indent.png', '(', '\n', '[', 'Indent'));
teButtons.push(new TextileEditorButton('ed_justifyl', 'left.png', '<', '\n', 'l', 'Left Justify'));
teButtons.push(new TextileEditorButton('ed_justifyc', 'center.png', '=', '\n', 'e', 'Center Text'));
teButtons.push(new TextileEditorButton('ed_justifyr', 'right.png', '>', '\n', 'r', 'Right Justify'));
teButtons.push(new TextileEditorButton('ed_justify', 'justify.png', '<>', '\n', 'j', 'Justify'));
//teButtons.push(new TextileEditorButton('[id]', '[image.png]', '[open]', '[close]', '[accesskey]', '[Title]', '[simple or extended]'));


// -------------------------------------------------------------
//  Custom button additions
// -------------------------------------------------------------
// Delete or comment out the ones you want to disable.

teButtons.push(new TextileEditorButtonSeparator(''));
teButtons.push("<button onclick=\"insert_text(this, 'link');return false;\" class=\"standard\"><img src=\""+EE.teh_options.image_path+"world_link.png\" title=\"Link\" alt=\"Link\" /></button>");
teButtons.push("<button onclick=\"insert_text(this, 'email');return false;\" class=\"standard\"><img src=\""+EE.teh_options.image_path+"email_link.png\" title=\"Email\" alt=\"Email\" /></button>");
teButtons.push(new TextileEditorButtonSeparator(''));
teButtons.push("<button class=\"standard teh_filebrowser\"><img src=\""+EE.teh_options.image_path+"image.png\" title=\"File Browser\" alt=\"File Browser\" /></button>");
teButtons.push("<button onclick=\"teh_help('"+EE.teh_options.help_url+"');return false;\" class=\"standard teh_help\"><img src=\""+EE.teh_options.image_path+"help.png\" title=\"Help\" alt=\"Help\" /></button>");


// Attach EE filebrowser to image button
// Must be re-initialized when toolbar is added dynamically
function teh_filebrowser(canvas) {
	$("#textile-toolbar-"+canvas+" .teh_filebrowser" ).click(function (event) {
		var c;
		if ($(this).closest("#textile-toolbar-write_mode_textarea").length) {
            c = "write_mode_textarea"
        } else {
            c = $(this).closest(".publish_field").attr("id").replace("hold_field_", "field_id_")
        }
		if (c != undefined) {
            $("#" + c).focus()
        }
        window.file_manager_context = c;
		return false;
    });
	$.ee_filebrowser.add_trigger("#textile-toolbar-"+canvas+" .teh_filebrowser", function (c) {
		if(!c.is_image) {
			insert = '<a href="{filedir_' + c.directory + '}">';
		} else {
			alt = prompt('Alternative Text');
			insert = '<img src="{filedir_' + c.directory + '}' + c.name + '" alt="' + alt + '" ' + c.dimensions + ' />';
		}
		insert_text("#textile-toolbar-"+canvas+" .teh_filebrowser", 'file', insert);
        $.ee_filebrowser.reset()
    });

}

// Open Help link in new window
// Called from custom Help button
function teh_help(url) {
	window.open(url, "_blank");
	return false;
}

// Insert string into text field. 
// Called from dialog or filebrowser callback
function insert_text(button, which, string) {
	if(typeof(button) == 'string') {
		button = $(button).get(0); // dom element
	}
	var myField = document.getElementById(button.canvas);
	myField.focus();

	// Selection testing straight from TEH ---------------------
	var textSelected = false;
	var finalText = '';
	var insert = '';
	var FF = false;

	// grab the text that's going to be manipulated, by browser
	if (document.selection) { // IE support
		
		sel = document.selection.createRange();
		
		// set-up the text vars
		var beginningText = '';
		var followupText = '';
		var selectedText = sel.text;

		// check if text has been selected
		if (sel.text.length > 0) {
			textSelected = true;	
		}

	}
	else if (myField.selectionStart || myField.selectionStart == '0') { // MOZ/FF/NS/S support
		
		// figure out cursor and selection positions
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		var cursorPos = endPos;
		var scrollTop = myField.scrollTop;
		FF = true; // note that is is a FF/MOZ/NS/S browser

		// set-up the text vars
		var beginningText = myField.value.substring(0, startPos);
		var followupText = myField.value.substring(endPos, myField.value.length);

		// check if text has been selected
		if (startPos != endPos) {
			textSelected = true;
			var selectedText = myField.value.substring(startPos, endPos);	
		}
	}
	// End selection testing -----------------------------------
	
	// Prompt user and build URL link
	if (which == 'link') {
		
		var link = prompt("Enter a URL:", "http://");
		if (link == "http://" || link == "" || link == null) return false;
		
		if (textSelected) {
			link = '"'+selectedText+'":'+link;	
		} else {
			link = '"Text Here":'+link+' ';
		}
		insert = link;
	}
	
	// Prompt user and build email link
	if (which == 'email') {
		
		var link = prompt("Enter an email address:", "");
		if (link == "" || link == null) return false;

		// Check for encoding option and build link
		if (EE.teh_options.encode_email === 'yes')
		{
			if (textSelected) {
				link = '['+'email='+link+']'+selectedText+'[/email]';
			} else {
				link = '[email]'+link+'[/email]';
			}
		} else {
			if (textSelected) {
				link = '"'+selectedText+'":mailto:'+link;
			} else {
				link = '"'+link+'":mailto:'+link;
			}
		}
		insert = link;
	}
	
	// Filebrowser
	if (which == 'file') {
		insert = string;
	}

	// set the appropriate DOM value with the final text
	if (FF == true) {
		finalText = beginningText+insert+followupText;
		myField.value = finalText;
		myField.scrollTop = scrollTop;
	}
	else {
		finalText = insert;
		sel.text = finalText;
	}
	
	// build up the selection capture, doesn't work in IE
	if (textSelected) {
		myField.selectionStart = startPos;
		myField.selectionEnd = startPos+insert.length;
	}
	else {
		myField.selectionStart = cursorPos+insert.length;
		myField.selectionEnd = cursorPos+insert.length;
	}
	
	jQuery(button).addClass('unselected');
}

// END custom functions