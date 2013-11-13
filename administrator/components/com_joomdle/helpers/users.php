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
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/content.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/mappings.php');

/**
 *
 * @static
 * @package		Joomdle
 * @since 1.5
 */
class JoomdleHelperUsers
{
	function create_joomla_user ($user_info)
	{
                $usersConfig = &JComponentHelper::getParams( 'com_users' );

                $authorize      =& JFactory::getACL();

				$user = new JUser ();

                // Initialize new usertype setting
                $newUsertype = $usersConfig->get( 'new_usertype' );
                if (!$newUsertype) {
                        $newUsertype = 'Registered';
                }


                // Bind the user_info array to the user object
                if (!$user->bind( $user_info)) {
                        JError::raiseError( 500, $user->getError());
                }

                // Set some initial user values
                $user->set('id', 0);
                $user->set('usertype', $newUsertype);
				$system = 2; // ID of Registered
                $user->groups = array ();
                $user->groups[] = $system;

                $date =& JFactory::getDate();
                $user->set('registerDate', $date->toSql());

                $parent =& JFactory::getUser();
                $user->setParam('u'.$parent->id.'_parent_id', $parent->id);

		if ($user_info['block'])
			$user->set('block', '1');

		// If there was an error with registration
                if ( !$user->save() )
                {
                        return false;
                }

		/* Update profile additional data */
		return JoomdleHelperMappings::save_user_info ($user_info);
	}

	function activate_joomla_user ($username)
	{
		$user_id = JUserHelper::getUserId($username);
        $user =& JFactory::getUser($user_id);
		$user->set('block', '0');
                if ( !$user->save() )
                        return false;

		return true;
	}

	static function getStateOptions()
    {
        // Build the filter options.
        $options    = array();

//		$options[] = JHTML::_('select.option',  0, '- '. JText::_( 'COM_JOOMDLE_SELECT_FILTER' ) .' -');
		$options[] = JHTML::_('select.option',  'joomla', JText::_( 'COM_JOOMDLE_JOOMLA_USERS' ) );
		$options[] = JHTML::_('select.option',  'moodle', JText::_( 'COM_JOOMDLE_MOODLE_USERS' ) );
		$options[] = JHTML::_('select.option',  'joomdle', JText::_( 'COM_JOOMDLE_JOOMLDE_USERS' ) );
		$options[] = JHTML::_('select.option',  'not_joomdle', JText::_( 'COM_JOOMDLE_NOT_JOOMDLE_USERS' ) );


        return $options;
    }

}

?>
