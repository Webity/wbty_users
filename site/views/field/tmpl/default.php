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
            
	
					<li><?php echo JText::_('COM_WBTY_USERS_FORM_LBL_FIELDS_FIELD_TYPE'); ?>: <?php echo $this->item->field_types_; ?></li>
					<li><?php echo JText::_('COM_WBTY_USERS_FORM_LBL_FIELDS_LIST_VIEW'); ?>: <?php echo $this->item->list_view; ?></li>

</ul>