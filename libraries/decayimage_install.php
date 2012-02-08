<?php
/**
 * Performs install/uninstall methods for the decayimage plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   March-Hare Communications Collective <info@march-hare.org>
 * @module	   Decayimage
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Decayimage_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the decayimage plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query("CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."decayimage` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `category_id` int(11) NOT NULL,
				  `decayimage_image` varchar(255) NOT NULL,
          `decayimage_thumb` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");


    $decayimage = ORM::factory('decayimage')
      ->where(array('category_id' => 0))
      ->find();

    if (!$decayimage->count_last_query()) {
      $this->db->query("INSERT INTO `".Kohana::config('database.default.table_prefix')."decayimage` (
        `category_id`, 
        `decayimage_image`, 
        `decayimage_thumb`)
        VALUES (0, 'Question_icon.png', 'Question_icon_thumb.png')");
    }
	}

	/**
	 * Deletes the database tables for the decayimage module
	 */
	public function uninstall()
  {

		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'decayimage`');
  }

}
