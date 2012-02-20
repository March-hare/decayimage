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

class Decayimage_Controller extends Template_Controller {

	/**
	 * Name of the view template for this controller
	 * @var string
	 */
  public $template = 'decayimage/json';

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
   * Generate JSON in NON-CLUSTER mode
   *
   * This is an extension of the controllers/json.php:index() method overridden 
   * to add relevant functionality to 
   *
	 */
	public function json()
  {
		$json = "";
		$json_item = "";
		$json_array = array();
		$color = Kohana::config('settings.default_map_all');

		$media_type = (isset($_GET['m']) AND intval($_GET['m']) > 0)? intval($_GET['m']) : 0;
		
		// Get the incident and category id
		$category_id = (isset($_GET['c']) AND intval($_GET['c']) > 0)? intval($_GET['c']) : 0;
		$incident_id = (isset($_GET['i']) AND intval($_GET['i']) > 0)? intval($_GET['i']) : 0;
		
		// Get the category colour
		if (Category_Model::is_valid_category($category_id))
		{
			$color = ORM::factory('category', $category_id)->category_color;
    }

    // The default path for all uploaded icons
    $prefix = url::base().Kohana::config('upload.relative_directory');

    // Get the default decayimage icon
    $decayimage_default_icon = ORM::factory('decayimage', 1);
    if ($decayimage_default_icon->decayimage_thumb == $this->default_decayimage_thumb) {
      $decayimage_default_icon = url::site() .'/plugins/decayimage/images/'.
        $decayimage_default_icon->decayimage_thumb;
    } else {
      $decayimage_default_icon = $prefix .'/'. 
        $decayimage_default_icon->decayimage_thumb;
    }

    // Find out if the endtime and decayimage plugins are installed
    $shouldDecayOnEnd = ORM::factory('plugin')
      ->orwhere(array('plugin_name' => 'decayimage', 'plugin_name' => 'endtime'))
      ->count_all() == 2;
		
		// Fetch the incidents
    $markers = (isset($_GET['page']) AND intval($_GET['page']) > 0)? reports::fetch_incidents(TRUE) : reports::fetch_incidents();
		
		// Variable to store individual item for report detail page
		$json_item_first = "";	
		foreach ($markers as $marker)
		{
			$thumb = "";
			if ($media_type == 1)
			{
				$media = ORM::factory('incident', $marker->incident_id)->media;
				if ($media->count())
				{
					foreach ($media as $photo)
					{
						if ($photo->media_thumb)
						{ 
							// Get the first thumb
							$prefix = url::base().Kohana::config('upload.relative_directory');
							$thumb = $prefix."/".$photo->media_thumb;
							break;
						}
					}
				}
      }

      // If we should decay on end then we need to find out if the incident has
      // ended
      $incidentHasEnded = FALSE;
      if ($shouldDecayOnEnd) {
        $incidentHasEnded = (bool) ORM::factory('endtime')
          // Does the use of php date cause an issue with application timezone 
          // vs system timezone?
          ->where(array(
            'incident_id' => $marker->incident_id,
            'incident_active' => '1',
            'endtime_date <' => date("Y-m-d H:i:s"),
            'remain_on_map' => '2'))
          ->count_all();
      }

      /* get the icon from the associated categories */
      Kohana::log('info', 'Decayimage_Controller::json() $marker'. print_r($marker, 1));
      $cats = ORM::factory('incident', $marker->incident_id)->category;
      Kohana::log('info', 'Decayimage_Controller::json() $cats'. print_r($cats, 1));
      $icon = Array();
      if ($cats->count())
      {
        foreach ($cats as $category)
        {
          if ($category->category_image)
          { 
            // TODO: this should be an array.
            $iconImage = '"'. $prefix."/". $category->category_image .'"';
            // If the endtime and decayimage modules are installed we should
            // use the decayimage associated with the category if it is past
            // the incidents endtime
            if ($incidentHasEnded) {
              $decayImageObject = ORM::factory('decayimage')
                ->where('category_id', $category->category_id);
              if ($decayImageObject->loaded()) {
                $iconImage = $decayImageObject->decayimage_thumb;
              }
            }
            $icon[] = $iconImage;
          }
        }
      }
			
			$json_item = "{";
			$json_item .= "\"type\":\"Feature\",";
			$json_item .= "\"properties\": {";
			$json_item .= "\"id\": \"".$marker->incident_id."\", \n";

			$encoded_title = utf8tohtml::convert($marker->incident_title, TRUE);
			$encoded_title = str_ireplace('"','&#34;',$encoded_title);
			$encoded_title = json_encode($encoded_title);
			$encoded_title = str_ireplace('"', '', $encoded_title);

			$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a "
					. "href='".url::base()."reports/view/".$marker->incident_id."'>".$encoded_title)."</a>") . "\","
					. "\"link\": \"".url::base()."reports/view/".$marker->incident_id."\", ";

			$json_item .= (isset($category))
				? "\"category\":[" . $category_id . "], "
				: "\"category\":[0], ";

