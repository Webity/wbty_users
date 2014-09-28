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
jimport('wbty_components.tables.wbtytableversioning');

/**
 * component_asset Table class
 */
class Wbty_usersTablecomponent_assets extends WbtyTableVersioning
{
	
	public function __construct(&$db)
	{
		parent::__construct('#__wbty_users_component_assets', 'id', $db);
	}

}
