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

jimport('wbty_components.controllers.wbtycontrollerform');

/**
 * field controller class.
 */
class Wbty_usersControllerfield extends WbtyControllerForm
{

    function __construct() {
        $this->view_list = 'fields';
        parent::__construct();
		
		$this->_model = $this->getModel();
    }
	
	function back() {
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);
	}
	
	function ajax_save() {
		$this->model = $this->getModel();
		$jinput = JFactory::getApplication()->input;
		$jform = $jinput->get('jform', array(), 'ARRAY');
		$data = $jform['field'];

		$return = array(json_encode($data));
		if (JSession::checkToken() && $id = $this->model->save($data, array())) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
			$helper = new wbty_usersHelperAjax;
			$form = $this->model->getForm(array(), true, 'jform', $id);

			$return['id'] = $id;
			$return['data'] = $helper->link_html('field', $id, $form);
			$return['token'] = JSession::getFormToken();
		} else {
			$return['error'] = "error";
			$return['token'] = JSession::getFormToken();
		}
		echo json_encode($return);
		exit();
	}

	function ajax_checkout() {
		$app = JFactory::getApplication();
		
		if (!$id = $app->input->get('id', 0)) {
			echo json_encode(array('error'=>'No id set'));
			exit();
		}

		$this->model = $this->getModel();
		$table = $this->model->getTable();

		$table->load($id);
		$checkout = $table->checkout(JFactory::getUser()->id);

		$return = array();
		if ($table->id == $id && $checkout) {
			$return['id'] = $id;
			$return['token'] = JSession::getFormToken();
		} else {
			$return['error'] = "Unable to load or checkout record";
			$return['token'] = JSession::getFormToken();
		}

		echo json_encode($return);
		exit();
	}

	function ajax_state() {
		$app = JFactory::getApplication();

		if (!$id = $app->input->get('id', 0)) {
			echo json_encode(array('error'=>'ID set incorrectly'));
			exit();
		}
		
		$state = $app->input->get('state_val', 0);
		if (!($state == 1 || $state == -2)) {
			echo json_encode(array('error'=>'Invalid state setting' . $state));
			exit();
		}

		$this->model = $this->getModel();
		$status = $this->model->publish($id, $state);

		$return = array();
		if ($status) {
			$return['id'] = $id;
			$return['token'] = JSession::getFormToken();
			$return['state'] = $state;
		} else {
			$return['error'] = "Unable to update state of item";
			$return['token'] = JSession::getFormToken();
		}

		echo json_encode($return);
		exit();
	}

	function ajax_order() {
		$app = JFactory::getApplication();
		if (!$ids = $app->input->get('ids', array(), 'ARRAY')) {
			echo json_encode(array('error'=>'IDs set incorrectly'));
			exit();
		}

		$this->model = $this->getModel();
		$status = $this->model->setOrder($ids);

		$return = array();
		if ($status) {
			$return['success'] = true;
		} else {
			$return['error'] = "Unable to reorder items";
		}

		echo json_encode($return);
		exit();
	}
	
	
	
	function extraFields() {
	
		$type = JRequest::getVar('field');
		$field_id = JRequest::getVar('id');
		
		if (!$type) {
			exit();
		} 
		
		$db =& JFactory::getDBO();
		$model = $this->getModel();
		$form = $model->getFieldForm($type);
		
		if (!$form) {
			echo "invalid-field-type";
			exit();
		}
		
		if ($type == 'J_SQL' || $type == 'J_CHECKBOXSQL') { // If the chosen field is an SQL type
			/*$session = JFactory::getSession();
			$com_id = $session->get('fscombuilder.component_id');
			
			// Prepare the table HTML
			$query = "SELECT id, table_display_name AS name FROM #__fscombuilder_tables WHERE component_id=$com_id";
			$db->setQuery($query);
			$tables = $db->loadAssocList();
			
			$table_html = '<select id="jfields_query" name="jfields[query]"><option value=""></option>';
			foreach ($tables as $table) {
				$table_html .= '<option value="'.$table['id'].'">'.$table['name'].'</option>';
			}
			$table_html .= '</select>';
			
			
			$query = "SELECT value FROM #__wbty_users_field_options WHERE field_id=".$db->quote($field_id)." AND NAME = 'query'";
			$db->setQuery($query);
			$table_id = $db->loadResult();
			
			JRequest::setVar('ajax_table_id', $table_id);
			$key_value_html = $this->getKeyValue(true);*/
		}
		
		if ($field_id) {
			$query = "SELECT * FROM #__wbty_users_field_options WHERE field_id=".$db->quote($field_id)." AND NAME != 'sql_value'";
			$db->setQuery($query);
			
			$field_vals = $db->loadAssocList();
			if ($field_vals) {
				foreach ($field_vals as $v) {
					if ($v['name'] == 'query') {
						$table_html = str_replace('<option value="'.$v['value'].'"', '<option value="'.$v['value'].'" selected="selected"', $table_html);
					}
					elseif ($v['name'] == 'key_field') {
						$key_value_html['key'] = str_replace('<option value="'.$v['value'].'"', '<option value="'.$v['value'].'" selected="selected"', $key_value_html['key']);
					}
					elseif ($v['name'] == 'value_field') {
						$key_value_html['value'] = str_replace('<option value="'.$v['value'].'"', '<option value="'.$v['value'].'" selected="selected"', $key_value_html['value']);
					}
					$form->setValue($v['name'], NULL, $v['value']);
				}
			}
		}
		
		echo "<div>";
		foreach ($form->getFieldset('fields') as $field) {
			
			if ( ($type == 'J_SQL'||$type == 'J_CHECKBOXSQL') && $field->__get('name') == 'jfields[query]') {
				$input = $table_html;
			} 
			//elseif ($type == 'J_SQL' && $field->__get('name') == 'jfields[key_field]') {
			//	echo $key_value_html['key']."</li>";
			//} 
			elseif ( ($type == 'J_SQL'||$type == 'J_CHECKBOXSQL') && $field->__get('name') == 'jfields[value_field]') {
				$input = $key_value_html['value'];
			} 
			else {
				$input = $field->__get('input');
			}
			
			if ($input) {
				echo "<div class='control-group'>".str_replace('<label', '<label class="control-label"', $field->__get('label')).'<div class="controls">'.$input."</div></div>";
			}
		}					
		
		echo "</div>";
		
		exit();
	}
	
	function getKeyValue($return = false) {
		$db = JFactory::getDBO();
		$table_id = JRequest::getVar('ajax_table_id');
		
		if(empty($table_id)) {
			return false;
		}
		
		$query = "SELECT field.id as id, (SELECT options.value FROM #__wbty_users_field_options as options WHERE options.field_id=field.id AND options.name='label') as name FROM #__wbty_users_fields as field WHERE field.table_id = $table_id";
		$db->setQuery($query);
		$fields = $db->loadAssocList();
		
		//$key_value_html['key'] = '<select id="jfields_key_field" name="jfields[key_field]">';
		$key_value_html['value'] = '<select id="jfields_value_field" name="jfields[value_field]">';
		foreach ($fields as $field) {
			//$key_value_html['key'] .= '<option value="'.$field['id'].'">'.$field['name'].'</option>';
			$key_value_html['value'] .= '<option value="'.$field['id'].'">'.$field['name'].'</option>';
		}
		//$key_value_html['key'] .= '</select>';
		$key_value_html['value'] .= '</select>';
		
		if ($return === true) {
			return $key_value_html;
		} else {
			//echo '<li><label>Key Field</label>'.$key_value_html['key'].'</li>';
			echo '<li><label>Value Field</label>"'.$key_value_html['value'].'</li>';
			exit();
		}
	}
	
}