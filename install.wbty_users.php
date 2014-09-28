<?php
/**
 * @package Wbty_users
 * @copyright Copyright (C) 2012-2013. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @author Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

// Install modules and plugins -- BEGIN

// -- General settings
jimport('joomla.installer.installer');
$db = & JFactory::getDBO();
$status = new JObject();
$status->libraries = array();
$status->modules = array();
$status->plugins = array();
if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
	// Thank you for removing installer features in Joomla! 1.6 Beta 13 and
	// forcing me to write ugly code, Joomla!...
	$src = dirname(__FILE__);
} else {
	$src = $this->parent->getPath('source');
}

// install wbty_components if not installed
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select('extension_id')->from('#__extensions')->where('element=\'wbty_components\'')->where('type=\'library\'')->where('enabled=1');
$already_installed = $db->setQuery($query)->loadResult();
if (!$already_installed) {
	// download and install
	jimport('joomla.updater.update');
	$xml = new JUpdate();
	$xml->loadFromXML("http://wbty.co/com_wbty_components.xml");

	if ($xml->downloadurl->_data) {
		$tmp_path = rtrim(JFactory::getApplication()->getCfg('tmp_path'), '/') . '/wbty_components.zip';
		file_put_contents($tmp_path, fopen($xml->downloadurl->_data, 'r'));

		$zip = new ZipArchive;
		$res = $zip->open($tmp_path);
		$tmp_folder = substr($tmp_path, 0, -4);
		if ($res === TRUE) {
			$zip->extractTo($tmp_folder);
			$zip->close();

			$installer = new JInstaller;
			$wbty_components = $installer->install($tmp_folder);
		}
	}
}

if(is_dir($src.'/libraries')) {
	$libraries = JFolder::folders($src.'/libraries', '.', false, false);
	foreach ($libraries as $library) {
		$installer = new JInstaller;
		$result = $installer->install($src.'/libraries/'.$library);
		$status->libraries[] = array('name'=>$library,'result'=>$result);
	}
}
if(is_dir($src.'/modules')) {
	$modules = JFolder::folders($src.'/modules', '.', false, false);
	foreach ($modules as $module) {
		$installer = new JInstaller;
		$result = $installer->install($src.'/modules/'.$module);
		$status->modules[] = array('name'=>$module,'result'=>$result);
	}
}
if(is_dir($src.'/plugins')) {
	$plugins = JFolder::folders($src.'/plugins', '.', false, false);
	foreach ($plugins as $plugin) {
		$installer = new JInstaller;
		$result = $installer->install($src.'/plugins/'.$plugin);
		$status->plugins[] = array('name'=>$plugin,'result'=>$result);
	}
}

// Install libraries, modules, and plugins -- END

// Finally, show the installation results form
?>
<h1>Wbty_users</h1>

<h2>Welcome!</h2>

<p>Thank you for installing Wbty_users.</p>

<?php if ($wbty_components) : ?>
	<p>WBTY Components Library and Plugin were also downloaded and installed</p>
<?php endif; ?>

<?php if (count($status->libraries)) : ?>
	<h3>Libraries</h3>
	<?php foreach ($status->libraries as $library) : ?>
	    <p><?php echo $library['name']; ?> - <?php echo ($library['result'])?JText::_('Installed'):JText::_('Not installed'); ?></p>
	<?php endforeach;?>
<?php endif; ?>

<?php if (count($status->modules)) : ?>
	<h3>Modules</h3>
	<?php foreach ($status->modules as $module) : ?>
	    <p><?php echo $module['name']; ?> - <?php echo ucfirst($module['client']); ?> - <?php echo ($module['result'])?JText::_('Installed'):JText::_('Not installed'); ?></p>
	<?php endforeach;?>
<?php endif; ?>

<?php if (count($status->plugins)) : ?>
	<h3>Plugins</h3>
	<?php foreach ($status->plugins as $plugin) : ?>
	    <p><?php echo $plugin['name']; ?> - <?php echo ucfirst($plugin['client']); ?> - <?php echo ($plugin['result'])?JText::_('Installed'):JText::_('Not installed'); ?></p>
	<?php endforeach;?>
<?php endif; ?>
