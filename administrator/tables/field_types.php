<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// No direct access
defined('_JEXEC') or die;
jimport('wbty_components.tables.wbtytable');

/**
 * field_type Table class
 */
class Wbty_usersTablefield_types extends WbtyTable
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__wbty_users_field_types', 'id', $db);
	}

}
