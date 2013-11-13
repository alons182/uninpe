<?php
/**
 * @version     
 * @package     Joomdle
 * @subpackage  Content
 * @copyright   Copyright (C) 2008 - 2010 Antonio Duran Terres
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');

require_once (JPATH_ADMINISTRATOR . '/components/com_joomdle/tables/forums.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_joomdle/helpers/joomlagroups.php');

class JoomdleHelperForum
{

	function forum_exists ($course_id, $forum_id)
	{
		$db = JFactory::getDBO ();
        $query = 'SELECT kunena_forum_id ' .
            ' FROM #__joomdle_course_forums' .
            " WHERE course_id = " . $db->Quote($course_id);
		$query .= " AND moodle_forum_id = " . $db->Quote($forum_id);
        $db->setQuery($query);
        $kunena_forum_id = $db->loadResult();

		if (!$kunena_forum_id)
			return false;

		// Check that forum exists in Kunena
        $query = 'SELECT id ' .
            ' FROM #__kunena_categories' .
            " WHERE id = " . $db->Quote($kunena_forum_id);
        $db->setQuery($query);
        $kunena_forum_id = $db->loadResult();

		if (!$kunena_forum_id)
		{
			// Forum was deleted from Kunena
			// Delete entry in Joomdle table and return false, so that it can be created again
			$query = 'DELETE ' .
				' FROM #__joomdle_course_forums' .
				" WHERE course_id = " . $db->Quote($course_id);
			$query .= " AND moodle_forum_id = " . $db->Quote($forum_id);
			$db->setQuery($query);
			$db->query();

			return false;
		}

		return $forum_id;
	}

	function get_version ()
	{
		$db = JFactory::getDBO ();
        $query = 'SELECT version ' .
            ' FROM #__kunena_version';
        $db->setQuery($query);
        $version = $db->loadResult();

		$n = substr ($version, 0, 1);

		return $n;
	}

	function add_forum ($course_id, $forum_id, $forum_name)
	{
		$version = JoomdleHelperForum::get_version ();

		switch ($version)
		{
			case 1:
				JoomdleHelperForum::add_forum_k1 ($course_id, $forum_id, $forum_name);
				break;
			default:
				JoomdleHelperForum::add_forum_k2 ($course_id, $forum_id, $forum_name);
				break;
		}
	}

    function add_forum_k1 ($course_id, $forum_id, $forum_name)
    {
        require_once (JPATH_ADMINISTRATOR . '/components/com_kunena/libraries/category.php');

        if (!JoomdleHelperForum::forum_exists($course_id, $forum_id))
        {
            if ($forum_id != -2) // id=-2 indicates main course forum category
                $data['parent'] = JoomdleHelperForum::get_parent_id ($course_id);
            else $data['parent'] = 0;

            $data['name'] = $forum_name;
            $data['published'] = 1;

            $group_id = JoomdleHelperJoomlagroups::get_course_group_id ($course_id, 'students');
            $data['pub_access'] = $group_id;
            $group_id = JoomdleHelperJoomlagroups::get_course_group_id ($course_id, 'teachers');
            $data['admin_access'] = $group_id;


            $db = JFactory::getDBO ();
            kimport('tables.kunenacategory');
            $row = new TableKunenaCategory ( $db );

            $row->save ($data);
            $kunena_forum_id = $row->id;

            // Add to joomdle xref table
            $row = new JoomdleTableForums ($db);
            $forum_data['course_id'] = $course_id;
            $forum_data['moodle_forum_id'] = $forum_id;
            $forum_data['kunena_forum_id'] = $kunena_forum_id;
            $row->save ($forum_data);
        }

        // Get all course teachers and set them as moderators
        $teachers = JoomdleHelperContent::getCourseTeachers ($course_id);
        foreach ($teachers as $teacher)
        {
            JoomdleHelperForum::add_moderator ($course_id, $forum_id, $teacher['username']);
        }
    }

	function get_main_category ()
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$id = $comp_params->get( 'courses_forum_category', 0 );

		return $id;
	}

	function add_forum_k2 ($course_id, $forum_id, $forum_name)
	{
		if (!JoomdleHelperForum::forum_exists($course_id, $forum_id))
		{
			$data = new KunenaForumCategory ( );
			if ($forum_id != -2) // id=-2 indicates main course forum category
				$data->parent_id = JoomdleHelperForum::get_parent_id ($course_id);
			else $data->parent_id = JoomdleHelperForum::get_main_category ();

			$data->name = $forum_name;
			$data->alias = '';
			$data->published = 1;

			$group_id = JoomdleHelperJoomlagroups::get_course_group_id ($course_id, 'students');
			$data->pub_access = $group_id;
			$group_id = JoomdleHelperJoomlagroups::get_course_group_id ($course_id, 'teachers');
			$data->admin_access = $group_id;


			$data->accesstype = 'joomla.group';
			$data->save ();

			$db = JFactory::getDBO ();

			$kunena_forum_id = $data->id;

			// Add to joomdle xref table
			$jf_row = new JoomdleTableForums ($db);
			$forum_data['course_id'] = $course_id;
			$forum_data['moodle_forum_id'] = $forum_id;
			$forum_data['kunena_forum_id'] = $kunena_forum_id;
			$jf_row->save ($forum_data);
		}

		// Get all course teachers and set them as moderators
		$teachers = JoomdleHelperContent::getCourseTeachers ($course_id);
		foreach ($teachers as $teacher)
		{
			JoomdleHelperForum::add_moderator ($course_id, $forum_id, $teacher['username']);
		}
	}

	function remove_forum ($course_id, $forum_id)
	{
		$version = JoomdleHelperForum::get_version ();

		switch ($version)
		{
			case 1:
				JoomdleHelperForum::remove_forum_k1 ($course_id, $forum_id);
				break;
			default:
				JoomdleHelperForum::remove_forum_k2 ($course_id, $forum_id);
				break;
		}
	}

    function remove_forum_k1 ($course_id, $forum_id)
    {
        require_once (JPATH_ADMINISTRATOR . '/components/com_kunena/libraries/category.php');
        $db = JFactory::getDBO ();
        kimport('tables.kunenacategory');
        $row = new TableKunenaCategory ( $db );

        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
        $row->delete ($kunena_forum_id);

        $query = 'DELETE ' .
            ' FROM #__joomdle_course_forums' .
            " WHERE course_id = " . $db->Quote($course_id);
        $query .= " AND moodle_forum_id = " . $db->Quote($forum_id);
        $db->setQuery($query);
        $db->query();
    }

	function remove_forum_k2 ($course_id, $forum_id)
	{
        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
		$category = KunenaForumCategory::getInstance ($kunena_forum_id);
		$category->delete ();

		$db = JFactory::getDBO ();
        $query = 'DELETE ' .
            ' FROM #__joomdle_course_forums' .
            " WHERE course_id = " . $db->Quote($course_id);
		$query .= " AND moodle_forum_id = " . $db->Quote($forum_id);
        $db->setQuery($query);
        $db->query();
	}

	// Removes all course forums
	function remove_course_forums ($course_id)
	{
		$forums =  JoomdleHelperForum::get_course_forums ($course_id);

		foreach ($forums as $forum)
		{
			JoomdleHelperForum::remove_forum ($course_id, $forum['moodle_forum_id']);
		}
	}

	function get_parent_id ($course_id)
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT kunena_forum_id' .
            ' FROM #__joomdle_course_forums' .
            " WHERE course_id = " . $db->Quote($course_id);
		$query .= " AND moodle_forum_id = -2";
        $db->setQuery($query);
        $forum_id = $db->loadResult();

        return $forum_id;
	}

	function get_kunena_forum_id ($course_id, $forum_id)
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT kunena_forum_id' .
            ' FROM #__joomdle_course_forums' .
            " WHERE moodle_forum_id = " . $db->Quote($forum_id);
		$query .= " AND course_id = ". $db->Quote($course_id);
        $db->setQuery($query);
        $forum_id = $db->loadResult();

        return $forum_id;
	}

	function get_kunena_news_forum_id ($course_id)
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT kunena_forum_id' .
            ' FROM #__joomdle_course_forums' .
            " WHERE moodle_forum_id = -1 and course_id =" . $db->Quote($course_id);
        $db->setQuery($query);
        $forum_id = $db->loadResult();

        return $forum_id;
	}

	function get_course_forums ($course_id)
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT *' .
            ' FROM #__joomdle_course_forums' .
            " WHERE course_id = " . $db->Quote($course_id);
        $db->setQuery($query);
        $forum_ids = $db->loadAssocList();

        return $forum_ids;
	}

	function add_moderator ($course_id, $forum_id, $username)
	{
		$version = JoomdleHelperForum::get_version ();

		switch ($version)
		{
			case 1:
				JoomdleHelperForum::add_moderator_k1 ($course_id, $forum_id, $username);
				break;
			default:
				JoomdleHelperForum::add_moderator_k2 ($course_id, $forum_id, $username);
				break;
		}
	}

    function add_moderator_k1 ($course_id, $forum_id, $username)
    {
        $user_id = JUserHelper::getUserId ($username);
        if (!$user_id)
            return;

        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
        if (!$kunena_forum_id)
            return;

        // Check if already added
        $db           =& JFactory::getDBO();
        $query = 'SELECT catid' .
            ' FROM #__kunena_moderation' .
            " WHERE catid = " . $db->Quote($kunena_forum_id) .
            " AND userid = " .  $db->Quote($user_id);
        $db->setQuery($query);
        $exists = $db->loadResult();

        if ($exists)
            return;

        $data->userid = $user_id;
        $data->catid = $kunena_forum_id;
        $db->insertObject( '#__kunena_moderation', $data);
    }

	function add_moderator_k2 ($course_id, $forum_id, $username)
	{
		$user_id = JUserHelper::getUserId ($username);
        if (!$user_id)
            return;

        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
        if (!$kunena_forum_id)
            return;

		// Check if already added
		$db           =& JFactory::getDBO();
        $query = 'SELECT category_id' .
            ' FROM #__kunena_user_categories' .
            " WHERE category_id = " . $db->Quote($kunena_forum_id) .
			" AND user_id = " .  $db->Quote($user_id);
        $db->setQuery($query);
        $exists = $db->loadResult();

		if ($exists)
			return;

		// Set the user as moderator
		$query = "UPDATE #__kunena_users set moderator=1 where userid=" .  $db->Quote($user_id);
		$db->setQuery($query);
		$db->query();

		// Set category moderator
        $data->user_id = $user_id;
        $data->category_id = $kunena_forum_id;
        $data->role = 1;
        $db->insertObject( '#__kunena_user_categories', $data);
	}

	// Sets teacher as moderator for all course forums
	function add_forums_moderator ($course_id, $username)
	{
		$forums =  JoomdleHelperForum::get_course_forums ($course_id);

		foreach ($forums as $forum)
		{
			JoomdleHelperForum::add_moderator ($course_id, $forum['moodle_forum_id'], $username);
		}
	}

	// Removes unassigned teacher as moderator for all course forums
	function remove_forums_moderator ($course_id, $username)
	{
		$forums =  JoomdleHelperForum::get_course_forums ($course_id);

		foreach ($forums as $forum)
		{
			JoomdleHelperForum::remove_moderator ($course_id, $forum['moodle_forum_id'], $username);
		}
	}

	function remove_moderator ($course_id, $forum_id, $username)
	{
		$version = JoomdleHelperForum::get_version ();

		switch ($version)
		{
			case 1:
				JoomdleHelperForum::remove_moderator_k1 ($course_id, $forum_id, $username);
				break;
			default:
				JoomdleHelperForum::remove_moderator_k2 ($course_id, $forum_id, $username);
				break;
		}
	}

    function remove_moderator_k1 ($course_id, $forum_id, $username)
    {
        $user_id = JUserHelper::getUserId ($username);
        if (!$user_id)
            return;

        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
        if (!$kunena_forum_id)
            return;

        $db = JFactory::getDBO ();
        $query = 'DELETE ' .
            ' FROM #__kunena_moderation' .
            " WHERE catid = " . $db->Quote($kunena_forum_id) .
            " AND userid = " . $db->Quote($user_id);
        $db->setQuery($query);
        $db->query();
    }

	function remove_moderator_k2 ($course_id, $forum_id, $username)
	{
		$user_id = JUserHelper::getUserId ($username);
        if (!$user_id)
            return;

        $kunena_forum_id = JoomdleHelperForum::get_kunena_forum_id ($course_id, $forum_id);
        if (!$kunena_forum_id)
            return;

        $db = JFactory::getDBO ();
		$query = 'DELETE ' .
            ' FROM #__kunena_user_categories' .
            " WHERE category_id = " . $db->Quote($kunena_forum_id) .
			" AND user_id = " .  $db->Quote($user_id);
        $db->setQuery($query);
        $db->query();
	}

	function sync_forums ($course_ids)
	{
		foreach ($course_ids as $course_id)
		{
			JoomdleHelperForum::sync_course_forums ($course_id);
		}
	}


	function sync_course_forums ($course_id)
	{
		// Create user groups
		$course_info = JoomdleHelperContent::getCourseInfo ($course_id);
		JoomdleHelperJoomlagroups::add_course_groups ($course_id, $course_info['fullname']);
		JoomdleHelperJoomlagroups::sync_group_members ($course_id);

		// Create parent category
		JoomdleHelperForum::add_forum ($course_id, -2, $course_info['fullname']);

		$sections = JoomdleHelperContent::call_method ( 'get_course_mods', (int) $course_id, '');

		foreach ($sections as $section)
		foreach ($section['mods'] as $mod)
		{
			if (($mod['mod'] == 'forum') && ($mod['type'] != 'news'))
			{
				JoomdleHelperForum::add_forum ($course_id, $mod['id'], $mod['name']);
			}
		}
	}

}

?>
