<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

/**
 * @param	array	A named array
 * @return	array
 */
function Wbty_usersBuildRoute(&$query)
{
	$segments = array();

	$app =& JFactory::getApplication();
	$menu		= $app->getMenu();
	$active = $menu->getActive();
	
	// hack to protect the actual current item as well as the search module or other places that use JRoute::_('index.php');
	if (count($query)==2 && $query['option'] && $query['Itemid']) {
		return $segments;
	}
	
	// start with no match found
	$match = false;
	$match_level = 0;
		
	// we want to find a menu item for this if possible. If the active menu item is the current menu item then we should see if there is a better match.
	if (empty($query['Itemid']) || ($query['Itemid'] == $active->id && empty($query['task']))) {
		// load all menu items
		$items = $menu->getMenu();
		
		// use the current item over others if it ties for the best match
		if ($active->query['option'] == $query['option']) {
			$match_level = 1;
			$match = $active;
			if ($active->query['view'] == $query['view']) {
				$match_level = 2;
				if ( !$query['id'] && !$active->query['id'] ) {
					$match_level = 2.5;
				}
				if ($active->query['layout'] == $query['layout'] || ($query['layout']=='default' && !$active->query['layout'])) {
					$match_level = 3;
					if ($query['id'] && !$active->query['id']) {
						$match_level = 3.5;
					}
					if ($active->query['id'] == $query['id']) {
						$match_level = 4;
					}
				}
			}
		}
		
		// loop through each menu item in order
		foreach ($items as $item) {
			
			// base check is that it is for this component
			// then cycle through each possibility finding it's match level
			if ($item->query['option'] == $query['option']) {
				$item_match = 1;
				if ($item->query['view'] == $query['view']) {
					$item_match = 2;
					if (!$query['id'] && !$item->query['id']) {
						$item_match = 2.5;
					}
					if (!$query['layout'] && $item->query['layout']) {
						$query['layout'] = 'default';
					}
					if ($item->query['layout'] == $query['layout'] || ($query['layout']=='default' && !$item->query['layout'])) {
						$item_match = 3;
						if ($query['id'] && !$item->query['id']) {
							$item_match = 3.5;
						}
						if ($item->query['id'] == $query['id']) {
							$item_match = 4;
						}
					}
				}
			}

			// if this item is a better match than our current match, set it as the best match
			if ($item_match > $match_level) {
				$match = $item;
				$match_level = $item_match;
			}
			
		}
		
		// if there is a match update Itemid to match that menu item
		if ($match) {
			$query['Itemid'] = $match->id;
			$menuItem = $menu->getItem($match->id);
		} else {
			$menuItem = $menu->getActive();
		}
	}
	
	if (in_array($query['view'], array('listings', 'customers', 'sales'))) {
		$query['reset'] = 0;
	}
	
	if ($match_level > 1) {
		$view = $query['view'];
		unset($query['view']);
	} elseif (isset($query['view'])&& strpos($query['view'],'.')===FALSE) {
		// by supporting tasks we do not support views with a period in them. Don't do it.
		$segments[] = $query['view'];
		unset($query['view']);
	} elseif (isset($query['task']) && strpos($query['task'],'.')!==FALSE) {
		// we can place a task in the view's position as long as it has a period in it to distinguish. Also you can't set a view and task without the task not being parsed
		$segments[] = $query['task'];
		if (!$query['id']) {
			$query['id'] = 0;
		}
		unset($query['task']);
	} else {
		// skip parsing if no view or task is set. View is required
		return $segments;
	}
	if (isset($query['layout'])) {
		if ($match_level < 3) {
			if ($match_level > 1) {
				// view is set above from $query['view']
				$segments[] = $view;
			}
			$segments[] = $query['layout'];
		}
		unset($query['layout']);
	}
	if (isset($query['id'])) {
		if ($match_level < 4) {
			$segments[] = $query['id'];
		}
		unset($query['id']);
	}
	if (isset($query['format'])) {
		$segments[] = $query['format'];
		unset($query['format']);
	}
	
	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/banners/task/id/Itemid
 *
 * index.php?/banners/id/Itemid
 */
function Wbty_usersParseRoute($segments)
{
	$vars = array();
	$app =& JFactory::getApplication();
	$menu		= $app->getMenu();
	$active = $menu->getActive();
	$vars = $active->query;
	
	// view is always the first element of the array
	$count = count($segments);

	if ($count)
	{
		$count--;
		$segment = array_shift($segments);
		if (is_numeric($segment)) {
			$vars['id'] = $segment;
		} elseif ($segment == 'default' || $segment == 'edit' || $segment == 'new') {
			$vars['layout'] = $segment;
		} elseif (strpos($segment, '.')===FALSE) {
			$vars['view'] = $segment;
		} else {
			$vars['task'] = $segment;
		}
	}

	$idset = false; 
	while ($count)
	{
		$count--;
		$segment = array_shift($segments) ;
		if (is_numeric($segment)) {
			$idset = true;
			$vars['id'] = $segment;
		} else {
			if (!$idset) {
				$vars['layout'] = $segment;
			} else {
				$vars['format'] = $segment;
			}
		}
	}
	
	return $vars;
}
