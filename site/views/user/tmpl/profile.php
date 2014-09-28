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

jimport('joomla.application.module.helper');

if (JDocumentHtml::countModules('wbty-user-sidebar-a or wbty-user-sidebar-b')) { 
	$sidebar = true; 
} else { 
	$sidebar = false; 
}

?>

<h2 class="title"><?php echo $this->item->name; ?></h2>

<div class="wbty-user-top">
	<?php $this->renderModules('wbty-user-top'); ?>
</div>

<div class="row-fluid">

	<div class="span<?php echo $sidebar ? 7 : 12; ?>">
		<ul>
			<li>Username: <?php echo $this->item->username; ?></li>
		<?php
			// as a basis, let's use the form to get all the fields to display
			foreach ($this->form->getFieldsets() as $fieldset) {
				foreach($this->form->getFieldset($fieldset->name) as $field) {
					if (!$field->__get('value') || strtolower($field->__get('type')) == 'hidden') {
						continue;
					}
					echo '<li>' . strip_tags($field->__get('label')) . ': ' . $field->__get('value') . '</li>';
				}
			}
		?>
		</ul>
	</div>
	<?php if ($sidebar) : ?>
		<div class="span5">
			<div class="wbty-user-sidebar-a">
				<?php $this->renderModules('wbty-user-sidebar-a'); ?>
			</div>
			<div class="wbty-user-sidebar-b">
				<?php $this->renderModules('wbty-user-sidebar-b'); ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<div class="wbty-user-bottom">
	<?php $this->renderModules('wbty-user-bottom'); ?>
</div>