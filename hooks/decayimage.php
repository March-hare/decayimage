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

class decayimage {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
    // TODO: we need to find a way to hook into application/controllers/json.php
		Event::add('ushahidi_filter.header_js', array($this, 'decayimage_ushahidi_filter_header_js'));
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function decayimage_ushahidi_filter_header_js_single()
	{
    Kohana::log('info', 'DEBUG: decay image hook');
    // Append a new showIncidentMap function to the end of the file
    preg_match(':^(.+)(//-->\s*</script>\s*)$:s', Event::$data, $matches);
    $new_js = '
    var showIncidentMapOrig = showIncidentMap;
    showIncidentMap = (function() {
      // URL to be used for fetching the incidents
      fetchURL = "'. url::site() .'json/index";
      
      // Generate the url parameter string
      parameterStr = makeUrlParamStr("", urlParameters);
      
      // Add the parameters to the fetch URL
      fetchURL += "?" + parameterStr;
      
      // Fetch the incidents
      
      // Set the layer name
      var layerName = "'. Kohana::lang('ui_main.reports') .'";
          
      // Get all current layers with the same name and remove them from the map
      currentLayers = map.getLayersByName(layerName);
      for (var i = 0; i < currentLayers.length; i++)
      {
        map.removeLayer(currentLayers[i]);
      }
          
      // Styling for the incidents
      reportStyle = new OpenLayers.StyleMap({
        pointRadius: "10",
        fillColor: "#30E900",
        fillOpacity: "0.8",
        strokeColor: "#197700",
        strokeWidth: 3,
        graphicZIndex: 1,
        graphic: true,
        graphicWidth: 18,
        // This depends on a modified /application/controllers/json:index() 
        // to correctly set the icon variable in the json output
        externalGraphic: "${icon}"
      });

      // Apply transform to each feature before adding it to the layer
      preFeatureInsert = function(feature)
      {
        var point = new OpenLayers.Geometry.Point(feature.geometry.x, feature.geometry.y);
        OpenLayers.Projection.transform(point, proj_4326, proj_900913);
      };
          
      // Create vector layer
      vLayer = new OpenLayers.Layer.Vector(layerName, {
        projection: map.displayProjection,
        extractAttributes: true,
        styleMap: reportStyle,
        strategies: [new OpenLayers.Strategy.Fixed()],
        protocol: new OpenLayers.Protocol.HTTP({
          url: fetchURL,
          format: new OpenLayers.Format.GeoJSON()
        })
      });
          
      // Add the vector layer to the map
      map.addLayer(vLayer);
      
      // Add feature selection events
      addFeatureSelectionEvents(map, vLayer);
	});
      ';
    Event::$data = $matches[1] . $new_js . $matches[2];
  }

	public function decayimage_ushahidi_filter_header_js()
	{
    // Append a new showIncidentMap function to the end of the file
    preg_match(':^(.+)(//-->\s*</script>\s*)$:s', Event::$data, $matches);
    $layerName = Kohana::lang('ui_main.reports');
    $site = url::site();

$new_js = <<<ENDJS
var showIncidentMapOrig = showIncidentMap;
  showIncidentMap = (function() {
  //return showIncidentMapOrig();

  // Set the layer name
  var layerName = "{$layerName}";
      
  // Get all current layers with the same name and remove them from the map
  currentLayers = map.getLayersByName(layerName);
  for (var i = 0; i < currentLayers.length; i++)
  {
    map.removeLayer(currentLayers[i]);
  }

  // Default styling for the incidents
  var reportStyle = OpenLayers.Util.extend({}, 
    OpenLayers.Feature.Vector.style["default"]);

  reportStyle.pointRadius = 8;
  reportStyle.fillColor = "#30E900";
  reportStyle.fillOpacity = "0.8";
  reportStyle.strokeColor = "#197700";
  // Does this make the total point radius = 8+3/2?
  reportStyle.strokeWidth = 3;
  reportStyle.graphicZIndex = 2;
      
  // create simple vector layer
  var vLayer = new OpenLayers.Layer.Vector(layerName, {
    projection: new OpenLayers.Projection("EPSG:4326"),
    style: reportStyle,
    rendererOptions: {zIndexing: true}
  });
      
  // URL to be used for fetching the incidents
  fetchURL = "{$site}json/index";
  
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

      // create a feature vector from the point and style
      var feature = new OpenLayers.Feature.Vector(incidentPoint, null, reportStyle);
      vLayer.addFeatures([feature]);

      var incidentStyle =  OpenLayers.Util.extend({}, reportStyle);
      incidentStyle.graphicOpacity = 1;
      incidentStyle.graphicZIndex = 1;
      incidentStyle.graphic = true;
      incidentStyle.graphicHeight = 25;

      var offsetRadius = reportStyle.pointRadius+incidentStyle.graphicHeight/2;
      // if the icon is set then apply it (this requires controller mod)
      // else if icon is an array, then place the icons around the incident
      if (val.properties.icon instanceof Array) {
        var numIcons = val.properties.icon.length;
        var iconCt = 1;
        // Loop over each icon setting externalGraphic and x,y offsets
        $.each(val.properties.icon, function(index, icon) {
          
          // TODO: -13 is a magic number here that got this working.
          // I dont totally understant what its related to.
          // pointRadius + strokeWidth + 2FunPixels?
          incidentStyle.externalGraphic = icon;
          incidentStyle.graphicXOffset = -13+
            offsetRadius*Math.cos(((2*3.14)/(numIcons))*index);
          incidentStyle.graphicYOffset = -13+
            offsetRadius*Math.sin(((2*3.14)/(numIcons))*index);

          iconPoint = incidentPoint.clone();
          var feature = new OpenLayers.Feature.Vector(
            iconPoint, null, incidentStyle);
          vLayer.addFeatures([feature]);
        });
      }
      // If icon is a single value (this is the protocol default)
      else if (val.properties.icon) {
        incidentStyle.externalGraphic = val.properties.icon;
        incidentStyle.graphicYOffset = offsetRadius;

        // create a feature vector from the point and style
        var feature = new OpenLayers.Feature.Vector(incidentPoint, null, reportStyle);
        vLayer.addFeatures([feature]);
      }

      // TODO
      // if decayed add a transparent decay icon over top
      //
      // add the feature to the layer
    });
  });

  // Add the vector layer to the map
  map.addLayer(vLayer);
  
  // Add feature selection events
  addFeatureSelectionEvents(map, vLayer);
});
ENDJS;

    Event::$data = $matches[1] . $new_js . $matches[2];
  }//end method

}//end class

// We only want this called for the reports view
$url = url::current();
if ($url != 'reports') {
  return;
}
new decayimage;
