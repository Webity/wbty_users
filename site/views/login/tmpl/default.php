<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

if ($redirect = JFactory::getApplication()->input->get('redirect', '', 'STRING')) {
	if (strpos($redirect, '://') !== FALSE) {
		$redirect = base64_encode($redirect);
	}
	$redirect = '<input type="hidden" value="'. $redirect . '" name="redirect" />';
} else {
	$redirect = '';
}
?>
<div class="row-fluid">
    <div id="login" class="span6">
    <?php
    require_once(JPATH_COMPONENT . DS . "helpers" . DS . "users.php");
    $login = JHtmlComUsers::buildUserLogin();
    $form = JHtmlComUsers::defaultLogin($login, 'com_wbty_users', 'login');
    
	 
	$search_array = array('<legend',
						  'COM_USERS_LOGIN_DEFAULT_LABEL',
						  'type="submit"',
						  '</form>'
						  );
	$replace_array = array('<legend class="wbty-users-header"',
						   'Login',
						   'type="submit" class="btn btn-primary"', 
						   $redirect . '</form>'
						   );
	
    echo str_replace($search_array, $replace_array,$form);
    ?>
    </div>
    
    <div id="signup" class="span6">
    <?php
    require_once(JPATH_COMPONENT . DS . "helpers" . DS . "users.php");
    $registration = JHtmlComUsers::buildUserForm();
    $form = JHtmlComUsers::defaultTemplate($registration, 'com_wbty_users', 'register');
    
	$search_array = array('<legend',
						  'type="submit"',
						  'title="Cancel"',
						  '</form>'
						  );
	$replace_array = array('<legend class="wbty-users-header"',
						   'type="submit" class="btn btn-primary"', 
						   'class="btn btn-error" title="Cancel"',
						   $redirect . '</form>'
						   );
	
    echo str_replace($search_array, $replace_array, $form);
    ?>
    </div>
</div>