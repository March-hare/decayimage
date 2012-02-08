<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Decayimage admin settings controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communications Collective <info@march-hare.org>
 * @module	   Decayimage
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */



class Decayimage_settings_Controller extends Admin_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'Decayimage';

		// If user doesn't have access, redirect to dashboard
		if ( ! admin::permissions($this->user, "manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}
  }

	/**
	 * Add Edit decayimage 
	 */
	public function index()
	{
    // The default decayimage thumb file name
    $default_decayimage_thumb = 'Question_icon_thumb.png';
    
		$this->template->content = new View('decayimage/settings');
		$this->template->content->title = Kohana::lang('decayimage.decayimage');

		// Setup and initialize form field names
		$form = array
		(
			'action' => '',
			'decayimage_id' => '',
      'decayimage_image' => '',
      'decayimage_file' => '',
      'decayimage_thumb' => '',
			'category_id' => '',
		);

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		$parents_array = array();

		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Fetch the submitted data
			$post_data = array_merge($_POST, $_FILES);
			
			// Extract decayimage-specific  information
      $decayimage_data = arr::extract($post_data, 'decayimage_icon');

      //TODO: this is where we would put the conditional code which depends on
      //wether or not the category icon module is installed.  In which case we
      //will allow an interface to associate decay icons with that category.

			// Check for action
			if ($post_data['action'] == 'a')
			{
				$post->add_rules('decayimage_icon', 'upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[50K]');

				
			}
			elseif ($post_data['action'] == 'r')
			{
        // Revert to default decayimage action
      }
		}

		//get array of categories
		$categories = ORM::factory("category")->where("category_visible", "1")->find_all();
		$cat_array = array();
		foreach($categories as $category)
		{
			$cat_array[$category->id] = $category->category_title;
    }
    $cat_array[0] = Kohana::lang('decayimage.default_incident_icon')

    //get array of decay images
		$decayimages = ORM::factory("decayimage")->find_all();
		$decayimage_array = array();
		foreach($decayimages as $decayimage)
		{
			$decayimage_array[$decayimage->decayimage_thumb] = $decayimage->decayiamge_thumb;
    }

		$this->template->content->cat_array = $cat_array;
		$this->template->content->decayimage_array = $decayimage_array;
    $this->template->content->url_site = url::site();
    $this->template->content->default_decayimage_thumb = $default_decayimage_thumb;
    $this->template->content->decayimages = $decayimages;
		$this->template->js = new View('decayimage/settings_js');
	}//end index function
	
	
}

