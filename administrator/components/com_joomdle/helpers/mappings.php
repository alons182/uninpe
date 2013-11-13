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
class JoomdleHelperMappings
{

	function get_app_mappings ($app)
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT *' .
			' FROM #__joomdle_field_mappings' .
			" WHERE joomla_app = " . $db->Quote($app);
                $db->setQuery($query);
                $mappings = $db->loadObjectList();

		if (!$mappings)
			return array ();

		return $mappings;
	}

	function getMappings ($filter_type, $limitstart, $limit, $filter_order, $filter_order_Dir, $search)
	{
                $db           =& JFactory::getDBO();

		$wheres = array ();
		if ($filter_type != '')
			$wheres[] = "joomla_app = ". $db->Quote($filter_type);

		if ($search)
		{
			$wheres_search[] = "joomla_field = ". $db->Quote($search);
			$wheres_search[] = "moodle_field = ". $db->Quote($search);
			$wheres[] = "(joomla_field LIKE  ". $search ." OR moodle_field LIKE ".$search.")";
		}

		$query = 'SELECT *' .
			' FROM #__joomdle_field_mappings';

		if(! empty($wheres)){
                   $query .= " WHERE ".implode(' AND ', $wheres);
                }

		$query .= " ORDER BY ".  $filter_order  ." ". $filter_order_Dir;

		if(! empty($limit)){
                   $query .= " LIMIT $limitstart, $limit";
                }

		$db->setQuery($query);
		$mappings = $db->loadAssocList();


		if (!$mappings)
			return array ();

		foreach ($mappings as $mapping)
		{
	//		$mapping['joomla_field'] =  $mapping['joomla_field'];
			$mapping['joomla_field_name'] = JoomdleHelperMappings::get_field_name ( $mapping['joomla_app'], $mapping['joomla_field'] );
			$mapping['moodle_field_name'] = JoomdleHelperMappings::get_moodle_field_name ( $mapping['moodle_field'] );
			$m[] = $mapping;
		}

		return $m;
	}

	function getMapping ($id)
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT *' .
			' FROM #__joomdle_field_mappings' .
                              " WHERE id = " . $db->Quote($id);
                $db->setQuery($query);
                $mapping = $db->loadObject();

		return $mapping;
	}

	function get_moodle_field_name ($field_id)
	{
		static $fields;

		if (!$fields)
			$fields = JoomdleHelperContent::call_method ('user_custom_fields');

		if (!$fields)
			return $field_id;

		foreach ($fields as $field)
		{
			if ("cf_".$field['id'] == $field_id)
				return $field['name'];
		}

		return $field_id;
	}

	function get_moodle_custom_field_value ($user_info, $field_id)
	{
		foreach ( $user_info['custom_fields'] as $field)
		{
			if ('cf_'.$field['id'] == $field_id)
			{
				$data = utf8_decode ($field['data']);
				break;
			}
		}

		return $data;
	}

	function delete_mappings ($cid)
	{
                $db           =& JFactory::getDBO();
		foreach ($cid as $id)
		{
			$query = 'DELETE ' .
				' FROM #__joomdle_field_mappings' .
				      " WHERE id = " . $db->Quote($id);
			$db->setQuery($query);
			$db->query();
		}
	}

	function get_user_info ($username, $app = '')
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );

		if (!$app)
			$app = $comp_params->get( 'additional_data_source' );

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['email'] = $user->email;

		/* Language */
		$user_info['lang']   = JoomdleHelperMappings::get_moodle_lang ($user->getParam( 'language' ));

		/* Timezone */
		$user_info['timezone']   = $user->getParam( 'timezone' );

		switch ($app)
		{
			case 'jomsocial':
				$more_info = JoomdleHelperMappings::get_user_info_jomsocial ($username);
				break;
			case 'virtuemart':
				$more_info = JoomdleHelperMappings::get_user_info_virtuemart ($username);
				break;
			case 'virtuemart2':
				$more_info = JoomdleHelperMappings::get_user_info_virtuemart2 ($username);
				break;
			case 'tienda':
				$more_info = JoomdleHelperMappings::get_user_info_tienda ($username);
				break;
			case 'cb':
				$more_info = JoomdleHelperMappings::get_user_info_cb ($username);
				break;
			case 'joomla16':
				$more_info = JoomdleHelperMappings::get_user_info_joomla16 ($username);
				break;
			case 'hikashop':
				$more_info = JoomdleHelperMappings::get_user_info_hikashop ($username);
				break;
            case 'no':
                $more_info = JoomdleHelperMappings::get_user_info_joomla ($username);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetUserInfo', array($username));
                $more_info = array_shift ($result);
                break;

		}

		return array_merge ($user_info, $more_info);
	}

	function get_user_info_for_joomla ($username)
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$app = $comp_params->get( 'additional_data_source' );

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		if (!$id)
			return array ();

		$user_info['name'] = $user->name;
		$user_info['email'] = $user->email;

		/* Language */
		$user_info['lang']   = JoomdleHelperMappings::get_moodle_lang ($user->getParam( 'language' ));

		/* Timezone */
		$user_info['timezone']   = $user->getParam( 'timezone' );

		switch ($app)
		{
			case 'jomsocial':
				$more_info = JoomdleHelperMappings::get_user_info_jomsocial ($username);
				$more_info['profile_url'] = 'index.php?option=com_community&view=profile&userid='.$id;
				break;
			case 'virtuemart':
				$more_info = JoomdleHelperMappings::get_user_info_virtuemart ($username);
				break;
			case 'virtuemart2':
				$more_info = JoomdleHelperMappings::get_user_info_virtuemart2 ($username);
				break;
			case 'tienda':
				$more_info = JoomdleHelperMappings::get_user_info_tienda ($username);
				break;
			case 'cb':
				$more_info = JoomdleHelperMappings::get_user_info_cb ($username);
				$more_info['profile_url'] = 'index.php?option=com_comprofiler&task=userprofile&user='.$id;
				break;
			case 'hikashop':
				$more_info = JoomdleHelperMappings::get_user_info_hikashop ($username);
				break;
            case 'no':
                $more_info = JoomdleHelperMappings::get_user_info_joomla ($username);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetUserInfo', array($username));
                $more_info = array_shift ($result);
                break;
		}

		if (array_key_exists ('country', $more_info))
		{
			require(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'countries_joomla.php');
			if ($more_info['country'])
				$more_info['country'] = $countries[$more_info['country']];
		}

		   if ((!array_key_exists ('pic_url', $more_info)) || (!$more_info['pic_url']) || ($more_info['pic_url'] == 'none'))
				$more_info['pic_url'] = JURI::root() . '/media/joomdle/images/avatar.png';


		return array_merge ($user_info, $more_info);
	}

	function get_field_name ($app, $field)
	{
		switch ($app)
		{
			case 'jomsocial':
				$name = JoomdleHelperMappings::get_field_name_jomsocial ($field);
				break;
			case 'virtuemart':
				$name = JoomdleHelperMappings::get_field_name_virtuemart ($field);
				break;
			case 'virtuemart2':
				$name = JoomdleHelperMappings::get_field_name_virtuemart2 ($field);
				break;
			case 'tienda':
				$name = JoomdleHelperMappings::get_field_name_tienda ($field);
				break;
			case 'cb':
				$name = JoomdleHelperMappings::get_field_name_cb ($field);
				break;
			case 'joomla16':
				$name = JoomdleHelperMappings::get_field_name_joomla16 ($field);
				break;
			case 'hikashop':
				$name = JoomdleHelperMappings::get_field_name_hikashop ($field);
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetFieldName', array($field));
                $name = array_shift ($result);
                break;
		}

		return $name;
	}

	function get_moodle_fields ()
	{
		return JoomdleHelperContent::call_method ('user_custom_fields');
	}

	function get_fields ($app)
	{
		switch ($app)
		{
			case 'jomsocial':
				$fields = JoomdleHelperMappings::get_fields_jomsocial ();
				break;
			case 'virtuemart':
				$fields = JoomdleHelperMappings::get_fields_virtuemart ();
				break;
			case 'virtuemart2':
				$fields = JoomdleHelperMappings::get_fields_virtuemart2 ();
				break;
			case 'tienda':
				$fields = JoomdleHelperMappings::get_fields_tienda ();
				break;
			case 'cb':
				$fields = JoomdleHelperMappings::get_fields_cb ();
				break;
			case 'joomla16':
				$fields = JoomdleHelperMappings::get_fields_joomla16 ();
				break;
			case 'hikashop':
				$fields = JoomdleHelperMappings::get_fields_hikashop ();
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetFields', array());
                $fields = array_shift ($result);
                break;
		}

		return $fields;
	}


	function get_user_info_joomla ($username)
	{

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);
		$user_info['pic_url'] =  'none';

		return $user_info;
	}

	/* Jomsocial fns */

	function get_user_info_jomsocial ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		/* User pic */
		$query = 'SELECT avatar, thumb' .
				' FROM #__community_users' .
				" WHERE userid = '$id'";
		$db->setQuery( $query );
		$user_row = $db->loadAssoc();
		$user_info['pic_url'] =  $user_row['avatar'];
		$user_info['thumb_url'] =  $user_row['thumb'];

		$mappings = JoomdleHelperMappings::get_app_mappings ('jomsocial');

		if (is_array ($mappings))
		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_jomsocial ($mapping->joomla_field, $user->id);
			if ($value)
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;

	}

	function get_field_name_jomsocial ($field)
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT name ' .
			' FROM #__community_fields' .
                              " WHERE id = " . $db->Quote($field);
                $db->setQuery($query);
                $field = $db->loadObject();

		return $field->name;
	}

	function get_field_type_jomsocial ($field)
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT type ' .
			' FROM #__community_fields' .
                              " WHERE id = " . $db->Quote($field);
                $db->setQuery($query);
                $field = $db->loadObject();

		return $field->type;
	}

	function get_field_value_jomsocial ($field, $user_id)
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT value ' .
			' FROM #__community_fields_values' .
                              " WHERE field_id = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);
                $db->setQuery($query);
                $field_obj = $db->loadObject();
		
		if (!$field_obj)
			return "";

		/* Check if data needs transformation */
		$type = JoomdleHelperMappings::get_field_type_jomsocial ($field);
		switch ($type)
		{
			case 'country':
				$field_obj->value = JoomdleHelperMappings::get_moodle_country ($field_obj->value);
				break;
			default:
				break;
		}

		return $field_obj->value;
	}

	function get_fields_jomsocial ()
	{
                $db           =& JFactory::getDBO();
		$query = 'SELECT id, name ' .
			' FROM #__community_fields';
                $db->setQuery($query);
                $fields = $db->loadObjectList();

		return $fields;
	}


	/* Virtuemart fns */

	function get_user_info_virtuemart ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);


		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('virtuemart');


		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_virtuemart ($mapping->joomla_field, $user->id);
			if ($value)
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;

	}

	function get_field_name_virtuemart ($field)
	{
		return $field;
	}

	function get_field_value_virtuemart ($field, $user_id)
	{
                $db           =& JFactory::getDBO();
		$query = "SELECT $field " .
			' FROM #__vm_user_info' .
                              " WHERE  user_id = " . $db->Quote($user_id);
                $db->setQuery($query);
                $field_object = $db->loadObject();
		
		if (!$field_object)
			return "";

		if ($field == 'country')
			$field_object->$field = JoomdleHelperMappings::get_vm_country ($field_object->$field);


		return $field_object->$field;
	}

	function get_fields_virtuemart ()
	{
		$fields = array ();

		$field = new JObject ();
		$field->name = 'first_name';
		$field->id = 'first_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'last_name';
		$field->id = 'last_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'last_name';
		$field->id = 'last_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'middle_name';
		$field->id = 'middle_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'company';
		$field->id = 'company';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'title';
		$field->id = 'title';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'phone_1';
		$field->id = 'phone_1';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'phone_2';
		$field->id = 'phone_2';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'fax';
		$field->id = 'fax';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'address_1';
		$field->id = 'address_1';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'address_2';
		$field->id = 'address_2';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'city';
		$field->id = 'city';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'state';
		$field->id = 'state';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'country';
		$field->id = 'country';
		$fields[] = $field;


		$field = new JObject ();
		$field->name = 'zip';
		$field->id = 'zip';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'user_email';
		$field->id = 'user_email';
		$fields[] = $field;


		//XXX meter el resto de user_info


		//XXX Echar un ojo a los campos personalizaos


		return $fields;
	}

	function get_vm_country ($country)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT  country_2_code" .
			' FROM #__vm_country' .
                              " WHERE  country_3_code = " . $db->Quote($country);
		$db->setQuery($query);
		$value = $db->loadResult();
		
		if (!$value)
			return "";

		return $value;
	}

	/* Virtuemart2 fns */

	function get_user_info_virtuemart2 ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('virtuemart2');

		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_virtuemart2 ($mapping->joomla_field, $user->id);
			if ($value)
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;

	}

	function get_field_name_virtuemart2 ($field)
	{
		return $field;
	}

	function get_field_value_virtuemart2 ($field, $user_id)
	{
                $db           =& JFactory::getDBO();
		$query = "SELECT $field " .
			' FROM #__virtuemart_userinfos' .
                              " WHERE  virtuemart_user_id = " . $db->Quote($user_id);
                $db->setQuery($query);
                $field_object = $db->loadObject();
		
		if (!$field_object)
			return "";

		if ($field == 'virtuemart_country_id')
			$field_object->$field = JoomdleHelperMappings::get_vm2_country ($field_object->$field);


		return $field_object->$field;
	}

    function get_fields_virtuemart2 ()
    {

        $fields = array ();

        $db           =& JFactory::getDBO();
        $query = "DESC ".
            ' #__virtuemart_userinfos' ;

        $db->setQuery($query);
        $field_objects = $db->loadObjectList();

        $fields = array ();
        $i = 0;
        foreach ($field_objects as $fo)
        {
            $fields[$i]->name =  $fo->Field;
            $fields[$i]->id =  $fo->Field;
            $i++;
        }

        return $fields;
    }

	function get_vm2_country ($country_id)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT  country_2_code" .
			' FROM #__virtuemart_countries' .
                              " WHERE  virtuemart_country_id = " . $db->Quote($country_id);
		$db->setQuery($query);
		$value = $db->loadResult();
		
		if (!$value)
			return "";

		return $value;
	}

    function create_additional_profile_virtuemart2 ($user_info)
    {
        $hash_secret = "VirtueMartIsCool";
        // Check if row already exists
        $username = $user_info['username'];
        $id = JUserHelper::getUserId($username);

        if (!$id)
            return; // user not found, should not happen
        $user_id = $id;

        $db = &JFactory::getDBO();

        $query = 'SELECT virtuemart_user_id' .
                ' FROM #__virtuemart_userinfos' .
                " WHERE virtuemart_user_id = '$id'";
        $db->setQuery( $query );
        $id = $db->loadResult();

        if ($id)
            return; // user row found, nothing to do

        // Create row in vmusers
        $fields2->virtuemart_user_id = $user_id;
        $fields2->customer_number = md5 ($username);
		$fields2->perms = 'shopper';
        $db->insertObject ('#__virtuemart_vmusers', $fields2);

        // Create row in userinfos
        $fields->virtuemart_userinfo_id = md5(uniqid( $hash_secret));
        $fields->virtuemart_user_id = $user_id;
        $fields->address_type = 'BT';
        $fields->address_type_name = '-default-';
//        $timestamp = time();
//        $fields->created_on = $timestamp;
//        $fields->mdate = $timestamp;

        $db->insertObject ('#__virtuemart_userinfos', $fields);
    }

	/* Tienda fns */

	function get_user_info_tienda ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name); //XXX remove
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('tienda');

		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_tienda ($mapping->joomla_field, $user->id);
			if ($value)
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;
	}

	function get_field_name_tienda ($field)
	{
		return $field;
	}

	function get_field_value_tienda ($field, $user_id)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT $field " .
			' FROM #__tienda_addresses' .
                              " WHERE  user_id = " . $db->Quote($user_id);
		$db->setQuery($query);
		$field_obj = $db->loadObject();
		
		if (!$field_obj)
			return "";

		/* Check if data needs transformation */
		switch ($field)
		{
			case 'country_id':
				$field_obj->$field = JoomdleHelperMappings::get_tienda_country ($field_obj->$field);
				break;
			default:
				break;
		}

		return $field_obj->$field;
	}

	function get_fields_tienda ()
	{
		$fields = array ();

		$field = new JObject ();
		$field->name = 'first_name';
		$field->id = 'first_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'last_name';
		$field->id = 'last_name';
		$fields[] = $field;


		$field = new JObject ();
		$field->name = 'middle_name';
		$field->id = 'middle_name';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'company';
		$field->id = 'company';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'title';
		$field->id = 'title';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'phone_1';
		$field->id = 'phone_1';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'phone_2';
		$field->id = 'phone_2';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'fax';
		$field->id = 'fax';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'address_1';
		$field->id = 'address_1';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'address_2';
		$field->id = 'address_2';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'city';
		$field->id = 'city';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'postal_code';
		$field->id = 'postal_code';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'country_id'; //XXX special cases
		$field->id = 'country_id';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'zone_id';
		$field->id = 'zone_id';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'user_email';
		$field->id = 'user_email';
		$fields[] = $field;


		//XXX meter el resto de user_info


		//XXX Echar un ojo a los campos personalizaos


		return $fields;
	}

	function get_tienda_country ($country_id)
        {
                $db = &JFactory::getDBO();
                $query = 'SELECT *' .
                                ' FROM #__tienda_countries' .
                                " WHERE country_id = " . $db->Quote($country_id);
                $db->setQuery( $query );
                $country = $db->loadAssoc();

                return $country['country_isocode_2'];
        }


	/* Community Builder fns */

	function get_user_info_cb ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('cb');

		/* User pic */
		$query = 'SELECT avatar' .
				' FROM #__comprofiler' .
				" WHERE user_id = '$id'";
		$db->setQuery( $query );
		$user_row = $db->loadAssoc();

		if ($user_row['avatar'] != '')
            $user_info['pic_url'] =  'images/comprofiler/'.$user_row['avatar'];
        else
            $user_info['pic_url'] =   'components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png';

		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_cb ($mapping->joomla_field, $user->id);
			if ($value)
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;
	}

	function get_field_name_cb ($field)
	{
		return $field;
	}

	function get_field_value_cb ($field, $user_id)
	{
                $db           =& JFactory::getDBO();
		$query = "SELECT $field " .
			' FROM #__comprofiler' .
                              " WHERE  user_id = " . $db->Quote($user_id);
                $db->setQuery($query);
                $field_object = $db->loadObject();
		
		if (!$field_object)
			return "";

		return $field_object->$field;
	}

	function get_fields_cb ()
	{
		$fields = array ();

                $db           =& JFactory::getDBO();
		$query = "DESC ".
			' #__comprofiler' ;

                $db->setQuery($query);
                $field_objects = $db->loadObjectList();

		$fields = array ();
		$i = 0;
		foreach ($field_objects as $fo)
		{
			$fields[$i]->name =  $fo->Field;
			$fields[$i]->id =  $fo->Field;
			$i++;
		}


		return $fields;


		//XXX special cases

		return $fields;
	}

	/* Hikashop fns */

	function get_user_info_hikashop ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name); 
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('hikashop');


		if (is_array ($mappings))
		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_hikashop ($mapping->joomla_field, $user->id);
			if ($value) //  Only overwrite if there is something
				$user_info[$mapping->moodle_field] = $value;
		}

		return $user_info;
	}

	function get_field_name_hikashop ($field)
	{
		return $field;
	}

	function get_field_value_hikashop ($field, $user_id)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT user_id " .
			' FROM #__hikashop_user' .
                              " WHERE  user_cms_id = " . $db->Quote($user_id);
		$db->setQuery($query);
		$hikashop_user_id = $db->loadResult();

		$query = "SELECT $field " .
			' FROM #__hikashop_address' .
                              " WHERE  address_user_id = " . $db->Quote($hikashop_user_id);
		$db->setQuery($query);
		$value = $db->loadResult();
		
		/* Check if data needs transformation */
		switch ($field)
		{
			case 'address_country':
				$value = JoomdleHelperMappings::get_moodle_country_from_hikashop ($value);
				break;
			default:
				break;
		}

		return $value;
	}

	function get_moodle_country_from_hikashop ($zone_key)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT zone_code_2 " .
			' FROM #__hikashop_zone' .
                              " WHERE  zone_namekey = " . $db->Quote($zone_key);
		$db->setQuery($query);
		$code = $db->loadResult();

		return $code;
	}

	function get_fields_hikashop ()
	{
		$fields = array ();

                $db           =& JFactory::getDBO();
		$query = "DESC ".
			' #__hikashop_address' ;

                $db->setQuery($query);
                $field_objects = $db->loadObjectList();

		$fields = array ();
		$i = 0;
		foreach ($field_objects as $fo)
		{
			$fields[$i]->name =  $fo->Field;
			$fields[$i]->id =  $fo->Field;
			$i++;
		}


		return $fields;


		//XXX special cases

		return $fields;
	}

	/* General helper fns */

	function get_firstname ($name)
        {
                $parts = explode (' ', $name);

                return  $parts[0];
        }

        function get_lastname ($name)
        {
                $parts = explode (' ', $name);

                $lastname = '';
                $n = count ($parts);
                for ($i = 1; $i < $n; $i++)
                {
                        if ($i != 1)
                                $lastname .= ' ';
                        $lastname .= $parts[$i];
                }

                return $lastname;
        }

	function get_moodle_country ($country)
        {
		require(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'countries.php');

                if ($country == 'selectcountry')
                        return '';
                return $countries[$country];
        }

	function get_joomla_country ($country)
        {
		require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'countries_joomla.php');
                if ($country == 'selectcountry')
                        return '';
                return $countries[$country];
        }

	function get_moodle_lang ($lang)
        {
                if (!$lang)
                        return '';

			$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
			$moodle_version = $comp_params->get( 'moodle_version' );
			if ($moodle_version == 20)
			{
				return substr ($lang, 0, 2);
			}
			else
			{
                switch ($lang)
                {
                        case 'en-GB':
                                return 'en_utf8';
                        case 'es-ES':
                                return 'es_utf8';
                        default:
                                return '';
                }
			}
        }


	function sync_user_to_joomla ($username)
	{
		$user_info = JoomdleHelperContent::call_method ('user_details', $username);

        JoomdleHelperMappings::create_additional_profile ($user_info);
		JoomdleHelperMappings::save_user_info ($user_info, false);
		
	}

    function create_additional_profile ($user_info)
    {
        $comp_params = &JComponentHelper::getParams( 'com_joomdle' );
        $app = $comp_params->get( 'additional_data_source' );

        $username = $user_info['username'];
        $id = JUserHelper::getUserId($username);
        $user =& JFactory::getUser($id);

        switch ($app)
        {
            case 'jomsocial':
                break;
            case 'virtuemart':
                JoomdleHelperMappings::create_additional_profile_virtuemart ($user_info);
                break;
            case 'virtuemart2':
                JoomdleHelperMappings::create_additional_profile_virtuemart2 ($user_info);
                break;
            case 'tienda':
                JoomdleHelperMappings::create_additional_profile_tienda ($user_info);
                break;
            case 'cb':
                JoomdleHelperMappings::create_additional_profile_cb ($user_info);
                break;
            case 'hikashop':
                JoomdleHelperMappings::create_additional_profile_hikashop ($user_info);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
                $dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleCreateAdditionalProfile', array($user_info));
                $more_info = array_shift ($result);
                break;
        }
    }

	function save_user_info ($user_info, $use_utf8_decode = true)
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$app = $comp_params->get( 'additional_data_source' );

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);


		/* Save info to joomla user table */
		$user->email = $user_info['email'];
		if ($use_utf8_decode)
			$user->name = utf8_decode ($user_info['firstname']) . " " . utf8_decode ($user_info['lastname']);
		else
			$user->name = $user_info['firstname'] . " " . $user_info['lastname'];

		switch ($app)
		{
			case 'jomsocial':
				$more_info = JoomdleHelperMappings::save_user_info_jomsocial ($user_info, $use_utf8_decode);
				break;
			case 'virtuemart':
				$more_info = JoomdleHelperMappings::save_user_info_virtuemart ($user_info, $use_utf8_decode);
				break;
			case 'virtuemart2':
				$more_info = JoomdleHelperMappings::save_user_info_virtuemart2 ($user_info, $use_utf8_decode);
				break;
			case 'tienda':
				$more_info = JoomdleHelperMappings::save_user_info_tienda ($user_info, $use_utf8_decode);
				break;
			case 'cb':
				$more_info = JoomdleHelperMappings::save_user_info_cb ($user_info, $use_utf8_decode);
				break;
			case 'joomla16':
				$more_info = JoomdleHelperMappings::save_user_info_joomla16 ($user_info, $use_utf8_decode);
				break;
			case 'hikashop':
				$more_info = JoomdleHelperMappings::save_user_info_hikashop ($user_info, $use_utf8_decode);
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
                $dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleSaveUserInfo', array($user_info, $user_utf8_decode));
                $more_info = array_shift ($result);
                break;

		}

		$user->save ();

