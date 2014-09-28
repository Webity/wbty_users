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
?>

<ul class="itemlist">
            
	
					<li><?php echo JText::_('COM_WBTY_USERS_FORM_LBL_COMPONENT_ASSETS_ASSET_ID'); ?>: <?php echo $this->item->asset_id; ?></li>
					<li><?php echo JText::_('COM_WBTY_USERS_FORM_LBL_COMPONENT_ASSETS_NAME'); ?>: <?php echo $this->item->name; ?></li>
					<li><?php echo JText::_('COM_WBTY_USERS_FORM_LBL_COMPONENT_ASSETS_BASE_USER_GROUP'); ?>: <?php echo $this->item->base_user_group; ?></li>

</ul>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_users&component_id='.JRequest::getCmd('component_id').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="component_asset-form" class="form-validate form-horizontal">
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_users" />
    <input type="hidden" name="form_name" id="form_name" value="component_asset" />
    <?php echo JHtml::_('form.token'); ?>
</form>