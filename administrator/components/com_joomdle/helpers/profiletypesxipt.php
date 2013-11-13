<?php
/**
 * @version		
 * @package		Joomdle
 * @subpackage	Content
 * @copyright	Copyright (C) 2008 - 2010 Antonio Duran Terres
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');
require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'content.php');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomdle
 * @since 1.5
 */
class JoomdleHelperProfiletypesxipt
{

	function getProfiletypes ($filter_type, $limitstart, $limit, $filter_order, $filter_order_Dir, $search)
	{
                $db           =& JFactory::getDBO();

		$wheres = array ();
		if ($filter_type)
		{
			/* kludge to use 0 as a value */
			if ($filter_type == -1)
				$filter_type = 0;
			$wheres[] = "create_on_moodle = ". $db->Quote($filter_type);
		}

		if ($search)
		{
			$wheres_search[] = "joomla_field = ". $db->Quote($search);
			$wheres_search[] = "moodle_field = ". $db->Quote($search);
			$wheres[] = "(name LIKE  ". $search .")";
		}

		$query = "SELECT * from #__xipt_profiletypes";
	//	$query = 'SELECT j.id, name, create_on_moodle 
	//		FROM #__xipt_profiletypes as x, #__joomdle_profiletypes as j 
	//		where x.id = j.profiletype_id';

		if(! empty($wheres)){
                   $query .= " AND ".implode(' AND ', $wheres);
                }

		$query .= " ORDER BY ".  $filter_order  ." ". $filter_order_Dir;

		if(! empty($limit)){
                   $query .= " LIMIT $limitstart, $limit";
                }

		$db->setQuery($query);
                $profiletypes = $db->loadObjectList();

		if (!$profiletypes)
			return NULL;

		foreach ($profiletypes as $profiletype)
		{
			$profiletype->published = JoomdleHelperProfiletypes::create_this_type ($profiletype->id);
			$profiletype->create_on_moodle = $profiletype->published;
			$m[] = $profiletype;
		}

		return $m;
	}

	function get_profiletype_data ($id)
    {
        $db           =& JFactory::getDBO();

        $query = "SELECT * from #__xipt_profiletypes" .
                " WHERE id = ". $db->Quote($id);

        $db->setQuery($query);
        $profiletype = $db->loadObject();

        return $profiletype;
    }

    function get_user_profile_id ($user_id)
    {
        $db           =& JFactory::getDBO();

        $query = "SELECT profiletype from #__xipt_users" .
                " WHERE userid = ". $db->Quote($user_id);

        $db->setQuery($query);
        $profiletype = $db->loadResult();

        return $profiletype;
    }

    function add_user_to_profile ($user_id, $profile_id)
    {
        $db           =& JFactory::getDBO();

        $query = "UPDATE #__xipt_users" .
                " SET profiletype = " . $db->Quote($profile_id) .
                " WHERE userid = ". $db->Quote($user_id);

        $db->setQuery($query);

        if (!$db->query()) {
            return JError::raiseWarning( 500, $db->getError() );
        }

    }
	


}
