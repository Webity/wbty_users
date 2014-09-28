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

/**
 * Wbty_users helper.
 */
class Wbty_usersHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		
		
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_wbty_users';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
	
	public static function registerComponent($name, $base_user_group = '', $user_form = '') {
		$component = JComponentHelper::getComponent($name);
		
		if (!$component->id) {
			return false;
		}
		
		$data = new stdClass;
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('id')
			->from('#__wbty_users_components')
			->where('name="'.$name.'"');
		
		$data->id = $db->setQuery($query, 0, 1)->loadResult();
		$data->name = $name;
		
		$query->clear()
			->select('id')
			->from('#__assets')
			->where('name="'.$name.'"');
		
		$data->asset_id = $db->setQuery($query, 0, 1)->loadResult();
		
		if ($base_user_group) {
			$data->base_user = $base_user_group;
		}
		if ($user_form) {
			$data->user_form = $user_form;
		}
		
		if ($data->id) {
			$result = $db->updateObject('#__wbty_users_components', $data, 'id');
		} else {
			$result = $db->insertObject('#__wbty_users_components', $data);
		}
		
		return $result;
	}
	
	/* $asset should be the id of an asset that is a section of the component. */
	public static function registerAsset($asset, $component, $base_user_group = '', $user_form = '') {
		$component = JComponentHelper::getComponent($component);
		
		if (!$component->id) {
			return false;
		}
		
		$data = new stdClass;
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('id')
			->from('#__wbty_users_components')
			->where('name="'.$component->option.'"');
		
		$data->component_id = $db->setQuery($query, 0, 1)->loadResult();
		
		$query->clear()
			->select('id')
			->from('#__wbty_users_component_assets')
			->where('asset_id="'.$asset.'"');
		
		$data->id = $db->setQuery($query, 0, 1)->loadResult();
		
		$data->asset_id = $asset;
		
		if ($base_user_group) {
			$data->base_user_group = $base_user_group;
		}
		if ($user_form) {
			$data->user_form = $user_form;
		}
		
		if ($data->id) {
			$return = $db->updateObject('#__wbty_users_component_assets', $data, 'id');
		} else {
			$return = $db->insertObject('#__wbty_users_component_assets', $data);
		}
		
		return $return;
	}
	
	public function accessCheck() {
		$user 	=& JFactory::getUser();
		$input 	= JFactory::getApplication()->input;
		$task 	= $input->get('task');
		$view 	= $input->get('view');
		$id 	= $input->get('id');
		
		if ($user->guest && ($view == 'user' || $view = 'login')) {
			
			$app =& JFactory::getApplication();
			
			// check first if form has been submitted to register or login
			if ($input->post->get('username')) {
				
				require_once(JPATH_COMPONENT . DS . "helpers" . DS . "users.php");

				// push redirect to return to mimic com_users
				if ($redirect = $input->get('redirect', '', 'string')) {
					JRequest::setVar('return', $redirect);
				}
				
				if ($input->post->get('email1')) {
					// create user
					if (!JHtmlComUsers::saveUserForm()) {
						JError::raiseWarning( 100, 'User could not be created.' );
					}
					
					// check for redirect data
					if ($redirect = $input->get('redirect', '', 'string')) {
						$app->redirect(JRoute::_(base64_decode($redirect)), false);
						return false;
					}
				} else {
					// log user in
					if (JHtmlComUsers::processLogin() !== true) {
						return false;
					}
					// check for redirect data
					if ($redirect = $input->get('redirect', '', 'string')) {
						$app->redirect(JRoute::_(base64_decode($redirect)), false);
						return false;
					}
				}
			}
			
			// recheck user
			$user =& JFactory::getUser();
			if ($user->guest) {
				// user needs to be logged in
				$app->enqueueMessage('Please login or create an account.');
				$input->set('view', 'login');
				$input->set('task', '');
				return false;
			}
		}
		return true;
	}
}

class JHTMLWbty_usersHelper {
	

	public static function buildEditForm($form, $hidden = true) {
		if (!$form instanceof JForm) {
			return false;
		}

		ob_start();
		foreach ($form->getFieldsets() as $fieldset) {
			echo '<fieldset name="'.$fieldset->name.'"';
			$class = array();
			$field = $value = '';
			if ($fieldset->multiple) {
				 $class[] = 'multiple';
			}
			if ($fieldset->dependency) {
				$class[] = 'dependency';
				$field = $fieldset->field;
				$value = $fieldset->value;
			}
			if ($class) {
				echo ' class="'. implode(' ', $class) . '"';
			}
			if ($field && $value) {
				echo ' data-field="'. $field . '" data-value="'. $value . '"';
			}
			if ($fieldset->copy) {
				echo ' data-copy="' . $fieldset->copy . '"';
			}
			echo '>';
			if ($fieldset->legend) {
				echo '<legend>'.$fieldset->legend.'</legend>';
			}
			if ($fieldset->soc) {
				echo '<p>This section should have a search or create option. Only one is currently shown.</p>';
			}
			//echo '<div class="edit-values">';
			foreach($form->getFieldset($fieldset->name) as $field):
				if (!$field->hidden && $field->display_value) {
				//	echo strip_tags($field->label) . ': <span class="' . str_replace(array('[',']'), array('_'),$field->name) . '">' . $field->value . '</span><br>';
				}
			endforeach;
			echo '<!--</div>-->
			<div class="edit-form">';
			foreach($form->getFieldset($fieldset->name) as $field):
				// If the field is hidden, only use the input.
				if ($field->hidden):
					echo $field->input;
				else:
				?>
				<div class="control-group">
					<?php echo str_replace('<label', '<label class="control-label"', $field->label); ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php
				endif;
			endforeach;
			echo '</div>';
			echo '</fieldset>';
		}
		$html = ob_get_contents();
		ob_end_clean();

		if ($hidden) {
			$html = '<div style="display:none;" id="hidden-forms">'.$html.'</div>';
		}

		return $html;
	}
}
