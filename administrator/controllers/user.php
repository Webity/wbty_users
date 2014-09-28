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
 * user controller class.
 */
class Wbty_usersControllerUser extends WbtyControllerForm
{

    function __construct() {
        $this->view_list = 'users';
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
		$data = $jform['user'];

		$return = array(json_encode($data));
		if (JSession::checkToken() && $id = $this->model->save($data, array())) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
			$helper = new wbty_usersHelperAjax;
			$form = $this->model->getForm(array(), true, 'jform', $id);

			$return['id'] = $id;
			$return['data'] = $helper->link_html('user', $id, $form);
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
	
	function edit($key = null, $urlVar = null) {
		// check for non-existant records, that exist in the main user table

		$app   = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$cid   = $this->input->post->get('cid', array(), 'array');

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->input->getInt($urlVar));

		if ($recordId) {
			// verify that we don't have a record. If we do, this will get reloaded later
			$record = $table->load($recordId);
			if (!$record) {
				// if no record, confirm that the user is set in the main user table
				$db = JFactory::getDbo();

				$query = $db->getQuery(true);

				$query->select('id')
					->from('#__users')
					->where('id='.$recordId);

				$id = $db->setQuery($query)->loadResult();

				// if the user exists, add an empty record in our table for that user, so it can be checked out in the parent function
				if ($id) {
					$obj = new stdClass;
					$obj->id = $id;

					$db->insertObject('#__wbty_users_users', $obj);
				}
			}
		}

		return parent::edit($key, $urlVar);
	}
	
}