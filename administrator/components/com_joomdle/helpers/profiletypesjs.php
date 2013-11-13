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
class JoomdleHelperProfiletypesjs
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

		
		if ($filter_type == 2) // not to be created
		{
			$query = "SELECT p.id, p.name from #__community_profiles as p " .
				"LEFT JOIN #__joomdle_profiletypes AS pt ON p.id = pt.profiletype_id " .
				" WHERE create_on_moodle = '0' OR create_on_moodle IS NULL ";
		}
		else if ($filter_type == 1) // to be created
		{
			$query = "SELECT p.id, p.name from #__community_profiles as p " .
				"LEFT JOIN #__joomdle_profiletypes AS pt ON p.id = pt.profiletype_id " .
				" WHERE create_on_moodle = '1'";
		}
		else  //all
		{
			$query = "SELECT p.id, p.name, pt.id as profiletype_id from #__community_profiles as p " .
				"LEFT JOIN #__joomdle_profiletypes AS pt ON p.id = pt.profiletype_id ";
		}

		$query .= " ORDER BY ".  $filter_order  ." ". $filter_order_Dir;

		if(! empty($limit)){
                   $query .= " LIMIT $limitstart, $limit";
                }

		$db->setQuery($query);
		$profiletypes = $db->loadObjectList();

		if (!$profiletypes)
			return NULL;

		$m = array();
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

        $query = "SELECT * from #__community_profiles" .
                " WHERE id = ". $db->Quote($id);

        $db->setQuery($query);
        $profiletype = $db->loadObject();

        return $profiletype;
    }
    
    function get_user_profile_id ($user_id)
    {
        $db           =& JFactory::getDBO();

        $query = "SELECT profile_id from #__community_users" .
                " WHERE userid = ". $db->Quote($user_id);

        $db->setQuery($query);
        $profiletype = $db->loadResult();

        return $profiletype;
    }   

	function add_user_to_profile ($user_id, $profile_id)
    {
        $db           =& JFactory::getDBO();

		$user->userid = $user_id;
		$user->status_access = 0;
		$user->points = 0;
		$user->posted_on = '0000-00-00 00:00:00';
		$user->invite = 0;
		$user->params = '{"notifyEmailSystem":1,"privacyProfileView":0,"privacyPhotoView":0,"privacyFriendsView":0,"privacyGroupsView":"","privacyVideoView":0,"notifyEmailMessage":1,"notifyEmailApps":1,"notifyWallComment":0,"notif_groups_notify_admin":1,"etype_groups_notify_admin":1,"notif_user_profile_delete":1,"etype_user_profile_delete":1,"notif_system_reports_threshold":1,"etype_system_reports_threshold":1,"notif_profile_activity_add_comment":1,"etype_profile_activity_add_comment":1,"notif_profile_activity_reply_comment":1,"etype_profile_activity_reply_comment":1,"notif_profile_status_update":1,"etype_profile_status_update":1,"notif_profile_like":1,"etype_profile_like":1,"notif_profile_stream_like":1,"etype_profile_stream_like":1,"notif_friends_request_connection":1,"etype_friends_request_connection":1,"notif_friends_create_connection":1,"etype_friends_create_connection":1,"notif_inbox_create_message":1,"etype_inbox_create_message":1,"notif_groups_invite":1,"etype_groups_invite":1,"notif_groups_discussion_reply":1,"etype_groups_discussion_reply":1,"notif_groups_wall_create":1,"etype_groups_wall_create":1,"notif_groups_create_discussion":1,"etype_groups_create_discussion":1,"notif_groups_create_news":1,"etype_groups_create_news":1,"notif_groups_create_album":1,"etype_groups_create_album":1,"notif_groups_create_video":1,"etype_groups_create_video":1,"notif_groups_create_event":1,"etype_groups_create_event":1,"notif_groups_sendmail":1,"etype_groups_sendmail":1,"notif_groups_member_approved":1,"etype_groups_member_approved":1,"notif_groups_member_join":1,"etype_groups_member_join":1,"notif_groups_notify_creator":1,"etype_groups_notify_creator":1,"notif_groups_discussion_newfile":1,"etype_groups_discussion_newfile":1,"notif_events_notify_admin":1,"etype_events_notify_admin":1,"notif_events_invite":1,"etype_events_invite":1,"notif_events_invitation_approved":1,"etype_events_invitation_approved":1,"notif_events_sendmail":1,"etype_events_sendmail":1,"notif_event_notify_creator":0,"etype_event_notify_creator":0,"notif_event_join_request":1,"etype_event_join_request":1,"notif_videos_submit_wall":1,"etype_videos_submit_wall":1,"notif_videos_reply_wall":1,"etype_videos_reply_wall":1,"notif_videos_tagging":1,"etype_videos_tagging":1,"notif_videos_like":1,"etype_videos_like":1,"notif_photos_submit_wall":1,"etype_photos_submit_wall":1,"notif_photos_reply_wall":1,"etype_photos_reply_wall":1,"notif_photos_tagging":1,"etype_photos_tagging":1,"notif_photos_like":1,"etype_photos_like":1,"notif_system_bookmarks_email":1,"etype_system_bookmarks_email":1,"notif_system_messaging":1,"etype_system_messaging":1};';

		$user->view = 0;
		$user->friendcount = 0;
		$user->profile_id = $profile_id;
		$user->watermark_hash = '';


		$db->insertObject ('#__community_users', $user);
    }


}
