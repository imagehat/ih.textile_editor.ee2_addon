<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
/*
	
    Textile Editor Helper (TEH) extension for ExpressionEngine Version 2.1

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
	var $version		= '2.0.4';
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
		// Gather all current textile fields
		// Note: As of EE2.1.3 this now requires a query since field formatting
		// is not added to the CP markup if the formatting menu is not active.
		$textile_fields = array();
		$site_id = $this->EE->config->item('site_id');
		
		$this->EE->db->select('field_id');
		$this->EE->db->where('field_fmt', 'textile');
		$this->EE->db->where('site_id', $site_id);
		$query = $this->EE->db->get('exp_channel_fields');
		
		if($query->num_rows() > 0) {
			foreach($query->result() as $row)
			{
				$textile_fields['field_id_'.$row->field_id] = $row->field_id;
			}
		}
				
        // Insert CSS
        $this->EE->cp->add_to_head('<link rel="stylesheet" href="'.trim($this->settings['teh_path']).'stylesheets/textile-editor.css" type="text/css" media="screen" />');
        
        // Add settings to the global EE javacript object
        $this->EE->javascript->set_global('teh_options.view', 'extended');
        $this->EE->javascript->set_global('teh_options.image_path', trim($this->settings['teh_path']).'images/');
        $this->EE->javascript->set_global('teh_options.help_url', $this->EE->cp->masked_url(trim($this->settings['help_url'])));
        $this->EE->javascript->set_global('teh_options.encode_email', trim($this->settings['encode_email']));
        $this->EE->javascript->set_global('teh_options.fields', $textile_fields);

        // Insert JS
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.trim($this->settings['teh_path']).'javascripts/textile-editor.js"></script>');
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.trim($this->settings['teh_path']).'javascripts/textile-editor-config.js"></script>');
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.trim($this->settings['teh_path']).'javascripts/textile-editor-cp.js"></script>');
		
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
                                    'class'			=> __CLASS__,
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
             
             // Update version
             $this->EE->db->where('class', __CLASS__);
             $this->EE->db->update('extensions', array('version' => $this->version));    
               	
    }
    // END update
    
    
    /**
	 * Disable extension
	 *
	 **/
    function disable_extension()
	{		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
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
    	    'teh_path'	   => $theme_folder_url . 'third_party/teh/',
    		'help_url'     => 'http://redcloth.org/hobix.com/textile/',
    		'encode_email' => 'no'
    	);
    	   	
    	return $default_settings;
    }
    // END Default settings
	
}


/* End of file ext.ih_textile_editor.php */
/* Location: ./system/extensions/ext.ih_textile_editor.php */