//		return array_merge ($user_info, $more_info);

		return $user_info;
	}


	function save_user_info_joomla ($user_info)
	{
		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);
	}

	function save_avatar_jomsocial ($userid, $pic_url)
	{
		$pic = JoomdleHelperContent::get_file ($pic_url);

		if (!$pic)
			return;

		require_once(JPATH_ROOT.'/'.'components'.'/'.'com_community'.'/'.'libraries'.'/'.'core.php');

		CFactory::load( 'helpers' , 'image' );

		$config                 = CFactory::getConfig();

		$imageMaxWidth  = 160;

		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$moodle_version = $comp_params->get( 'moodle_version' );

		if ($moodle_version == 19)
		{
			$extension = '.jpg';  // Moodle stores JPG always in 1.9
			$type = 'image/jpeg';
		}
		else
		{
			$extension = '.png';  // Moodle stores PNG always in 2.0
			$type = 'image/png';
		}

		$jconfig = JFactory::getConfig();
		$tmp_file = $jconfig->get('tmp_path'). '/' .'tmp_pic'.time();


		file_put_contents ($tmp_file, $pic);
		// Get a hash for the file name.
		$fileName               = JApplication::getHash( $pic_url . time() );
		$hashFileName   = JString::substr( $fileName , 0 , 24 );

		$storage                        = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/' . 'avatar';
		$storageImage           = $storage . '/' . $hashFileName . $extension;
		$storageThumbnail       = $storage . '/' . 'thumb_' . $hashFileName . $extension ;
		$image                          = $config->getString('imagefolder') . '/avatar/' . $hashFileName . $extension ;
		$thumbnail                      = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . $extension;

		$userModel                      =& CFactory::getModel( 'user' );

		// Only resize when the width exceeds the max.
		list($currentWidth, $currentHeight) = getimagesize( $tmp_file );
		if ($currentWidth < $imageMaxWidth)
			$imageMaxWidth = $currentWidth;
		//if( !CImageHelper::resizeProportional( $tmp_file , $storageImage , 'image/jpeg' , $imageMaxWidth ) ) //Moodle always stores jpg
		//if( !CImageHelper::resizeProportional( $tmp_file , $storageImage , 'image/png' , $imageMaxWidth ) ) //Moodle always stores png in 2.0? XXX
		if( !CImageHelper::resizeProportional( $tmp_file , $storageImage , $type , $imageMaxWidth ) ) //Moodle always stores png in 2.0? XXX
		{
			$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageImage), 'error');

			if(isset($url)){
				$mainframe->redirect($url);
			}
		}

		// Generate thumbnail
	//	if(!CImageHelper::createThumb( $tmp_file , $storageThumbnail , 'image/png' )) //Moodle always stores png
		if(!CImageHelper::createThumb( $tmp_file , $storageThumbnail , $type )) //Moodle always stores png
		{
			$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageThumbnail), 'error');

			if(isset($url)){
				$mainframe->redirect($url);
			}
		}

		$userModel->setImage( $userid , $image , 'avatar' );
		$userModel->setImage( $userid , $thumbnail , 'thumb' );

	}

	function delete_avatar_jomsocial ($id)
	{

		require_once(JPATH_SITE.'/'.'components'.'/'.'com_community'.'/'.'libraries'.'/'.'core.php');
		require_once(JPATH_SITE.'/'.'components'.'/'.'com_community'.'/'.'models'.'/'.'user.php');

		$userModel      =& CFactory::getModel( 'User' );
		$userModel->setImage( $id , DEFAULT_USER_AVATAR , 'avatar');
		$userModel->setImage( $id , DEFAULT_USER_THUMB , 'thumb');

		return;
	}


	function save_user_info_jomsocial ($user_info, $use_utf8_decode = true)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('jomsocial');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			// Custom moodle fields
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_jomsocial ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_jomsocial ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_jomsocial ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }

		}

		if (($user_info['picture']) && ($user_info['pic_url']))
		{
			JoomdleHelperMappings::save_avatar_jomsocial ($id, $user_info['pic_url']);
		}
		else
			JoomdleHelperMappings::delete_avatar_jomsocial ($id);

		return $additional_info;

	}

	function set_field_value_jomsocial ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();

		/* Check if data needs transformation */
		$type = JoomdleHelperMappings::get_field_type_jomsocial ($field);
		switch ($type)
		{
			case 'country':
				$value = JoomdleHelperMappings::get_joomla_country ($value);
				break;
			default:
				break;
		}

		$query = 
			' SELECT count(*) from  #__community_fields_values' .
                              " WHERE field_id = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);

                $db->setQuery($query);
		$exists = $db->loadResult();

		if ($exists)
			$query = 
				' UPDATE #__community_fields_values' .
				' SET value='. $db->Quote($value) .
				      " WHERE field_id = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);
		else
			$query = 
				' INSERT INTO #__community_fields_values' .
				' (field_id, user_id, value) VALUES ('. $db->Quote($field) . ','.  $db->Quote($user_id) . ',' . $db->Quote($value) . ')';

                $db->setQuery($query);
                $db->query();
		
		return true;
	}

	/* Community Builder */
	function save_user_info_cb ($user_info, $use_utf8_decode)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('cb');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			// Custom moodle fields
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_cb ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_cb ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_cb ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}

		if (($user_info['picture']) && ($user_info['pic_url']))
		{
			JoomdleHelperMappings::save_avatar_cb ($id, $user_info['pic_url']);
		}
		else
			JoomdleHelperMappings::delete_avatar_cb ($id);

		return $additional_info;

	}

	function save_avatar_cb ($userid, $pic_url)
	{
		$pic = JoomdleHelperContent::get_file ($pic_url);

		if (!$pic)
			return;

		if ($moodle_version == 19)
		{
			$extension = '.jpg';  // Moodle stores JPG always in 1.9
			$type = 'image/jpeg';
		}
		else
		{
			$extension = '.png';  // Moodle stores PNG always in 2.0
			$type = 'image/png';
		}

		$newFileName =  uniqid($userid."_") . '.' .$extension;
		

		file_put_contents (JPATH_SITE . '/images/comprofiler/' . $newFileName , $pic);

		$db = &JFactory::getDBO();

		if ($ueConfig['avatarUploadApproval']==1) {

            $cbNotification =   new cbNotification();
            $cbNotification->sendToModerators(_UE_IMAGE_ADMIN_SUB,_UE_IMAGE_ADMIN_MSG);

            $db->setQuery("UPDATE #__comprofiler SET avatar=" . $db->Quote($newFileName) . ", avatarapproved=0 WHERE id=" . (int) $userid);
        } else {
            $db->setQuery("UPDATE #__comprofiler SET avatar=" . $db->Quote($newFileName) . ", avatarapproved=1, lastupdatedate=now()  WHERE id=" . (int) $userid);
        }

		$db->query();
	}

	function delete_avatar_cb ($userid)
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT avatar' .
				' FROM #__comprofiler' .
				" WHERE user_id = '$id'";
		$db->setQuery( $query );
		$avatar = $db->loadResult();

		if( ( strpos( $avatar, '/' ) === false ) && is_file(JPATH_SITE.'/images/comprofiler/'.$avatar))
		{
			@unlink(JPATH_SITE.'/images/comprofiler/'.$avatar);
			if(is_file(JPATH_SITE.'/images/comprofiler/tn'.$avatar)) 
				@unlink(JPATH_SITE.'/images/comprofiler/tn'.$avatar);
		}
		$db->setQuery("UPDATE #__comprofiler SET avatar=null, avatarapproved=1, lastupdatedate=now()  WHERE id=" . (int) $userid);
		$db->query();
	}

	function set_field_value_cb ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();

		$query = 
			' UPDATE #__comprofiler' .
			' SET '. $field.'='. $db->Quote($value) .
				  " WHERE user_id = " . $db->Quote($user_id);

		$db->setQuery($query);
		$db->query();
		
		return true;
	}

    function create_additional_profile_cb ($user_info)
    {
        $username = $user_info['username'];
        $id = JUserHelper::getUserId($username);

        if (!$id)
            return; // user not found, should not happen
        $user_id = $id;

        $db = &JFactory::getDBO();

        $query = 'SELECT user_id' .
                ' FROM #__comprofiler' .
                " WHERE user_id = '$id'";
        $db->setQuery( $query );
        $id = $db->loadResult();

        if ($id)
            return; // user row found, nothing to do

        // Create row
        $fields->id = $user_id;
        $fields->user_id = $user_id;

        $db->insertObject ('#__comprofiler', $fields);
    }


	/* Virtuemart */
	function save_user_info_virtuemart ($user_info, $use_utf8_decode)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('virtuemart');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_virtuemart ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_virtuemart ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_virtuemart ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}

		return $additional_info;
	}

	function set_field_value_virtuemart ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();
		$query = 
			' UPDATE #__vm_user_info' .
			' SET '. $field.'='. $db->Quote($value) .
				  " WHERE user_id = " . $db->Quote($user_id);
		$db->setQuery($query);
		$field_object = $db->loadObject();
		
		$db->setQuery($query);
		$db->query();
		
		return true;
	}

	/* Virtuemart2 */
	function save_user_info_virtuemart2 ($user_info, $use_utf8_decode = true)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('virtuemart2');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_virtuemart2 ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_virtuemart2 ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_virtuemart2 ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}

		return $additional_info;
	}

	function set_field_value_virtuemart2 ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();

		if ($field == 'virtuemart_country_id')
			$value = JoomdleHelperMappings::get_vm2_country_id ($value);

		$query = 
			' UPDATE #__virtuemart_userinfos' .
			' SET '. $field.'='. $db->Quote($value) .
				  " WHERE virtuemart_user_id = " . $db->Quote($user_id);
		$db->setQuery($query);
		$field_object = $db->loadObject();
		
		$db->setQuery($query);
		$db->query();
		
		return true;
	}

	function get_vm2_country_id ($country)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT  virtuemart_country_id" .
			' FROM #__virtuemart_countries' .
                              " WHERE  country_2_code = " . $db->Quote($country);
		$db->setQuery($query);
		$value = $db->loadResult();
		
		if (!$value)
			return "";

		return $value;
	}

	/* Tienda */
	function save_user_info_tienda ($user_info, $use_utf8_decode = true)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('tienda');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			// Custom moodle fields
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_tienda ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_tienda ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_tienda ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}

		return $additional_info;
	}

	function set_field_value_tienda ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();
		$query = 
			' UPDATE  #__tienda_addresses' .
			' SET '. $field.'='. $db->Quote($value) .
				  " WHERE user_id = " . $db->Quote($user_id);
		$db->setQuery($query);
		$field_object = $db->loadObject();
		
		$db->setQuery($query);
		$db->query();
		
		return true;
	}

	/* Joomla 1.6  fns */

	function get_user_info_joomla16 ($username)
	{
		$db = &JFactory::getDBO();

		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$user_info['firstname'] = JoomdleHelperMappings::get_firstname ($user->name);
		$user_info['lastname'] = JoomdleHelperMappings::get_lastname ($user->name);

		$mappings = JoomdleHelperMappings::get_app_mappings ('joomla16');

		if (is_array ($mappings))
		foreach ($mappings as $mapping)
		{
			$value = JoomdleHelperMappings::get_field_value_joomla16 ($mapping->joomla_field, $user->id);
			if ($value)
			{
				// Value is stored in DB in unicode
				$user_info[$mapping->moodle_field] =  json_decode ($value);
			}
		}

		return $user_info;

	}

	function get_field_name_joomla16 ($field)
	{
		return substr ($field, 8); //remove "profile."
	}

	function get_field_value_joomla16 ($field, $user_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT profile_value ' .
			' FROM #__user_profiles' .
                              " WHERE profile_key = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);
                $db->setQuery($query);
                $field_obj = $db->loadObject();
		
		if (!$field_obj)
			return "";

		return $field_obj->profile_value;
	}

	function get_fields_joomla16 ()
	{
		require_once (JPATH_ADMINISTRATOR . '/components/com_joomdle/models/j16profiles.php');
		$j16profiles = new PluginsModelJ16profiles ();
		$form = $j16profiles->getForm ();
		$form_fields =  $form->getFieldset();

		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$j16_profile_plugin = $comp_params->get( 'j16_profile_plugin' );

		if (!$j16_profile_plugin)
			$j16_profile_plugin = 'profile';

		$fields = array ();
		foreach ($form_fields as $field)
		{
			$name = $field->__get('name');

			preg_match_all("^\[(.*?)\]^",$name,$matches, PREG_PATTERN_ORDER);
			$field_name =  $matches[1][0];

			$f = new JObject ();
			$f->name = $field_name;
	//		$f->id = 'ldap.'. $f->name;
			$f->id = $j16_profile_plugin . '.' . $f->name;
			$fields[] = $f;
		}

		return $fields;

// XXX DISCARD

		$field = new JObject ();
		$field->name = 'address1';
		$field->id = 'profile.address1';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'address2';
		$field->id = 'profile.address2';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'city';
		$field->id = 'profile.city';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'region';
		$field->id = 'profile.region';
		$fields[] = $field;

		// Contry cannot be mapped as it is a free string in Joomla16
		/*
		$field = new JObject ();
		$field->name = 'country';
		$field->id = 'profile.country';
		$fields[] = $field;
		*/

		$field = new JObject ();
		$field->name = 'postal_code';
		$field->id = 'profile.postal_code';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'phone';
		$field->id = 'profile.phone';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'website';
		$field->id = 'profile.website';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'favoritebook';
		$field->id = 'profile.favoritebook';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'aboutme';
		$field->id = 'profile.aboutme';
		$fields[] = $field;

		$field = new JObject ();
		$field->name = 'date of birth';
		$field->id = 'profile.dob';
		$fields[] = $field;


		return $fields;
	}

	function save_user_info_joomla16 ($user_info, $use_utf8_decode = true)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('joomla16');


		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