			$json_item .= "\"color\": \"".$color."\", \n";
			//$json_item .= "\"icon\": \"".$icon."\", \n";
			$json_item .= "\"icon\": [". implode(',', $icon) ."], \n";
			$json_item .= "\"thumb\": \"".$thumb."\", \n";
			$json_item .= "\"timestamp\": \"" . strtotime($marker->incident_date) . "\"";
			$json_item .= "},";
			$json_item .= "\"geometry\": {";
			$json_item .= "\"type\":\"Point\", ";
			$json_item .= "\"coordinates\":[" . $marker->longitude . ", " . $marker->latitude . "]";
			$json_item .= "},";
			$json_item .= "\"incidentHasEnded\": \"". ($incidentHasEnded ? 1 : 0) ."\"";
			$json_item .= "}";

			if ($marker->incident_id == $incident_id)
			{
				$json_item_first = $json_item;
			}
			else
			{
				array_push($json_array, $json_item);
			}
			
			// Get Incident Geometries
			$geometry = $this->_get_geometry($marker->incident_id, $marker->incident_title, $marker->incident_date);
			if (count($geometry))
			{
				$json_item = implode(",", $geometry);
				array_push($json_array, $json_item);
      }
		}
		
		if ($json_item_first)
		{
			// Push individual marker in last so that it is layered on top when pulled into map
			array_push($json_array, $json_item_first);
		}
		
		$json = implode(",", $json_array);

    header('Content-type: application/json; charset=utf-8');
    $this->template->json = $json;
    $this->template->decayimage_default_icon = $decayimage_default_icon;
    Kohana::log('info', print_r($this->template, 1));
  }

	/**
	 * Get Geometry JSON
	 * @param int $incident_id
	 * @param string $incident_title
	 * @param int $incident_date
	 * @return array $geometry
	 */
	private function _get_geometry($incident_id, $incident_title, $incident_date)
	{
		$geometry = array();
		if ($incident_id)
		{
			$geom_data = $this->_get_geometry_data_for_incident($incident_id);
			$wkt = new Wkt();

			foreach ( $geom_data as $item )
			{
				$geom = $wkt->read($item->geometry);
				$geom_array = $geom->getGeoInterface();

				$json_item = "{";
				$json_item .= "\"type\":\"Feature\",";
				$json_item .= "\"properties\": {";
				$json_item .= "\"id\": \"".$incident_id."\", ";
				$json_item .= "\"feature_id\": \"".$item->id."\", ";

				$title = ($item->geometry_label) ? 
					utf8tohtml::convert($item->geometry_label,TRUE) : 
					utf8tohtml::convert($incident_title,TRUE);
					
				$fillcolor = ($item->geometry_color) ? 
					utf8tohtml::convert($item->geometry_color,TRUE) : "ffcc66";
					
				$strokecolor = ($item->geometry_color) ? 
					utf8tohtml::convert($item->geometry_color,TRUE) : "CC0000";
					
				$strokewidth = ($item->geometry_strokewidth) ? $item->geometry_strokewidth : "3";

				$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='" . url::base() . "reports/view/" . $incident_id . "'>".$title."</a>")) . "\",";

				$json_item .= "\"description\": \"" . utf8tohtml::convert($item->geometry_comment,TRUE) . "\", ";
				$json_item .= "\"color\": \"" . $fillcolor . "\", ";
				$json_item .= "\"strokecolor\": \"" . $strokecolor . "\", ";
				$json_item .= "\"strokewidth\": \"" . $strokewidth . "\", ";
				$json_item .= "\"link\": \"".url::base()."reports/view/".$incident_id."\", ";
				$json_item .= "\"category\":[0], ";
				$json_item .= "\"timestamp\": \"" . strtotime($incident_date) . "\"";
				$json_item .= "},\"geometry\":".json_encode($geom_array)."}";
				$geometry[] = $json_item;
			}
		}
		
		return $geometry;
  }

	/**
	 * Get geometry records from the database and cache 'em.
	 *
	 * They're heavily read from, no point going back to the db constantly to
	 * get them.
	 * @param int $incident_id - Incident to get geometry for
	 * @return array
	 */
	public function _get_geometry_data_for_incident($incident_id) {
		if (self::$geometry_data) {
			return isset(self::$geometry_data[$incident_id]) ? self::$geometry_data[$incident_id] : array();
		}

		$db = new Database();
		// Get Incident Geometries via SQL query as ORM can't handle Spatial Data
		$sql = "SELECT id, incident_id, AsText(geometry) as geometry, geometry_label, 
			geometry_comment, geometry_color, geometry_strokewidth FROM ".$this->table_prefix."geometry";
		$query = $db->query($sql);

		foreach ( $query as $item )
		{
			self::$geometry_data[$item->incident_id][] = $item;
		}

		return isset(self::$geometry_data[$incident_id]) ? self::$geometry_data[$incident_id] : array();
	}


}

