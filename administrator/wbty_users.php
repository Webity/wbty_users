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

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_wbty_users')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');
jimport('legacy.controller.legacy');

if (!class_exists(JControllerLegacy)) {
	class JControllerLegacy extends JController {}
}

// Include base css and javascript files for the component
// Import CSS
$document = &JFactory::getDocument();
if ($_SERVER['REQUEST_URI']) {
	$document->setBase($_SERVER['REQUEST_URI']);
}

$jversion = new JVersion();
$above3 = version_compare($jversion->getShortVersion(), '3.0', 'ge');

JHTML::stylesheet('wbty_users/wbty_users.css', false, true);

if ($above3) {
	JHtml::_('bootstrap.framework');
	JHTML::stylesheet('wbty_components/ui-lightness/jquery-ui-1.10.3.custom.min.css', false, true);
	JHTML::script('wbty_components/jquery-ui-1.10.3.custom.min.js', false, true);
	if (JFactory::getApplication()->isAdmin()) {}
} else {
	JHTML::stylesheet('wbty_components/ui-lightness/jquery-ui-1.10.3.custom.min.css', false, true);
	JHTML::stylesheet('wbty_components/bootstrap.min.css', false, true);
	JHTML::stylesheet('wbty_components/font-awesome.min.css', false, true);
	JHTML::script('wbty_components/jquery-1.10.2.min.js', false, true);
	JHTML::script('wbty_components/jquery-ui-1.10.3.custom.min.js', false, true);
	JHTML::script('wbty_components/bootstrap.min.js', false, true);
}


// Import Javascript

$controller	= JControllerLegacy::getInstance('Wbty_users');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
