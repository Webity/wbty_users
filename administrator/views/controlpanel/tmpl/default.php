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

?>

<div class="cpanel">
	<h2>Main Tasks</h2>
    <div class="icon-wrapper">
        
				<div class="btn cpanel-btn">
					<a href="index.php?option=com_wbty_users&view=users"><img src="<?php echo JURI::root(); ?>media/wbty_users/img/users.png" alt=""><span>Users</span></a>
				</div>
        <div class="clr"></div>
    </div>
    <h2 style="clear:left;">Configuration / Settings</h2>
    <div class="icon-wrapper">
				<div class="btn cpanel-btn">
					<a href="index.php?option=com_wbty_users&view=fields"><img src="<?php echo JURI::root(); ?>media/wbty_users/img/fields.png" alt=""><span>Fields</span></a>
				</div>
				<div class="btn cpanel-btn">
					<a href="index.php?option=com_wbty_users&view=components"><img src="<?php echo JURI::root(); ?>media/wbty_users/img/components.png" alt=""><span>Components</span></a>
				</div>
				<div class="btn cpanel-btn">
					<a href="index.php?option=com_wbty_users&view=component_assets"><img src="<?php echo JURI::root(); ?>media/wbty_users/img/component_assets.png" alt=""><span>Component Assets</span></a>
				</div>
        
        <div class="clr"></div>
    </div>
</div>