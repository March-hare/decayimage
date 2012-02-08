<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model for Decayimage
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communications Collective <info@march-hare.org>
 * @module	   Decayimage
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Decayimage_Model extends ORM
{
	protected $belongs_to = array('category');
	
	// Database table name
	protected $table_name = 'decayimage';
}
