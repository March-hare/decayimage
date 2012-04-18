<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Decayimage controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communications Collective
 * @module	   Decayimage Controller	
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
*/

class Decayimage_Controller extends Main_Controller {

	/**
	 * Default image for decaying incidents
	 * @var string
	 */
  public $default_decayimage_thumb = 'Question_icon_thumb.png';

	/**
	 * Database table prefix
	 * @var string
	 */
	protected $table_prefix;

	// Geometry data
	private static $geometry_data = array();

	public function __construct()
	{
    parent::__construct();

		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');
  }

	/**
	 * Displays all reports.
	 */
	public function index()
	{
		// Cacheable Controller
		$this->is_cachable = TRUE;

		$this->template->header->this_page = 'reports';
		$this->template->content = new View('reports');
		$this->themes->js = new View('reports_js');
    Kohana::log('info', 'decayimage::index _get_report_listing_view()');

		// Store any exisitng URL parameters
		$this->themes->js->url_params = json_encode($_GET);

		// Enable the map
		$this->themes->map_enabled = TRUE;

		// Set the latitude and longitude
		$this->themes->js->latitude = Kohana::config('settings.default_lat');
		$this->themes->js->longitude = Kohana::config('settings.default_lon');
		$this->themes->js->default_map = Kohana::config('settings.default_map');
		$this->themes->js->default_zoom = Kohana::config('settings.default_zoom');

		// Load the alert radius view
		$alert_radius_view = new View('alert_radius_view');
		$alert_radius_view->show_usage_info = FALSE;
		$alert_radius_view->enable_find_location = FALSE;
		$alert_radius_view->css_class = "rb_location-radius";

		$this->template->content->alert_radius_view = $alert_radius_view;

		// Get locale
		$l = Kohana::config('locale.language.0');

    // Get the report listing view
		$report_listing_view = $this->_get_report_listing_view($l);

		// Set the view
		$this->template->content->report_listing_view = $report_listing_view;

		// Load the category
		$category_id = (isset($_GET['c']) AND intval($_GET['c']) > 0)? intval($_GET['c']) : 0;
		$category = ORM::factory('category', $category_id);

		if ($category->loaded)
		{
			// Set the category title
			$this->template->content->category_title = Category_Lang_Model::category_title($category_id,$l);
		}
		else
		{
			$this->template->content->category_title = "";
		}

		// Collect report stats
		$this->template->content->report_stats = new View('reports_stats');
		// Total Reports

		$total_reports = Incident_Model::get_total_reports(TRUE);

		// Get the date of the oldest report
		if (isset($_GET['s']) AND !empty($_GET['s']) AND intval($_GET['s']) > 0)
		{
			$oldest_timestamp =  intval($_GET['s']);
		}
		else
		{
			$oldest_timestamp = Incident_Model::get_oldest_report_timestamp();
		}

		//Get the date of the latest report
		if (isset($_GET['e']) AND !empty($_GET['e']) AND intval($_GET['e']) > 0)
		{
			$latest_timestamp = intval($_GET['e']);
		}
		else
		{
			$latest_timestamp = Incident_Model::get_latest_report_timestamp();
		}


		// Round the number of days up to the nearest full day
		$days_since = ceil((time() - $oldest_timestamp) / 86400);
		$avg_reports_per_day = ($days_since < 1)? $total_reports : round(($total_reports / $days_since),2);

		// Percent Verified
		$total_verified = Incident_Model::get_total_reports_by_verified(TRUE);
		$percent_verified = ($total_reports == 0) ? '-' : round((($total_verified / $total_reports) * 100),2).'%';

		// Category tree view
		$this->template->content->category_tree_view = category::get_category_tree_view();

		// Additional view content
		$this->template->content->custom_forms_filter = new View('reports_submit_custom_forms');
		$disp_custom_fields = customforms::get_custom_form_fields();
		$this->template->content->custom_forms_filter->disp_custom_fields = $disp_custom_fields;
		$this->template->content->oldest_timestamp = $oldest_timestamp;
		$this->template->content->latest_timestamp = $latest_timestamp;
		$this->template->content->report_stats->total_reports = $total_reports;
		$this->template->content->report_stats->avg_reports_per_day = $avg_reports_per_day;
		$this->template->content->report_stats->percent_verified = $percent_verified;
		$this->template->content->services = Service_Model::get_array();

		$this->template->header->header_block = $this->themes->header_block();
		$this->template->footer->footer_block = $this->themes->footer_block();
	}

	/**
	 * Helper method to load the report listing view
	 */
	private function _get_report_listing_view($locale = '')
	{
		// Check if the local is empty
		if (empty($locale))
		{
			$locale = Kohana::config('locale.language.0');
		}

		// Load the report listing view
		$report_listing = new View('reports_listing');

		// Fetch all incidents
		$all_incidents = reports::fetch_incidents();

		// Reports
		$incidents = Incident_Model::get_incidents(reports::$params);

		// Swap out category titles with their proper localizations using an array (cleaner way to do this?)
		$localized_categories = array();
		foreach ($incidents as $incident)
		{
			$incident = ORM::factory('incident', $incident->incident_id);
			foreach ($incident->category AS $category)
			{
				$ct = (string)$category->category_title;
				if ( ! isset($localized_categories[$ct]))
				{
					$localized_categories[$ct] = Category_Lang_Model::category_title($category->id, $locale);
				}
			}
		}
		// Set the view content
		$report_listing->incidents = $incidents;
		$report_listing->localized_categories = $localized_categories;

		//Set default as not showing pagination. Will change below if necessary.
		$report_listing->pagination = "";

		// Pagination and Total Num of Report Stats
    $plural = (count($incidents) > 1)? "" : "s";

    $report_listing->stats_breadcrumb = 
      count($incidents).' '.Kohana::lang('ui_admin.reports').$plural;

		// Return
		return $report_listing;
	}

}

