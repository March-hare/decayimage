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

    switch (url::current()) {
    case 'admin/reports/edit':
      // We replace this because we want to add our configureables in the same
      // section.

      Kohana::log('info', 'endtime::add() '. print_r(new endtime, 1));
      Event::replace('ushahidi_action.report_form_admin_after_time', 
        //array($this, '_report_form'),
        array(new endtime, '_report_form'),
        //new endtime,
        //0,
        array($this, '_report_form')
      );
      break;

    case 'reports':
      Event::add('ushahidi_filter.header_js', 
        array($this, 'decayimage_ushahidi_filter_header_js'));
      break;
    }
	}
	
	public function decayimage_ushahidi_filter_header_js()
	{
    // Append a new showIncidentMap function to the end of the file
    preg_match(':^(.+)(//-->\s*</script>\s*)$:s', Event::$data, $matches);
    $layerName = Kohana::lang('ui_main.reports');
    $site = url::site();

$new_js = <<<ENDJS
  var showIncidentMapOrig = showIncidentMap;
  //showIncidentMapBak = (function() {
  showIncidentMap = (function() {
  //return showIncidentMapOrig();

  // Set the layer name
  var layerName = "{$layerName}";
      
  // Get all current layers with the same name and remove them from the map
  currentLayers = map.getLayersByName(layerName);
  // TODO: I am not really sure if this is needed
  currentLayersIcons = map.getLayersByName(layerName + 'Category Icons');
  for (var i = 0; i < currentLayers.length; i++)
  {
    map.removeLayer(currentLayers[i]);
    map.removeLayer(currentLayersIcons[i]);
  }

  // Default styling for the reports
  var reportStyle = OpenLayers.Util.extend({}, 
    OpenLayers.Feature.Vector.style["default"]);

  reportStyle.pointRadius = 8;
  reportStyle.fillColor = "#30E900";
  reportStyle.fillOpacity = "0.8";
  reportStyle.strokeColor = "#197700";
  // Does this make the total point radius = 8+3/2?
  reportStyle.strokeWidth = 3;
  reportStyle.graphicZIndex = 2;

  // Default style for the associated report category icons 
  var iconStyle =  OpenLayers.Util.extend({}, reportStyle);
  iconStyle.graphicOpacity = 1;
  iconStyle.graphicZIndex = 1;
  iconStyle.graphic = true;
  iconStyle.graphicHeight = 25;

  // create simple vector layer where the report icons will be placed
  var vLayer = new OpenLayers.Layer.Vector(layerName, {
    projection: new OpenLayers.Projection("EPSG:4326"),
    style: reportStyle,
    rendererOptions: {zIndexing: true}
  });

  // create a seperate vector layer where the icons associated with the report
  // categories will be placed.
  var vLayerIcons = new OpenLayers.Layer.Vector(layerName + ' Category Icons', {
    projection: new OpenLayers.Projection("EPSG:4326"),
    style: iconStyle,
    rendererOptions: {zIndexing: true}
  });
      
  // URL to be used for fetching the incidents
  fetchURL = "{$site}decayimage/json";
  
  // Generate the url parameter string
  parameterStr = makeUrlParamStr("", urlParameters);
  
  // Add the parameters to the fetch URL
  fetchURL += "?" + parameterStr;

  var aFeatures = new Array();

  // Fetch the incidents
  var json = jQuery.getJSON(fetchURL, function(data) {
    $.each(data.features, function(key, val) {

      // create a point from the latlon
      var incidentPoint = new OpenLayers.Geometry.Point(
        val.geometry.coordinates[0],
        val.geometry.coordinates[1]
      );
      var proj = new OpenLayers.Projection("EPSG:4326");
      incidentPoint.transform(proj, map.getProjectionObject());

      // If the incident has ended but it is configured to "decay" we should
      // set the incident icon to the decayimage default icon
      console.log(val.incidentHasEnded);
      var newIcidentStyle =  OpenLayers.Util.extend({}, reportStyle);
      if (val.incidentHasEnded) {
        newIncidentStyle.externalGraphic = data.decayimage_default_icon;
      }

      // create a feature vector from the point and style
      var feature = new OpenLayers.Feature.Vector(incidentPoint, null, reportStyle);
      feature.attributes = val.properties;
      vLayer.addFeatures([feature]);

      var offsetRadius = reportStyle.pointRadius+iconStyle.graphicHeight/2;
      // if the icon is set then apply it (this requires controller mod)
      // else if icon is an array, then place the icons around the incident
      if (val.properties.icon instanceof Array) {
        var numIcons = val.properties.icon.length;
        var iconCt = 1;
        // Loop over each icon setting externalGraphic and x,y offsets
        $.each(val.properties.icon, function(index, icon) {
          
          var newIconStyle =  OpenLayers.Util.extend({}, iconStyle);
          // TODO: make sure we are using the decayimage category icons if they
          // are set.  I think this should be transparently set by the json 
          // controller anyhow.
          newIconStyle.externalGraphic = icon;
          // TODO: -13 is a magic number here that got this working.
          // I dont totally understant what its related to.
          // pointRadius + strokeWidth + 2FunPixels?
          newIconStyle.graphicXOffset = -13+
            offsetRadius*Math.cos(((2*3.14)/(numIcons))*index);
          newIconStyle.graphicYOffset = -13+
            offsetRadius*Math.sin(((2*3.14)/(numIcons))*index);

          iconPoint = incidentPoint.clone();
          var feature = new OpenLayers.Feature.Vector(
            iconPoint, null, newIconStyle);
          vLayerIcons.addFeatures([feature]);
        });
      }
      // If icon is a single value (this is the protocol default)
      else if (val.properties.icon) {
        iconStyle.externalGraphic = val.properties.icon;
        iconStyle.graphicYOffset = offsetRadius;

        // create a feature vector from the point and style
        var feature = new OpenLayers.Feature.Vector(incidentPoint, null, reportStyle);
        vLayerIcons.addFeatures([feature]);
      }

      // TODO: if decayed add a transparent decay icon over top
    });
  });

  // Add the vector layer to the map
  map.addLayer(vLayer);
  map.addLayer(vLayerIcons);

  // Add feature selection events
  addFeatureSelectionEvents(map, vLayer);
});
ENDJS;

    Event::$data = $matches[1] . $new_js . $matches[2];
  }//end method

  public function _report_form() {
    Kohana::log('info', 'decayimage::_report_form()');
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

}//end class

new decayimage;
