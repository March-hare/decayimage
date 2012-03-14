<?php defined('SYSPATH') or die('No direct script access.');
/**
 * English language file for Decay Image plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communications Collective <info@march-hare.org>
 * @module	   Decayimage
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

	$lang = array(		
		"decayimage"=>"Decay Image",
		"decayimage_settings" => "Decay Image Settings",
    "show_decayimage"=>"Show Decay Image:",
    "decayimage_has_been"=>"Decay image as been ",
		"default_incident_icon"=>"Default Incident Icon",
    'remain_on_map' => 'Keep marker on map after endtime?',
    'remove_from_map' => 'Remove marker from map after endtime?',
    'decay_from_map' => 'Marker image should decay after endtime?',
    'will_remain_on_map' => 'Marker will remain on map after endtime',
    'remove_from_map' => 'Marker will be removed from map after endtime',
    'cant_del_default' => 'The default decayimage can not be deleted',
    'restore' => 'Restore',
		"yes"=>"Yes",
    "no"=>"No",
    "added"=>"Added",
    "updated"=>"Updated",
    'decayimage_thumb' => array(
      'length' => 'The file name for the thumbnail has to be between 5 and 255 characters',
      'invalid_decayimage_thumb' => 'There was an internal problem encountered when procesing your request.  Please try again.'
    ),
    'decayimage_id' => array(
      'invalid_decayimage_id' => 'An invalid decay image id was passed in',
      'required' => 'There was an internal problem encountered when procesing your request.  Please try again.'
    ),
    'category_id' => array(
      'required' => 'There was an internal problem encountered when procesing your request.  Please try again.'
    ),
    'category' => array(
      'did_not_find_category_image_for_grayscale' => 'There was an internal system error.  Please try again.',
      'did_not_find_category_for_grayscale' => 'There was an internal system error.  Please try again.',
      'invalid_image_type' => 'The decayimage module did not create a decayimage for this categpry icon because it was not of type PNG.'
    )
	);
?>
