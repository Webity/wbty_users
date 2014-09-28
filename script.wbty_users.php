<?php
/**
 * @package AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: ars.php 123 2011-04-13 07:47:16Z nikosdion $
 */

class com_wbty_usersInstallerScript {
	
	function update($parent) {
	}
	
	function postflight($type, $parent) {
		require_once('install.wbty_users.php');
		if($type == "install"){
			if(method_exists($parent, 'extension_root')) {
				$configfile = $parent->getPath('extension_root').'/config.xml';
			} else {
				$configfile = $parent->getParent()->getPath('extension_root').'/config.xml';
			}
			if (file_exists($configfile)) {
				$xml = file_get_contents($configfile);
				$form = JForm::getInstance('installer', $xml, array(), false, '/config');
				
				$params = array();
				if ($form->getFieldset('component')) {
					foreach ($form->getFieldset('component') as $field) {
						$params[$field->__get('name')] = $field->__get('value');
					}
				}
				$db = JFactory::getDBO();
				$query = "UPDATE #__extensions SET params='".json_encode($params)."' WHERE element='".$parent->get('element')."'";
				$db->setQuery($query)->query();
			}
		}
	}
}