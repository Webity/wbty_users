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

if($this->forms) : ?>
	<?php foreach ($this->forms as $name => $form) { ?>
		<form method="post" action="index.php?option=com_wbty_users&view=<?php echo substr($name, 0, -7); ?>" class="form-validate form-horizontal">
        <h2>Search <?php echo ucwords(str_replace('_', ' ', substr($name, 0, -7))); ?></h2>
			<?php
			$fieldsets = $form->getFieldsets();
			foreach ($fieldsets as $fieldset) {
				foreach ($form->getFieldset($fieldset->name) as $field) {
					echo '<div class="control-group">'.str_replace('<label', '<label class="control-label"', $field->__get('label')).'<div class="controls">'.$field->__get('input').'</div></div>';
				}
			}
			?>
            
            <div class="control-group">
                <div class="controls">
                  <input type="submit" class="button" value="Submit" />
                </div>
            </div>
        </form>
    <?php } ?>
    
<?php endif; ?>