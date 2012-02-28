<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Actionable Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class decayimage extends endtime {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
  {
    parent::__construct();
  }

  public function add() {
    if (preg_match(':^admin/reports/edit:', url::current())) {
      // We replace this because we want to add our configureables in the same
      // section.
      Event::replace('ushahidi_action.report_form_admin_after_time', 
        array(new endtime, '_report_form'),
        array($this, '_report_form'));

      // Hook into the report_submit_admin (post_POST) event right before saving
      Event::replace('ushahidi_action.report_submit_admin', 
        array(new endtime, '_report_validate'),
        array($this, '_report_validate'));

      // Hook into the report_edit (post_SAVE) event
      Event::replace('ushahidi_action.report_edit', 
        array(new endtime, '_report_form_submit'),
        array($this, '_report_form_submit'));

    } else if (preg_match(':^reports:', url::current())) {
      Event::add('ushahidi_action.main_footer', 
        array($this, 'decayimage_ushahidi_filter_header_js'));
    }
	}
	
	public function decayimage_ushahidi_filter_header_js()
	{
    // TODO: figure out if the line below is necesary
    //echo plugin::render("javascript");
    echo "<script src=\"".url::base()."decayimage\"></script>";
  }

  public function _report_form() {
		// Load the View
		$view = View::factory('decayimage/endtime_form');
		// Get the ID of the Incident (Report)
		$id = Event::$data;
		
		//initialize the array
		$form = array
			(
			    'end_incident_date'  => '',
			    'end_incident_hour'      => '',
			    'end_incident_minute'      => '',
			    'end_incident_ampm' => ''
			);
		
		
		if ($id)
		{
			// Do We have an Existing Actionable Item for this Report?
			$endtime_item = ORM::factory('endtime')
				->where('incident_id', $id)
				->find();

			$view->applicable = $endtime_item->applicable;
			$view->remain_on_map = $endtime_item->remain_on_map;
			$endtime_date = $endtime_item->endtime_date;
			
			if($endtime_date == "")
			{
				$incident = ORM::factory('incident')->where('id', $id)->find();
				$i_date_time = $incident->incident_date;
				$form['end_incident_date'] = date('m/d/Y', strtotime($i_date_time));
				$form['end_incident_hour'] = date('h', strtotime($i_date_time));
				$form['end_incident_minute'] = date('i', strtotime($i_date_time));
				$form['end_incident_ampm'] = date('a', strtotime($i_date_time));
			}
			else
			{
				$form['end_incident_date'] = date('m/d/Y', strtotime($endtime_date));
				$form['end_incident_hour'] = date('h', strtotime($endtime_date));
				$form['end_incident_minute'] = date('i', strtotime($endtime_date));
				$form['end_incident_ampm'] = date('a', strtotime($endtime_date));
			}
		}		
		else //initialize to now
		{
			$view->applicable = 0;
			$view->remain_on_map= 0;
			$form['end_incident_date'] = date("m/d/Y",time());
			$form['end_incident_hour'] = date('h', time());
			$form['end_incident_minute'] = date('i', time());
			$form['end_incident_ampm'] = date('a', time());
		}
		
		// Time formatting
		$view->minute_array = $this->_minute_array();
		$view->hour_array = $this->_hour_array();
		$view->ampm_array = $this->_ampm_array();
		$view->date_picker_js = $this->_date_picker_js();

		$view->form = $form;
    $view->render(TRUE);
  }

	/**
	 * Validate Form Submission
	 */
	public function _report_validate() {
    parent::_report_validate();
		if(is_object($this->post_data))
		{
			$this->post_data->add_rules('remain_on_map','digit');
		}
	}

	/**
	 * Handle Form Submission and Save Data
	 */
	public function _report_form_submit() {
    parent::_report_form_submit();

		$incident = Event::$data;
		$id = $incident->id;
    if ($this->post_data) {
      // TODO: remain_on_map should be moved to a table independent of the 
      // endtime table.
			$endtime = ORM::factory('endtime')
				->where('incident_id', $id)
        ->find();

      $endtime->remain_on_map = isset($this->post_data['remain_on_map']) ? 
        $this->post_data['remain_on_map'] : "0";

      Kohana::log('info', 'decayimage::_report_form_submit() remain_on_map: '.
        $endtime->remain_on_map);

			$endtime->save();
    }
  }

}//end class

new decayimage;
