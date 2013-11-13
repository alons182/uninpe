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
require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'system.php');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomdle
 * @since 1.5
 */
class JoomdleHelperAcymailing
{

    function save_list ($name, $description)
    {
		require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_acymailing'.'/'.'helpers'.'/'.'helper.php');

        $db           =& JFactory::getDBO();

		$list->name = $name;
		$list->alias = JFilterOutput::stringURLSafe (trim ($name));
		$list->description = $description;
		$list->published = 1;
		$list->visible = 1;
		$list->userid = JoomdleHelperSystem::get_admin_id ();
		$list->color = '#ffcc66';

		$status = $db->insertObject(acymailing_table('list'),$list);
		$insert_id = $db->insertid();

		// Re-order
		$orderClass = acymailing_get('helper.order');
		$orderClass->pkey = 'listid';
		$orderClass->table = 'list';
		$orderClass->groupMap = 'type';
		$orderClass->groupVal = 'list'; //empty($list->type) ? $this->type : $list->type;
		$orderClass->reOrder();

		return $insert_id;
    }   

	function add_sub ($list_id, $user_id)
	{
		require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_acymailing'.'/'.'helpers'.'/'.'helper.php');

        $db           =& JFactory::getDBO();

		$sub_id = JoomdleHelperAcymailing::get_sub_id ($user_id);

		// Should not happen after syncing joomla users at acymailing install
		if (!$sub_id)
			return 0;

		$listsub->listid = $list_id;
		$listsub->subid = $sub_id;
		$listsub->subdate = time();
		$listsub->status = 1;

		$status = $db->insertObject(acymailing_table('listsub'),$listsub);

		return $status;
	}

	function get_sub_id ($user_id)
	{
        $db           =& JFactory::getDBO();

		$query = 'SELECT subid' .
                ' FROM #__acymailing_subscriber' .
                ' WHERE userid='.$db->Quote( $user_id );
        $db->setQuery( $query );
        $id = $db->loadResult();

		return $id;
	}

	function delete_list ($list_id)
	{
		$db           =& JFactory::getDBO();

        $query = 'DELETE ' .
                ' FROM #__acymailing_list' .
                ' WHERE listid='.$db->Quote( $list_id );
        $db->setQuery( $query );
        $db->Query();

		// delete lists subs
        $query = 'DELETE ' .
                ' FROM #__acymailing_listsub' .
                ' WHERE listid='.$db->Quote( $list_id );
        $db->setQuery( $query );
        $db->Query();
	}

	function remove_sub ($list_id, $user_id)
	{
		$db           =& JFactory::getDBO();

		$sub_id = JoomdleHelperAcymailing::get_sub_id ($user_id);
        $query = 'DELETE ' .
                ' FROM #__acymailing_listsub' .
                ' WHERE listid='.$db->Quote( $list_id ) .
                ' AND subid='.$db->Quote( $sub_id ) ;
        $db->setQuery( $query );
        $db->Query();

		return "OK";
	}
}
