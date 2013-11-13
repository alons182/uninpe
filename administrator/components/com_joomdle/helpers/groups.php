<?php
/**
 * @version             
 * @package             Joomdle
 * @copyright   Copyright (C) 2008 - 2010 Antonio Duran Terres
 * @license             GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.user.helper');
jimport('joomla.filesystem.folder');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/content.php');



/**
 * Content Component Query Helper
 *
 * @static
  @package             Joomdle
 * @since 1.5
 */
class JoomdleHelperGroups
{
	function get_main_category ()
    {
        $comp_params = &JComponentHelper::getParams( 'com_joomdle' );
        $id = $comp_params->get( 'jomsocial_groups_category', 1 );

        return $id;
    }

	function addJSGroup ($name, $description, $course_id, $website)
	{
		if (file_exists (JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php'))
			require_once(JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php');
		require_once(JPATH_SITE.'/components/com_community/libraries/core.php');
		require_once(JPATH_SITE.'/components/com_community/models/groups.php');


                $mainframe =& JFactory::getApplication();

                // Get my current data.
                $validated      = true;

                $group          =& JTable::getInstance( 'Group' , 'CTable' );
				$model = CFactory::getModel('groups');

                // @rule: Test for emptyness
                if( empty( $name ) )
                {
                        $validated = false;
                }

                // @rule: Test if group exists
                if( $model->groupExist( $name ) )
                {
                        $validated = false;
                }

                // @rule: Test for emptyness
                if( empty( $description ) )
                {
                        $validated = false;
                }

				$categoryId = JoomdleHelperGroups::get_main_category ();
                if( empty( $categoryId ) )
                {
                        $validated      = false;
                }

				if($validated)
                {
                        // Assertions
                        // Category Id must not be empty and will cause failure on this group if its empty.
                        CError::assert( $categoryId , '', '!empty', __FILE__ , __LINE__ );


                        // @rule: Retrieve params and store it back as raw string
						$params    = new CParameter( '' );


                        $discussordering                        = JRequest::getVar( 'discussordering' , DISCUSSION_ORDER_BYLASTACTIVITY , 'REQUEST' );
                        $params->set('discussordering' , $discussordering );

                        $photopermission                        = JRequest::getVar( 'photopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
                        $params->set('photopermission' , $photopermission );

                        $videopermission                        = JRequest::getVar( 'videopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
                        $params->set('videopermission' , $videopermission );

                        $grouprecentphotos                      = JRequest::getVar( 'grouprecentphotos' , GROUP_PHOTO_RECENT_LIMIT , 'REQUEST' );
                        $params->set('grouprecentphotos' , $grouprecentphotos );

                        $grouprecentvideos                      = JRequest::getVar( 'grouprecentvideos' , GROUP_VIDEO_RECENT_LIMIT , 'REQUEST' );
                        $params->set('grouprecentvideos' , $grouprecentvideos );

                        $newmembernotification          = JRequest::getVar( 'newmembernotification' , '1' , 'REQUEST' );
                        $params->set('newmembernotification' , $newmembernotification );

                        $joinrequestnotification        = JRequest::getVar( 'joinrequestnotification' , '1' , 'REQUEST' );
                        $params->set('joinrequestnotification' , $joinrequestnotification );

                        $params->set('course_id' , $course_id );

                        CFactory::load('helpers' , 'owner' );
						$group->name            = $name;
                        $group->description     = $description;
                        $group->categoryid      = $categoryId;
                        $group->website         = $website;
                        $group->ownerid         = JoomdleHelperSystem::get_admin_id ();

                        $group->created         = gmdate('Y-m-d H:i:s');
                        $group->approvals       = 1;
                        $group->params          = $params->toString();

                        // Set the default thumbnail and avatar for the group just in case
                        // the user decides to skip this
                     //   $group->thumb           = 'components/com_community/assets/group_thumb.jpg';
                     //   $group->avatar          = 'components/com_community/assets/group.jpg';

                        $group->published       = 1;

                        // Store the group now.
                        $group->store();

                        // Since this is storing groups, we also need to store the creator / admin
                        // into the groups members table
                        $member                         =& JTable::getInstance( 'GroupMembers' , 'CTable' );
                        $member->groupid        = $group->id;
                        $member->memberid       = $group->ownerid;

                        // Creator should always be 1 as approved as they are the creator.
                        $member->approved       = 1;

                        // @todo: Setup required permissions in the future
                        $member->permissions    = '1';

                        $member->store();

                        // Increment the member count
						$group->updateStats();
						$group->store();
                }

                return "OK";
        }


	function updateJSGroup ($name, $description, $course_id, $website)
	{
		 $db           =& JFactory::getDBO();
		 $query = 'SELECT id' .
				 ' FROM #__community_groups' .
			//	 " WHERE params LIKE '%\"course_id\":$course_id%' LIMIT 1";
				  " WHERE params LIKE '%course_id=$course_id%' LIMIT 1";

		$db->setQuery( $query );
		$id = $db->loadResult();

		 $query = 'UPDATE #__community_groups ' .
			 		"SET name='$name', description='$description'" .
				 " WHERE id='$id'";
		$db->setQuery( $query );
		$db->Query();

		return "OK";
	}

	/* Gets the first superadmin user id */
	function get_admin_id ()
	{
		 $db           =& JFactory::getDBO();
		 $query = 'SELECT id' .
				 ' FROM #__users' .
				 " WHERE usertype = 'Super Administrator' LIMIT 1";
		$db->setQuery( $query );
		$result = $db->loadResult();

		return $result;
	}

	function get_js_group_by_name ($name)
	{
                $db =& JFactory::getDBO();

		$name = utf8_decode ($name);
		$strSQL = 'SELECT id FROM `#__community_groups`'
			. " WHERE `name`=" . $db->Quote( $name );


                $db->setQuery( $strSQL );
                $result = $db->loadResult();

		return $result;
	}

	function get_js_group_by_course_id ($course_id)
	{
		$db =& JFactory::getDBO();

		$strSQL = 'SELECT id FROM `#__community_groups`' .
				  " WHERE params LIKE '%\"course_id\":$course_id%' LIMIT 1";


		$db->setQuery( $strSQL );
		$result = $db->loadResult();

		return $result;
	}

	function get_js_group_image_link ($name)
	{
                $db =& JFactory::getDBO();

		$name = utf8_decode ($name);
		$strSQL = 'SELECT id, avatar FROM `#__community_groups`'
			. " WHERE `name`=" . $db->Quote( $name );


                $db->setQuery( $strSQL );
                $result = $db->loadAssoc();

		return $result;
	}

	function removeJSGroup ($name)
        {
		if (file_exists (JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php'))
            require_once(JPATH_ADMINISTRATOR.'components/com_community/tables/cache.php');
		require_once(JPATH_SITE.'/components/com_community/libraries/core.php');
		require_once(JPATH_SITE.'/components/com_community/models/groups.php');

		$group_id = JoomdleHelperGroups::get_js_group_by_name ($name);

		CommunityModelGroups::deleteGroupBulletins($group_id);
		CommunityModelGroups::deleteGroupMembers($group_id);
		CommunityModelGroups::deleteGroupWall($group_id);
		CommunityModelGroups::deleteGroupDiscussions($group_id);
		CommunityModelGroups::deleteGroupMedia($group_id);

		$group  =& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $group_id );

		$group->delete( $group_id);


		return "OK";
	}

	function addJSGroupMember ($group_name, $username, $permissions, $course_id)
	{
		if (file_exists (JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php'))
            require_once(JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php');
		require_once(JPATH_SITE.'/components/com_community/libraries/core.php');
		require_once(JPATH_SITE.'/components/com_community/models/groups.php');

		$group          =& JTable::getInstance( 'Group' , 'CTable' );
		$groupModel = CFactory::getModel('groups');
		$member         =& JTable::getInstance( 'GroupMembers' , 'CTable' );

		$group_id = JoomdleHelperGroups::get_js_group_by_course_id ($course_id);

		if (!$group_id)
			return "NO GROUP";

		$group->load( $group_id );

		//$my =  CFactory::getUser($username);
		$user_id = JUserHelper::getUserId($username);
		$my =  JFactory::getUser($user_id);

		$params         = $group->getParams();

		// Set the properties for the members table
		$member->groupid        = $group->id;
		$member->memberid       = $my->id;

		CFactory::load( 'helpers' , 'owner' );

		/* kludge: remove when fixing call_method fns */
		if ($permissions == -1)
			$permissions = 0;

		$member->permissions    = $permissions;
		$member->approved    = '1';

		// Get the owner data
//		$owner  = CFactory::getUser( $group->ownerid );

		$store  = $member->store();

		// Add assertion if storing fails
		CError::assert( $store , true , 'eq' , __FILE__ , __LINE__ );

		$group->updateStats();
		$group->store();

		return "OK";
	}

	function removeJSGroupMember ($group_name, $username)
	{
		if (file_exists (JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php'))
            require_once(JPATH_ADMINISTRATOR.'/components/com_community/tables/cache.php');
		require_once(JPATH_SITE.'/components/com_community/libraries/core.php');
		require_once(JPATH_SITE.'/components/com_community/models/groups.php');

		$group          =& JTable::getInstance( 'Group' , 'CTable' );
		$groupModel = CFactory::getModel('groups');
		$member         =& JTable::getInstance( 'GroupMembers' , 'CTable' );

		$group_id = JoomdleHelperGroups::get_js_group_by_name ($group_name);

		if (!$group_id)
			return;

		$group->load( $group_id );

//		$my =  CFactory::getUser($username);
		$user_id = JUserHelper::getUserId($username);
        $my =  JFactory::getUser($user_id);

		$db =& JFactory::getDBO();

                $query = 'DELETE from #__community_groups_members'
                        . ' WHERE groupid=' . $db->Quote( $group_id ).' and memberid='.$db->Quote( $my->id )
                        ;

		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}

		$query = "UPDATE #__community_groups SET membercount=membercount-1 WHERE id =".$db->Quote( $group_id);
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getError() );
		}
		return "OK";
	}

}


?>
