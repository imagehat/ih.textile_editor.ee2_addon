<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
	
    Textile Editor Helper (TEH) extension for ExpressionEngine Version 2.0 (build 20100517)

    EE extension by Mike Kroll, www.imagehat.com
    Port of Textile Editor by Dave Olson, slateinfo.blogs.wvu.edu

    ------------------------------------------------------------------------------	
    Textile Editor v0.2
    ------------------------------------------------------------------------------	
    created by: dave olsen, wvu web services
    created on: march 17, 2007
    project page: slateinfo.blogs.wvu.edu
    inspired by: 
     - Patrick Woods, http://www.hakjoon.com/code/38/textile-quicktags-redirect & 
     - Alex King, http://alexking.org/projects/js-quicktags
    ------------------------------------------------------------------------------

    Copyright (c) 2007 Dave Olsen, West Virginia University

    Permission is hereby granted, free of charge, to any person
    obtaining a copy of this software and associated documentation
    files (the "Software"), to deal in the Software without
    restriction, including without limitation the rights to use,
    copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following
    conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
    OTHER DEALINGS IN THE SOFTWARE.

    ------------------------------------------------------------------------------

*/


class Ih_textile_editor_ext
{
	var $settings		= array();
	
	var $name			= 'Textile Editor Helper (TEH)';
	var $version		= '2.0.0';
	var $description	= 'Makes all Textareas set to use Textile formatting in the Publish area WYSIWYG-ish';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://slateinfo.blogs.wvu.edu/plugins/textile_editor_helper/';
	
	/**
	 * Constructor
	 *
	 **/
	function Ih_textile_editor_ext($settings='')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	// END
	
	/**
	 * Configure and initialize editor(s)
	 *
	 **/
	function initialize_editor($data)
	{

        // Insert CSS
        $this->EE->cp->add_to_head('<link rel="stylesheet" href="'.trim($this->settings['teh_path']).'stylesheets/textile-editor.css" type="text/css" media="screen" />');
        
        // Add settings to the global EE javacript object
        $this->EE->javascript->set_global('teh_options.view', 'extended');
        $this->EE->javascript->set_global('teh_options.image_path', trim($this->settings['teh_path']).'images/');
        $this->EE->javascript->set_global('teh_options.help_url', $this->EE->cp->masked_url(trim($this->settings['help_url'])));
        $this->EE->javascript->set_global('teh_options.encode_email', trim($this->settings['encode_email']));
        
        
        // Build the JS
	    $js = '<!-- Textile Editor Extension -->

<script type="text/javascript" src="'.trim($this->settings['teh_path']).'javascripts/textile-editor.js"></script>
<script type="text/javascript" src="'.trim($this->settings['teh_path']).'javascripts/textile-editor-config.js"></script>

<script type="text/javascript">
//<![CDATA[
    
    $(document).ready(function($) {
        
        // Initialize textile fields without formatting menu
        $("input[name*=\'field_ft_\']").each(function(){
            if ($(this).val() == "textile") {
                var id = $(this).attr("name").substring(9);
    			var canvas = "field_id_"+id;			
                $("#"+canvas).TextileEditor(EE.teh_options);
    			teh_filebrowser(canvas);
    		}
        });
    
        // Initialize textile fields with formatting menu
    	$("select[name*=\'field_ft_\']").each(function(){
    	    var id = $(this).attr("name").substring(9);
    	    var canvas = "field_id_"+id;
			var toolbar = $("#textile-toolbar-"+canvas);
			var eebuttons = (typeof(EE.publish.markitup.fields["field_id_"+id]) != "undefined") ? true : false; // flag if the field set to display default formatting buttons?
			
    		if ($(this).val() == "textile") {
			    if(eebuttons && $("#markItUpField_id_"+id).length) {
			        $("#"+canvas).markItUpRemove();
		        }
				if (toolbar.length == 0) {
				    $("#"+canvas).TextileEditor(EE.teh_options); 
				    teh_filebrowser(canvas);
				}
    		}
    					
    		// Toggle TEH and toggle default formatting buttons if needed
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
    

//]]>
</script>
<!-- END Textile Editor Extension -->';
        
		// Insert JS
		$this->EE->cp->add_to_foot($js);
		
		// Just return data to hook, thanks for calling
		return ($this->EE->extensions->last_call !== FALSE) ? $this->EE->extensions->last_call : $data;
    }
    // END add header
  
    
    /**
	 * Activate extension
	 *
	 **/
    function activate_extension()
    {
    	$default_settings = serialize( $this->default_settings() );
    	
    	$this->EE->db->insert('extensions',
                                array('extension_id'	=> '',
                                    'class'			=> get_class($this),
                                    'method'		=> "initialize_editor",
                                    'hook'			=> "publish_form_channel_preferences",
                                    'settings'		=> $default_settings,
                                    'priority'		=> 10,
                                    'version'		=> $this->version,
                                    'enabled'		=> "y"
                                    )
    			   );

    }
    // END activate
    
    
    /**
	 * Update extension
	 *
	 **/
    function update_extension($current='')
    {
    	
             if ($current == '' OR $current == $this->version)
             {
                 return FALSE;
             }
             
             if ($current < '1.1.0')
             {
                 // Kill the old version just in case (class was renamed in 1.1)
                 $this->EE->db->delete('extensions', array('class' => get_class($this)));
                 
                 // Add new settings
                 $this->EE->db->where('class', get_class($this));
                 $this->EE->db->update('extensions', array('settings' => addslashes(serialize($this->settings))));
             }
             
             // Update version
             $this->EE->db->where('class', get_class($this));
             $this->EE->db->update('extensions', array('version' => $this->version));    
               	
    }
    // END update
    
    
    /**
	 * Disable extension
	 *
	 **/
    function disable_extension()
	{		
		$this->EE->db->delete('extensions', array('class' => get_class($this)));
	}
	
	/**
	 * Extension settings
	 *
	 **/
    function settings()
    {    	
    	$settings = array();
    	
		$settings['teh_path']	  = '';
		$settings['help_url']     = '';
		$settings['encode_email'] = array('r', array('yes' => "yes", 'no' => "no"), 'no');
    	   	
    	return $settings;
    }
    // END settings
    
    
    /**
	 * Default Extension settings
	 *
	 * @since version 1.2.0
	 **/
    function default_settings()
    {    	
    	$theme_folder_url = $this->EE->config->item('theme_folder_url');
		if (substr($theme_folder_url, -1) != '/') $theme_folder_url .= '/';
    	
    	$default_settings = array(
    	    'teh_path'	   => $theme_folder_url . 'teh/',
    		'help_url'     => 'http://redcloth.org/hobix.com/textile/',
    		'encode_email' => 'no'
    	);
    	   	
    	return $default_settings;
    }
    // END Default settings
	
}


/* End of file ext.ih_textile_editor.php */
/* Location: ./system/extensions/ext.ih_textile_editor.php */