//			$user_info[$mapping->moodle_field] = json_encode ($user_info[$mapping->moodle_field]);
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_joomla16 ($mapping->joomla_field, $data, $id);
			}
			else
            {
                  if ($use_utf8_decode) // Removed when Joomla started using json encoded values in profile table
                      JoomdleHelperMappings::set_field_value_joomla16 ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                  else
                    JoomdleHelperMappings::set_field_value_joomla16 ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}

		return $additional_info;
	}

	function set_field_value_joomla16 ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();

		$query = 
			' SELECT count(*) from  #__user_profiles' .
                              " WHERE profile_key = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);

		$db->setQuery($query);
		$exists = $db->loadResult();

		// Encode value in format used by Joomla
		$value = json_encode ($value);

		if ($exists)
			$query = 
				' UPDATE #__user_profiles' .
				' SET profile_value='. $db->Quote($value) .
				      " WHERE profile_key = " . $db->Quote($field) . " AND user_id = " . $db->Quote($user_id);
		else
			$query = 
                ' INSERT INTO #__user_profiles' .
				' (profile_key, user_id, profile_value) VALUES ('. $db->Quote($field) . ','.  $db->Quote($user_id) . ',' . $db->Quote($value) . ')';

                $db->setQuery($query);
                $db->query();
		
		return true;
	}

	/* Hikashop */
	function save_user_info_hikashop ($user_info, $use_utf8_decode = true)
	{
		$db = &JFactory::getDBO();

		$username = $user_info['username'];
		$id = JUserHelper::getUserId($username);
		$user =& JFactory::getUser($id);

		$mappings = JoomdleHelperMappings::get_app_mappings ('hikashop');

		foreach ($mappings as $mapping)
		{
			$additional_info[$mapping->joomla_field] = $user_info[$mapping->moodle_field];
			// Custom moodle fields
			if (strncmp ($mapping->moodle_field, 'cf_', 3) == 0)
			{
				$data = JoomdleHelperMappings::get_moodle_custom_field_value ($user_info, $mapping->moodle_field);
				JoomdleHelperMappings::set_field_value_hikashop ($mapping->joomla_field, $data, $id);
			}
			else
            {
                if ($use_utf8_decode)
                    JoomdleHelperMappings::set_field_value_hikashop ($mapping->joomla_field, utf8_decode ($user_info[$mapping->moodle_field]), $id);
                else
                    JoomdleHelperMappings::set_field_value_hikashop ($mapping->joomla_field,  ($user_info[$mapping->moodle_field]), $id);
            }
		}


		return $additional_info;

	}

	function set_field_value_hikashop ($field, $value, $user_id)
	{
		$db           =& JFactory::getDBO();

		switch ($field)
		{
			case 'address_country':
				$value = JoomdleHelperMappings::get_hikashop_country_from_moodle ($value);
				break;
			default:
				break;
		}

		$db           =& JFactory::getDBO();
        $query = "SELECT user_id " .
            ' FROM #__hikashop_user' .
                              " WHERE  user_cms_id = " . $db->Quote($user_id);
        $db->setQuery($query);
        $hikashop_user_id = $db->loadResult();

		$query = 
			' UPDATE #__hikashop_address' .
			' SET '. $field.'='. $db->Quote($value) .
				  " WHERE address_user_id = " . $db->Quote($hikashop_user_id);

		$db->setQuery($query);
		$db->query();
		
		return true;
	}

	function get_hikashop_country_from_moodle ($code)
	{
		$db           =& JFactory::getDBO();
		$query = "SELECT zone_namekey " .
			' FROM #__hikashop_zone' .
                              " WHERE  zone_code_2 = " . $db->Quote($code);
		$db->setQuery($query);
		$value = $db->loadResult();

		return $value;
	}

    function create_additional_profile_hikashop ($user_info)
    {
        $username = $user_info['username'];
        $id = JUserHelper::getUserId($username);

        if (!$id)
            return; // user not found, should not happen
        $user_id = $id;

        $db = &JFactory::getDBO();

        $query = 'SELECT user_id' .
                ' FROM #__hikashop_user' .
                " WHERE user_cms_id = '$id'";
        $db->setQuery( $query );
        $id = $db->loadResult();

        if (!$id)
		{
			// Create row
			$fields->user_cms_id = $user_id;

			$db->insertObject ('#__hikashop_user', $fields);
			$h_user_id = $db->insertid();
		}
		else $h_user_id = $id;

        $query = 'SELECT address_user_id' .
                ' FROM #__hikashop_address' .
                " WHERE address_user_id = '$h_user_id'";
        $db->setQuery( $query );
        $id = $db->loadResult();

        if ($id)
            return; // user row found, nothing to do

        $fields2->address_user_id = $h_user_id;
        $db->insertObject ('#__hikashop_address', $fields2);
    }

	/* This is *admin* profile url */
	function get_profile_url ($username)
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$app = $comp_params->get( 'additional_data_source' );

		$id = JUserHelper::getUserId($username);
        $user =& JFactory::getUser($id);
		$user_id = $user->id;

		switch ($app)
		{
			case 'jomsocial':
				$url = 'index.php?option=com_community&view=users&layout=edit&id='. $user_id;
				break;
			case 'virtuemart':
				$url = "index.php?page=admin.user_form&user_id=$user_id&option=com_virtuemart";
				break;
			case 'tienda':
				$url = "index.php?option=com_tienda&controller=users&view=users&task=view&id=".$user_id;
				break;
			case 'cb':
				$url = 'index.php?option=com_users&view=user&task=edit&cid[]='.$user_id;
				break;
            case 'no':
				$url = 'index.php?option=com_users&task=user.edit&id='.$user_id;
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
                $dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetProfileUrl', array($user_id));
                $url = array_shift ($result);
                break;

		}

		return $url;
	}

	function get_login_url ($course_id)
	{
		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$app = $comp_params->get( 'additional_data_source' );
		$itemid = $comp_params->get( 'joomdle_itemid' );


		//XXX return only seems to work with normal Joomla login page (not CB or Jomsocial)
		$return = base64_encode ('index.php?option=com_joomdle&view=detail&course_id='.$course_id.'&Itemid='.$itemid);
		switch ($app)
		{
			case 'jomsocial':
				$url = "index.php?option=com_community&view=frontpage&return=$return";
				break;
			case 'virtuemart':
				$url = "index.php?option=com_user&view=login&return=$return";
				break;
			case 'tienda':
				$url = "index.php?option=com_user&view=login&return=$return";
				break;
			case 'cb':
				$url = "index.php?option=com_comprofiler&task=login&return=$return";
				break;
            case 'none':
				$url = "index.php?option=com_users&view=login&return=$return";
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleprofile' );
                $dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onJoomdleGetLoginUrl', array($return));
                $url = array_shift ($result);
                break;

		}

		return $url;
	}

	static function getStateOptions()
	{
        // Build the filter options.
        $options    = array();

		$options[] = JHTML::_('select.option',  'jomsocial',  'Jomsocial');
		$options[] = JHTML::_('select.option',  'cb',  'Community Builder');
		$options[] = JHTML::_('select.option',  'virtuemart', 'Virtuemart' );
		$options[] = JHTML::_('select.option',  'tienda',  'Tienda');
		$options[] = JHTML::_('select.option',  'joomla16',  'J1.6+ profiles');

        return $options;
    }

}

?>
