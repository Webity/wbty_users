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

jimport('joomla.application.component.controllerform');

/**
 * Control Panel controller class.
 */
class Wbty_usersControllerControlPanel extends JControllerForm
{

    function __construct() {
        $this->view_list = 'controlpanel';
        parent::__construct();
    }

}