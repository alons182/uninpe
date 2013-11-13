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
require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_joomdle'.'/'.'helpers'.'/'.'parents.php');

/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomdle
 * @since 1.5
 */
class JoomdleHelperShop
{

	function is_course_on_sell ($course_id)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		if (!$shop)
			return false;

		$on_sell = false;
		switch ($shop)
		{
			case 'tienda':
				$on_sell = JoomdleHelperShop::is_course_on_sell_on_tienda ($course_id);
				break;
			case 'virtuemart2':
				$on_sell = JoomdleHelperShop::is_course_on_sell_on_vm2 ($course_id);
				break;
			case 'hikashop':
                $on_sell = JoomdleHelperShop::is_course_on_sell_on_hikashop ($course_id);
                break;
			default:
                JPluginHelper::importPlugin( 'joomdleshop' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onIsCourseOnSell', array($course_id));
				$on_sell = array_shift ($result);
                break;
		}

		return $on_sell;
	}

	function getShopCourses ()
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		$courses = array ();
		switch ($shop)
		{
			case 'tienda':
				$courses = JoomdleHelperShop::getTiendaCourses ();
				break;
			case 'virtuemart2':
				$courses = JoomdleHelperShop::getVirtuemart2Courses ();
				break;
            case 'hikashop':
                $courses = JoomdleHelperShop::getHikashopCourses ();
                break;
			default:
                JPluginHelper::importPlugin( 'joomdleshop' );
				$dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onGetShopCourses', array());
                $courses = array_shift ($result);
                break;
		}

		return $courses;
	}

	function get_bundles ()
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT * ' .
            ' FROM #__joomdle_bundles' ;
		$db->setQuery($query);
		$data = $db->loadAssocList();

		if (!$data)
			$data = array ();
		
		$i = 0;
		$c = array ();
		foreach ($data as $bundle)
		{
				$c[$i]->id = $bundle['id'];
				$c[$i]->name = $bundle['name'];
				$c[$i]->description = $bundle['description'];
				$c[$i]->cost = $bundle['cost'];
				$c[$i]->currency = $bundle['currency'];
				$c[$i]->published = JoomdleHelperShop::is_course_on_sell ('bundle_'.$bundle['id']);
				$i++;
		}

		return $c;
	}

	function get_sell_url ($course_id)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		switch ($shop)
		{
			case 'tienda':
				$url = JoomdleHelperShop::get_tienda_sell_url ($course_id);
				break;
			case 'virtuemart2':
				$url = JoomdleHelperShop::get_vm2_sell_url ($course_id);
				break;
            case 'hikashop':
                $url = JoomdleHelperShop::get_hikashop_sell_url ($course_id);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $result = $dispatcher->trigger('onGetSellUrl', array ($course_id));
                $url = array_shift ($result);
                break;
		}

		
		$shop_itemid = $params->get( 'shop_itemid' );
		if ($shop_itemid)
			$url .= "&Itemid=$shop_itemid";

		return $url;
	}

	function publish_courses ($courses)
	{
		foreach ($courses as $course_id)
		{
			$course_array = array ($course_id);
			if (JoomdleHelperShop::is_course_on_sell ($course_id))
				JoomdleHelperShop::dont_sell_courses ($course_array);
			else
				JoomdleHelperShop::sell_courses ($course_array);
		}
	}

	function sell_courses ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		switch ($shop)
		{
			case 'tienda':
				JoomdleHelperShop::sell_courses_on_tienda ($courses);
				break;
			case 'virtuemart2':
				JoomdleHelperShop::sell_courses_on_vm2 ($courses);
				break;
            case 'hikashop':
                JoomdleHelperShop::sell_courses_on_hikashop ($courses);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onSellCourses', array ($courses));
                break;
		}
	}

	function dont_sell_courses ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		switch ($shop)
		{
			case 'tienda':
				JoomdleHelperShop::dont_sell_courses_on_tienda ($courses);
				break;
			case 'virtuemart2':
				JoomdleHelperShop::dont_sell_courses_on_vm2 ($courses);
				break;
            case 'hikashop':
                JoomdleHelperShop::dont_sell_courses_on_hikashop ($courses);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onDontSellCourses', array ($courses));
                break;
        }
	}

	function reload_courses ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		switch ($shop)
		{
			case 'tienda':
				JoomdleHelperShop::reload_courses_to_tienda ($courses);
				break;
			case 'virtuemart2':
				JoomdleHelperShop::reload_courses_to_vm2 ($courses);
				break;
            case 'hikashop':
                JoomdleHelperShop::reload_courses_to_hikashop ($courses);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $disptacher->trigger('onReloadCourses', array ($courses));
                break;

		}
	}

	function delete_courses ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			if (strncmp ($sku, 'bundle_', 7) == 0)
			{
				$bundle_id = substr ($sku, 7);
				$query = "DELETE FROM  #__joomdle_bundles  where id = " . $db->Quote($bundle_id);
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
			}
		}

		switch ($shop)
		{
			case 'tienda':
				JoomdleHelperShop::delete_courses_from_tienda ($courses);
				break;
			case 'virtuemart2':
				JoomdleHelperShop::delete_courses_from_vm2 ($courses);
				break;
            case 'hikashop':
                JoomdleHelperShop::delete_courses_from_hikashop ($courses);
                break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onDeleteCourses', array ($courses));
                break;

		}
	}

	function create_bundle ($bundle)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$shop = $params->get( 'shop_integration' );

		// Insert record in bundles table
		$db           =& JFactory::getDBO();


		$b->courses = implode (',', $bundle['courses']);
		$b->name = $bundle['name'];
		$b->description = $bundle['description'];
		$b->cost = $bundle['cost'];
		$b->currency = $bundle['currency'];

		 /* Update record */
        if (array_key_exists ('id', $bundle))
		{
			$b->id = $bundle['id'];
            $db->updateObject ('#__joomdle_bundles', $b, 'id');
		}
        else
        {
            /* Insert new record */
            $db->insertObject ('#__joomdle_bundles', $b);
			$bundle['id'] = $db->insertid();
        }



		switch ($shop)
		{
			case 'tienda':
				JoomdleHelperShop::create_bundle_on_tienda ($bundle);
				break;
			case 'virtuemart2':
				JoomdleHelperShop::create_bundle_on_vm2 ($bundle);
				break;
			case 'hikashop':
				JoomdleHelperShop::create_bundle_on_hikashop ($bundle);
				break;
            default:
                JPluginHelper::importPlugin( 'joomdleshop' );
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onCreateBundle', array ($bundle));
                break;
		}
	}

	function get_bundle_info ($bundle_id)
	{
		$db           =& JFactory::getDBO();
        $query = 'SELECT * ' .
            ' FROM #__joomdle_bundles' .
			  " WHERE id = " . $db->Quote($bundle_id);
		$db->setQuery($query);
		$data = $db->loadAssoc();

		return $data;
	}

	/* Tienda  related functions */
	function getTiendaCourses ()
        {
                $cursos = JoomdleHelperContent::getCourseList (0);

                $c = array ();
                $i = 0;
		if (!is_array ($cursos))
			return $c;

                foreach ($cursos as $curso)
                {
                        $c[$i]->id = $curso['remoteid'];
                        $c[$i]->fullname = $curso['fullname'];
			$c[$i]->published = JoomdleHelperShop::is_course_on_sell_on_tienda ($curso['remoteid']);
                        $i++;
                }

                return $c;
        }

	function is_course_on_sell_on_tienda ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_sku' .
                                ' FROM #__tienda_products' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and product_enabled='1'";
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
			return 1;
		else
			return 0;

	}

	function get_tienda_sell_url ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_id' .
                                ' FROM #__tienda_products' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and product_enabled='1'";
		$db->setQuery($query);
		$product = $db->loadObjectList();
		$product_id = $product[0]->product_id;
		$url = "index.php?option=com_tienda&view=products&task=view&id=$product_id";

		return $url;
	}

	function reload_courses_to_tienda ($courses)
	{
		require_once(JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'defines.php');
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'tables' );
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			// skip bundles
			if (strncmp ($sku, 'bundle_', 7) == 0)
				continue;

			$query = "SELECT product_id FROM #__tienda_products WHERE product_sku = ". $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$product_id = $products[0]->product_id;

				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $course_info['fullname'];
				$desc = $course_info['summary'];
				$cost = $course_info['cost'];
				$currency = $course_info['currency'];

				$product = JTable::getInstance('Products', 'TiendaTable');
				$product->load ($product_id);
				$product->product_name = $name;
				$product->product_description = $desc;
				$product->product_description_short = $desc;
				$product->product_sku = $sku;
				$product->product_enabled = 1;
				$product->product_check_inventory = 0; // XXX Esto no va en joomdle.info... differente version?
				$product->product_ships = 0;


				$product->save();

				/* Set price */
				$price = JTable::getInstance('ProductPrices', 'TiendaTable');
				$price->load (array ('product_id' => $product_id));

				$price->product_id = $product->product_id;
				$price->product_price = $cost;
				$price->group_id = 1;

				$price->save();

				/* Set category */
				$category = JTable::getInstance( 'Productcategories', 'TiendaTable' );
				$category->product_id = $product->id;
				$category->category_id = $courses_category;
				if (!$category->save())
				{
					$this->messagetype      = 'notice';
					$this->message .= " :: ".$category->getError();
				}
			}
			else JoomdleHelperShop::sell_courses_on_tienda (array($sku));
		}
	}

	function delete_courses_from_tienda ($courses)
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'tables' );
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );

		$db           =& JFactory::getDBO();

		foreach ($courses as $sku)
		{
			$query = 'SELECT product_id' .
					' FROM #__tienda_products' .
					' WHERE product_sku =';
			$query .= $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			/* Product not on Tienda, nothing to do */
			if (!count ($products))
				continue;

			$query = "DELETE FROM  #__tienda_products  where product_sku = " . $db->Quote($sku);
			$db->setQuery($query);
			if (!$db->query()) {
				return JError::raiseWarning( 500, $db->getError() );
			 }
		}
	}

	function sell_courses_on_tienda ($courses)
	{
		require_once( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'defines.php' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'tables' );
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );

		$db           =& JFactory::getDBO();

		foreach ($courses as $sku)
		{
			$query = 'SELECT product_sku' .
					' FROM #__tienda_products' .
					' WHERE product_sku =';
			$query .= $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				/* Product already on Tienda, just publish it */
				$query = "UPDATE  #__tienda_products SET product_enabled = '1' where product_sku = ". $db->Quote($sku);
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				continue;
			}


			/* New product to add to Tienda */
			if (strncmp ($sku, 'bundle_', 7) == 0)
			{
				//bundle
				$bundle_id = substr ($sku, 7);
				$bundle = JoomdleHelperShop::get_bundle_info ($bundle_id);
				$name = $bundle['name'];
				$desc = $bundle['description'];
				$cost = $bundle['cost'];
				$currency = $bundle['currency'];
			}
			else
			{
				//Course
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $course_info['fullname'];
				$desc = $course_info['summary'];
				$cost = $course_info['cost'];
				$currency = $course_info['currency'];
			}

			$product = JTable::getInstance('Products', 'TiendaTable');
			$product->product_name = $name;
			$product->product_description = $desc;
			$product->product_description_short = $desc;
			$product->product_sku = $sku;
			$product->product_enabled = 1;
			$product->product_check_inventory = 0;
			$product->product_ships = 0;

			$product->save();

			/* Set price */
			$price = JTable::getInstance('ProductPrices', 'TiendaTable');
			$price->product_id = $product->product_id;
			$price->product_price = $cost;
			$price->group_id = 1;

			$price->save();

			/* Set category */
			$category = JTable::getInstance( 'Productcategories', 'TiendaTable' );
			$category->product_id = $product->id;
			$category->category_id = $courses_category;
			if (!$category->save())
			{
				$this->messagetype      = 'notice';
				$this->message .= " :: ".$category->getError();
			}
		}
	}

	function dont_sell_courses_on_tienda ($courses)
	{
		$db           =& JFactory::getDBO();

		foreach ($courses as $sku)
		{
			$query = "UPDATE  #__tienda_products SET product_enabled = '0' where product_sku = " . $db->Quote($sku);
			$db->setQuery($query);
			if (!$db->query()) {
				return JError::raiseWarning( 500, $db->getError() );
			 }
		}
	}

	function create_bundle_on_tienda ($bundle)
	{
		require_once( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'defines.php' );
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/'.'components'.'/'.'com_tienda'.'/'.'tables' );
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );

		$db           =& JFactory::getDBO();

		$bundle_id = 'bundle_'.$bundle['id'];
		$sku = $bundle_id;
		$query = 'SELECT product_sku' .
				' FROM #__tienda_products' .
				' WHERE product_sku =';
		$query .= $db->Quote($bundle_id);
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
		{
			/* Product already on Tienda, just publish it */
			$query = "UPDATE  #__tienda_products SET product_enabled = '1' where product_sku = ". $db->Quote($bundle_id);
			$db->setQuery($query);
			if (!$db->query()) {
				return JError::raiseWarning( 500, $db->getError() );
			 }
			return;
		}

		/* New product to add to Tienda */
		$name = $bundle['name'];
		$desc = $bundle['description'];
		$cost = $bundle['cost'];
		$currency = $bundle['currency'];

		$product = JTable::getInstance('Products', 'TiendaTable');
		$product->product_name = $name;
		$product->product_description = $desc;
		$product->product_description_short = $desc;
		$product->product_sku = $bundle_id;
		$product->product_enabled = 1;
		$product->product_check_inventory = 0;
		$product->product_ships = 0;

		$product->save();

		/* Set price */
		$price = JTable::getInstance('ProductPrices', 'TiendaTable');
		$price->product_id = $product->product_id;
		$price->product_price = $cost;
		$price->group_id = 1;

		$price->save();

		/* Set category */
		$category = JTable::getInstance( 'Productcategories', 'TiendaTable' );
		$category->product_id = $product->id;
		$category->category_id = $courses_category;
		if (!$category->save())
		{
			$this->messagetype      = 'notice';
			$this->message .= " :: ".$category->getError();
		}
	}


	/* Virtuemart related functions */

	function getVirtuemartCourses ()
        {
                $cursos = JoomdleHelperContent::getCourseList (0);

                $c = array ();
                $i = 0;
		if (!is_array ($cursos))
			return $c;

                foreach ($cursos as $curso)
                {
                        $c[$i]->id = $curso['remoteid'];
                        $c[$i]->fullname = $curso['fullname'];
			$c[$i]->published = JoomdleHelperShop::is_course_on_sell ($curso['remoteid']);
                        $i++;
                }

                return $c;
        }

	function is_course_on_sell_on_vm ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_sku' .
                                ' FROM #__vm_product' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and product_publish='Y'";
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
			return 1;
		else
			return 0;

	}

	function get_vm_sell_url ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_id' .
                                ' FROM #__vm_product' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and product_publish='Y'";
		$db->setQuery($query);
		$product = $db->loadObjectList();
		$product_id = $product[0]->product_id;
		$url = "index.php?page=shop.product_details&flypage=flypage.tpl&product_id=$product_id&option=com_virtuemart";

		return $url;
	}

	/* Reload data from Moodle */
	function reload_courses_to_vm ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			// skip bundles
			if (strncmp ($sku, 'bundle_', 7) == 0)
				continue;

			$query = "SELECT product_id FROM #__vm_product WHERE product_sku = " . $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$product_id = $products[0]->product_id;
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $db->getEscaped($course_info['fullname']);
				$desc = $db->getEscaped($course_info['summary']);
				$price = $db->getEscaped($course_info['cost']);
				$currency = $db->getEscaped($course_info['currency']);

				$query = "UPDATE  #__vm_product SET product_publish = 'Y', product_name = '$name', product_desc = '$desc', product_s_desc = '$desc'  where product_sku = '$sku'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				/* Price */
				$query = "UPDATE  #__vm_product_price SET product_price='$price', product_currency = '$currency' where product_id = '$product_id'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
			}
			else JoomdleHelperShop::sell_courses_on_vm (array($sku));
		}
	}

	function sell_courses_on_vm ($courses)
	{
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			/* If course already exists, only publish it */
			$query = "SELECT product_id FROM #__vm_product WHERE product_sku = '$sku'";
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$query = "UPDATE  #__vm_product SET product_publish = 'Y' where product_sku = '$sku'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				continue;
			}
			
			/* New course to insert in VM */
			
			if (strncmp ($sku, 'bundle_', 7) == 0)
			{
				//bundle
				$bundle_id = substr ($sku, 7);
				$bundle = JoomdleHelperShop::get_bundle_info ($bundle_id);
				$name = $bundle['name'];
				$desc = $bundle['description'];
				$price = $bundle['cost'];
				$currency = $bundle['currency'];
			}
			else
			{
				//Course
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $db->getEscaped($course_info['fullname']);
				$desc = $db->getEscaped($course_info['summary']);
				$price = $db->getEscaped($course_info['cost']);
				$currency = $db->getEscaped($course_info['currency']);
			}


			/* Add new product to Virtuemart */
			$query = "INSERT into #__vm_product (vendor_id, product_parent_id, product_sku, product_name, product_s_desc, product_desc, product_publish, child_options, quantity_options)
				  VALUES ('1', '0', '$sku', '$name', '$desc', '$desc', 'Y', 'N,N,N,N,N,N,20%,10%,', 'hide,0,0,1');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			/* Get product id */
			$product_id = $db->insertid();

			/* Get product type ID for Courses */
			$query = "SELECT product_type_id from #__vm_product_type where product_type_name='Course'";
			$db->setQuery($query);
			$items_aux = $db->loadObjectList();

			if ($db->getErrorNum()) {
				JError::raiseWarning( 500, $db->stderr() );
			}

			if (count ($items_aux) == 0)
			{
				/* Insert into product types if not done yet . We cannot do it  at install because VM may not be installed */
				$query = "INSERT into #__vm_product_type (product_type_name, product_type_description, product_type_publish) VALUES  ('Course', 'Joomdle Course', 'N')";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				/* Get product type ID for Courses  once is inserted */
				$query = "SELECT product_type_id from #__vm_product_type where product_type_name='Course'";
				$db->setQuery($query);
				$items_aux = $db->loadObjectList();

				if ($db->getErrorNum()) {
					JError::raiseWarning( 500, $db->stderr() );
				}
			}
			$type_id = $items_aux[0]->product_type_id;

			/* Create table if it no exists yet. We cannot create at install time due to variable name :( */
			$query = "CREATE TABLE IF NOT EXISTS #__vm_product_type_$type_id (product_id int primary key);";
			$db->setQuery($query);

			if (!$db->query()) {
				return JError::raiseWarning( 500, $db->getError() );
			 }
			/* Insert into product_type_$type_id table */
			$query = "INSERT INTO #__vm_product_type_$type_id VALUES ('$product_id')";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
			/* Insert into product_type_xref table */
			$query = "INSERT INTO #__vm_product_product_type_xref  (product_id, product_type_id) VALUES ('$product_id', '$type_id')";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			/* Add to category */
			//XXX configurar que categoria se ponen a los cursos por defecto, luego se podra cambiar en VM... XXX Do correspndoncia
			// category_id es el primer parametro del values : configurar en la pantalla del virtuemart
			$query = "INSERT into #__vm_product_category_xref 
				VALUES ('$courses_category', '$product_id', 1);";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
			// XXX falta meterlo en la tabla de manufactures
			//   INSERT INTO jos_vm_product_mf_xref VALUES ('7', '1')
			/* Add price */
			$query = "INSERT into #__vm_product_price (product_id, shopper_group_id, product_price, product_currency) 
				VALUES ('$product_id', 5, '$price', '$currency');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			/* Add download for new product */
			$query = "INSERT into #__vm_product_attribute (product_id, attribute_name, attribute_value) 
				VALUES ('$product_id', 'download', 'file.html');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			/* Add file */
			$filename = JPATH_COMPONENT.'/'.'views'.'/'.'virtuemart'.'/'.'downloads'.'/'.'file.html';
			$query  = "INSERT into #__vm_product_files (file_product_id, file_name, file_published)
				VALUES ('$product_id', '$filename', 0);";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
		}
	}

	function create_bundle_on_vm ($bundle)
	{
		JoomdleHelperShop::sell_courses_on_vm (array ('bundle_'.$bundle['id']));
	}

	function delete_courses_from_vm ($courses)
	{
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			/* Get product id */
			$query = "SELECT product_id FROM #__vm_product WHERE product_sku = '$sku'";
			$db->setQuery($query);
			$products = $db->loadObjectList();
			$product_id = $products[0]->product_id;

			$query = "DELETE from #__vm_product where product_sku = '$sku'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			$query = "DELETE from #__vm_product_category_xref
				WHERE product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
			$query = "DELETE from #__vm_product_price
				WHERE product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			$query = "DELETE from #__vm_product_attribute
				WHERE product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			$query = "DELETE from #__vm_product_files
				WHERE file_product_id = '$product_id';";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
		}
	}

	/* Unpublish courses in VM */
	function dont_sell_courses_on_vm ($courses)
	{
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			$query = "UPDATE  #__vm_product SET product_publish = 'N' where product_sku = '$sku'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
		}
	}

	function add_order_enrols ($order_id, $user_id)
	{
		$db           =& JFactory::getDBO();

		$user =& JFactory::getUser($user_id);
		$username=  $user->username;
		$email =  $user->email;

		/* Update user profile in Moodle  with VM data, if necessary */
		JoomdleHelperContent::call_method ('create_joomdle_user', $username);

		/* Get product type ID for Courses */
		$query = "SELECT product_type_id from #__vm_product_type where product_type_name='Course'";
		$db->setQuery($query);
		$items_aux = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseWarning( 500, $db->stderr() );
		}
		$type_id = $items_aux[0]->product_type_id;

		$order_id = $db->Quote ($order_id);
                $query = 'SELECT *' .
                        ' FROM #__vm_order_item' .
                        ' WHERE order_id =';
                $query .= "$order_id";

                $db->setQuery($query);
                $items = $db->loadObjectList();

                if ($db->getErrorNum()) {
                        JError::raiseWarning( 500, $db->stderr() );
                }

                /* No items in this order */
                if (count ($items) == 0)
                        return;

		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$buy_for_children = $params->get( 'buy_for_children' );


                foreach ($items as $item)
                {
			/* Only process product of type Course */
			$product_id = $item->product_id;
			$order_item_id = $item->order_item_id;
			$query = "SELECT product_id from #__vm_product_type_$type_id where product_id='$product_id'";
			$db->setQuery($query);
			$p_ids = $db->loadObjectList();

			if ($db->getErrorNum()) {
				JError::raiseWarning( 500, $db->stderr() );
			}
			/* If it is a course */
			if (count ($p_ids))
			{
				$sku = $item->order_item_sku;
				if ($buy_for_children)
				{
					if (strncmp ( $item->order_item_sku, 'bundle_', 7) == 0)
					{
						//bundle
						JoomdleHelperParents::purchase_bundle ($username, $sku,  $item->product_quantity);
					}
					else
					{
						JoomdleHelperParents::purchase_course ($username, $sku, $item->product_quantity);
					}
				}
				else
				{
					if (strncmp ( $item->order_item_sku, 'bundle_', 7) == 0)
					{
						//bundle
						 JoomdleHelperShop::enrol_bundle ($username, $sku);
					}
					else
					{
						//course
						JoomdleHelperContent::enrolUser ($username, $sku);
						/* Send confirmation email */
						JoomdleHelperShop::send_confirmation_email ($email, $sku);
					}
				}

				/* Update item status */
				$query = "update #__vm_order_item set order_status='C' where order_item_id='$order_item_id'";
				$db->setQuery($query);
				if (!$db->query()) {

					return JError::raiseWarning( 500, $db->getError() );
				 }
			}
                }

		$timestamp = time ();
		$mysqlDatetime = date("Y-m-d G:i:s", $timestamp); // + ($mosConfig_offset*60*60));  //Custom

                /* Mark order as Procesed (Enroled) */ ///XXX Quizas mejor dejarlo en C, menos lio
                //$query = "UPDATE #__vm_orders set order_status = 'E', mdate = '$timestamp' where order_id='$order_id'";  ///XXX Cambiado a C
                $query = "UPDATE #__vm_orders set order_status = 'C', mdate = '$timestamp' where order_id=$order_id"; 
                $db->setQuery($query);
                if (!$db->query()) {
                        return JError::raiseWarning( 500, $db->getError() );
                 }

		/* Update order status history */
                $query = "INSERT INTO #__vm_order_history (order_id, order_status_code, date_added) VALUES ('$order_id', 'C', '$mysqlDatetime')";
                $db->setQuery($query);
                if (!$db->query()) {
                        return JError::raiseWarning( 500, $db->getError() );
                 }

		/* Update items status */
 /*               $query = "update #__vm_order_item set order_status='C' where order_id='$order_id'";
                $db->setQuery($query);
                if (!$db->query()) {
                        return JError::raiseWarning( 500, $db->getError() );
                 }
*/
		/* XXX Update sales and stock level XXX*/
              /*  $query = "update #__vm_product set order_status='C' where order_id='$order_id'";
                $db->setQuery($query);
                if (!$db->query()) {
                        return JError::raiseWarning( 500, $db->getError() );
                 } */
	}

	/* Virtuemart related functions */

	function getVirtuemart2Courses ()
        {
                $cursos = JoomdleHelperContent::getCourseList (0);

                $c = array ();
                $i = 0;
		if (!is_array ($cursos))
			return $c;

                foreach ($cursos as $curso)
                {
                        $c[$i]->id = $curso['remoteid'];
                        $c[$i]->fullname = $curso['fullname'];
						$c[$i]->published = JoomdleHelperShop::is_course_on_sell_on_vm2 ($curso['remoteid']);
                        $i++;
                }

                return $c;
        }

	function is_course_on_sell_on_vm2 ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_sku' .
                                ' FROM #__virtuemart_products' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and published=1";
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
			return 1;
		else
			return 0;
	}

	function get_vm2_sell_url ($course_id)
	{
		$db           =& JFactory::getDBO();
		$query = 'SELECT virtuemart_product_id' .
                                ' FROM #__virtuemart_products' .
                                ' WHERE product_sku =';
		$query .= $db->Quote($course_id) . " and published=1";
		$db->setQuery($query);
		$product_id = $db->loadResult();
//		$url = "index.php?page=shop.product_details&flypage=flypage.tpl&product_id=$product_id&option=com_virtuemart";

		$url = "index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=$product_id";

		return $url;
	}

	/* Reload data from Moodle */
	function reload_courses_to_vm2 ($courses)
	{
		jimport('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$siteLang = JFactory::getLanguage()->getTag();
		if ( ! $siteLang ) {
			// use user default
			$lang =& JFactory::getLanguage();
			$siteLang = $lang->getTag();
		}
		$lang = strtolower(strtr($siteLang,'-','_'));

		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			// skip bundles
			if (strncmp ($sku, 'bundle_', 7) == 0)
				continue;

			$query = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_sku = " . $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$product_id = $products[0]->virtuemart_product_id;
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $db->getEscaped($course_info['fullname']);
				$desc = $db->getEscaped($course_info['summary']);
				$price = $db->getEscaped($course_info['cost']);
				$currency = $db->getEscaped($course_info['currency']);

				$slug = JFilterOutput::stringURLSafe($name);
			//	$query = "UPDATE  #__virtuemart_products SET published = 1, product_name = '$name', product_desc = '$desc', product_s_desc = '$desc', slug = '$slug'  where product_sku = '$sku'";
				$query = "UPDATE  #__virtuemart_products_$lang SET  product_name = '$name', product_desc = '$desc', product_s_desc = '$desc', slug = '$slug'  where virtuemart_product_id = '$product_id'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				/* Price */
				$query = "SELECT virtuemart_currency_id FROM #__virtuemart_currencies WHERE currency_code_3 = '$currency'";
				$db->setQuery($query);
				$currency_id = $db->loadResult();
				$query = "UPDATE  #__virtuemart_product_prices SET product_price='$price', product_currency = '$currency_id' where virtuemart_product_id = '$product_id'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
			}
			else JoomdleHelperShop::sell_courses_on_vm2 (array($sku));
		}
	}

	function sell_courses_on_vm2 ($courses)
	{
		jimport('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$siteLang = JFactory::getLanguage()->getTag();
		if ( ! $siteLang ) {
			// use user default
			$lang =& JFactory::getLanguage();
			$siteLang = $lang->getTag();
		}
		$lang = strtolower(strtr($siteLang,'-','_'));

		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			/* If course already exists, only publish it */
			$query = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_sku = '$sku'";
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$query = "UPDATE  #__virtuemart_products SET published = 1 where product_sku = '$sku'";
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
				 }
				continue;
			}
			
			/* New course to insert in VM */
			
			if (strncmp ($sku, 'bundle_', 7) == 0)
			{
				//bundle
				$bundle_id = substr ($sku, 7);
				$bundle = JoomdleHelperShop::get_bundle_info ($bundle_id);
				$name = $bundle['name'];
				$desc = $bundle['description'];
				$price = $bundle['cost'];
				$currency = $bundle['currency'];
			}
			else
			{
				//Course
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $db->getEscaped($course_info['fullname']);
				$desc = $db->getEscaped($course_info['summary']);
				$price = $db->getEscaped($course_info['cost']);
				$currency = $db->getEscaped($course_info['currency']);
			}


			/* Add new product to Virtuemart */
			$slug = JFilterOutput::stringURLSafe($name);
	//		$query = "INSERT into #__virtuemart_products (virtuemart_vendor_id, product_parent_id, product_sku, product_name, product_s_desc, product_desc, published, slug)
	//			  VALUES ('1', '0', '$sku', '$name', '$desc', '$desc', 1, '$slug');";
			$query = "INSERT into #__virtuemart_products (virtuemart_vendor_id, product_parent_id, product_sku, published)
				  VALUES ('1', '0', '$sku', 1);";
			$db->setQuery($query);
			if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
			 }

			/* Get product id */
			$product_id = $db->insertid();

			 $query = "INSERT into #__virtuemart_products_$lang (virtuemart_product_id, product_name, product_s_desc, product_desc,  slug)
                  VALUES ('$product_id', '$name', '$desc', '$desc', '$slug');";
            $db->setQuery($query);
            if (!$db->query()) {
                    return JError::raiseWarning( 500, $db->getError() );
			 }


			/* Add to category */
			//XXX configurar que categoria se ponen a los cursos por defecto, luego se podra cambiar en VM... XXX Do correspndoncia
			// category_id es el primer parametro del values : configurar en la pantalla del virtuemart
			$query = "INSERT into #__virtuemart_product_categories (virtuemart_product_id, virtuemart_category_id)
				VALUES ( '$product_id', '$courses_category');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			/* Add price */
			$query = "SELECT virtuemart_currency_id FROM #__virtuemart_currencies WHERE currency_code_3 = '$currency'";
			$db->setQuery($query);
			$currency_id = $db->loadResult();

			$query = "INSERT into #__virtuemart_product_prices (virtuemart_product_id,  product_price, product_currency) 
				VALUES ('$product_id', '$price', '$currency_id');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
			return;

			/* Add download for new product */

/*
			$query = "INSERT into #__vm_product_attribute (product_id, attribute_name, attribute_value) 
				VALUES ('$product_id', 'download', 'file.html');";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			// Add file 
			$filename = JPATH_COMPONENT.'/'.'views'.'/'.'virtuemart'.'/'.'downloads'.'/'.'file.html';
			$query  = "INSERT into #__vm_product_files (file_product_id, file_name, file_published)
				VALUES ('$product_id', '$filename', 0);";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
*/
		}

	}

	function create_bundle_on_vm2 ($bundle)
	{
		JoomdleHelperShop::sell_courses_on_vm2 (array ('bundle_'.$bundle['id']));
	}

	function delete_courses_from_vm2 ($courses)
	{
		jimport('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$siteLang = JFactory::getLanguage()->getTag();
		if ( ! $siteLang ) {
			// use user default
			$lang =& JFactory::getLanguage();
			$siteLang = $lang->getTag();
		}
		$lang = strtolower(strtr($siteLang,'-','_'));

		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			/* Get product id */
			$query = "SELECT virtuemart_product_id FROM #__virtuemart_products WHERE product_sku = '$sku'";
			$db->setQuery($query);
			$products = $db->loadObjectList();
			$product_id = $products[0]->virtuemart_product_id;

			$query = "DELETE from #__virtuemart_products where product_sku = '$sku'";
			$db->setQuery($query);
			if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
			 }

			$query = "DELETE from #__virtuemart_products_$lang where virtuemart_product_id = '$product_id'";
			$db->setQuery($query);
			if (!$db->query()) {
					return JError::raiseWarning( 500, $db->getError() );
			 }

			$query = "DELETE from #__virtuemart_product_categories
				WHERE virtuemart_product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			$query = "DELETE from #__virtuemart_product_prices
				WHERE virtuemart_product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
			return;

			$query = "DELETE from #__vm_product_attribute
				WHERE product_id = '$product_id'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }

			$query = "DELETE from #__vm_product_files
				WHERE file_product_id = '$product_id';";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
		}
	}

	/* Unpublish courses in VM */
	function dont_sell_courses_on_vm2 ($courses)
	{
		$db           =& JFactory::getDBO();
		foreach ($courses as $sku)
		{
			$query = "UPDATE  #__virtuemart_products SET published = 0 where product_sku = '$sku'";
			$db->setQuery($query);
                        if (!$db->query()) {
                                return JError::raiseWarning( 500, $db->getError() );
                         }
		}
	}

	function add_order_enrols_vm2 ($order_id, $user_id)
	{
		$db           =& JFactory::getDBO();

		$user =& JFactory::getUser($user_id);
		$username=  $user->username;
		$email =  $user->email;

		/* Update user profile in Moodle  with VM data, if necessary */
		JoomdleHelperContent::call_method ('create_joomdle_user', $username);

		$order_id = $db->Quote ($order_id);
		$query = 'SELECT *' .
				' FROM #__virtuemart_order_items' .
				' WHERE virtuemart_order_id =';
		$query .= "$order_id";

		$db->setQuery($query);
		$items = $db->loadObjectList();

		if ($db->getErrorNum()) {
				JError::raiseWarning( 500, $db->stderr() );
		}

		/* No items in this order */
		if (count ($items) == 0)
				return;

		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$buy_for_children = $params->get( 'buy_for_children' );


		$courses_category = $params->get( 'courses_category' );
		foreach ($items as $item)
		{
			/* Only process product in Courses category */
			$product_id = $item->virtuemart_product_id;
			$order_item_id = $item->virtuemart_order_item_id;

			$query = "SELECT virtuemart_category_id" .
					" FROM #__virtuemart_product_categories" .
					" WHERE virtuemart_product_id=".  $db->Quote($product_id);

			$db->setQuery($query);

			$cats = $db->loadAssocList();

			$cats_array = array ();
			foreach ($cats as $cat)
				$cats_array[] = $cat['virtuemart_category_id'];

			if ($db->getErrorNum()) {
				JError::raiseWarning( 500, $db->stderr() );
			}
			/* If it is a course */
			if (in_array ($courses_category, $cats_array))
			{
				$sku = $item->order_item_sku;
				if ($buy_for_children)
				{
					if (strncmp ( $item->order_item_sku, 'bundle_', 7) == 0)
					{
						//bundle
						JoomdleHelperParents::purchase_bundle ($username, $sku,  $item->product_quantity);
					}
					else
					{
						JoomdleHelperParents::purchase_course ($username, $sku, $item->product_quantity);
					}
				}
				else
				{
					if (strncmp ( $item->order_item_sku, 'bundle_', 7) == 0)
					{
						//bundle
						 JoomdleHelperShop::enrol_bundle ($username, $sku);
					}
					else
					{
						//course
						JoomdleHelperContent::enrolUser ($username, $sku);
						/* Send confirmation email */
						JoomdleHelperShop::send_confirmation_email ($email, $sku);
					}
				}

			}
		}
	}

   /* Hikashop related functions */
    function getHikashopCourses ()
    {
        $cursos = JoomdleHelperContent::getCourseList (0);
        $c = array ();
        $i = 0;

        if (!is_array ($cursos))
            return $c;

        foreach ($cursos as $curso)
        {
            $c[$i]->id = $curso['remoteid'];
            $c[$i]->fullname = $curso['fullname'];
            $c[$i]->published = JoomdleHelperShop::is_course_on_sell_on_hikashop ($curso['remoteid']);
            $i++;
        }

        return $c;
    }



    function is_course_on_sell_on_hikashop ($course_id)
    {
		$db           =& JFactory::getDBO();
		$query = 'SELECT product_code' .
                                ' FROM #__hikashop_product' .
                                ' WHERE product_code =';
		$query .= $db->Quote($course_id) . " and product_published=1";
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
			return 1;
		else
			return 0;
    }



    function get_hikashop_sell_url ($course_id)
    {
        $db =& JFactory::getDBO();
        $query = 'SELECT product_id' .
              ' FROM #__hikashop_product' .
              ' WHERE product_code =' . $db->Quote($course_id) .
              " and product_published='1'" ;
        $db->setQuery($query);
        $product_id = $db->loadResult();

        $url = "index.php?option=com_hikashop&ctrl=product&task=show&cid=$product_id";

        return $url;
    }



    function reload_courses_to_hikashop ($courses)
	{
        require_once( JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php' );

		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );
		$db           =& JFactory::getDBO();
		$config =& hikashop_config();
		foreach ($courses as $sku)
		{
			// skip bundles
			if (strncmp ($sku, 'bundle_', 7) == 0)
				continue;

			$query = "SELECT product_id FROM #__hikashop_product WHERE product_code = ". $db->Quote($sku);
			$db->setQuery($query);
			$products = $db->loadObjectList();
			if (count ($products))
			{
				$product_id = $products[0]->product_id;
				$element->product_id = $product_id;

				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $course_info['fullname'];
				$desc = $course_info['summary'];
				$cost = $course_info['cost'];
				$currency = $course_info['currency'];

				$product_class = hikashop_get('class.product');
				$element->categories = $product_class->getCategories ($product_id);

				$element->categories[] = $courses_category;
				$element->related = array();
				$element->options = array();
				$element->product_name = $name;
				$element->product_description = $desc;
				$element->product_code = $sku;
				$element->product_published = 1;

				$query = "SELECT category_id FROM #__hikashop_category WHERE category_namekey='default_tax'";
				$db->setQuery($query);
				$tax_id = $db->loadResult();
				if ($tax_id)
				{
					$element->product_tax_id = $tax_id;

					$query = "SELECT tax_namekey FROM #__hikashop_taxation WHERE category_namekey='default_tax'";
					$db->setQuery($query);
					$tax_namekey = $db->loadResult();

					$query = "SELECT tax_rate FROM #__hikashop_tax WHERE tax_namekey=".$db->Quote($tax_namekey);
					$db->setQuery($query);
					$tax_rate = $db->loadResult();

					$div = $tax_rate + 1;
					$price_without_tax = $cost / $div;
					$cost = $price_without_tax;
				}
				$element->prices = array ();
				$element->prices[0]->price_value = $cost;

				$query = "SELECT currency_id FROM #__hikashop_currency WHERE currency_code = '$currency'";
				$db->setQuery($query);
				$currency_id = $db->loadResult();
				$element->prices[0]->price_currency_id = $currency_id;
				$element->prices[0]->price_min_quantity = 0;

				$status = $product_class->save($element);
				if ($status)
				{
					$product_class->updateCategories($element,$status);
					$product_class->updatePrices($element,$status);
				}
			}
			else JoomdleHelperShop::sell_courses_on_hikashop (array($sku));
		}
	}

    function delete_courses_from_hikashop ($courses)
    {
        require_once( JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php' );
		$db           =& JFactory::getDBO();

		$ids = array();
        foreach ($courses as $sku)
        {
            $query = 'SELECT product_id' .
                    ' FROM #__hikashop_product' .
                    ' WHERE product_code =';
            $query .= $db->Quote($sku);
            $db->setQuery($query);
            $product_id = $db->loadResult();
            /* Product not on Hikashop, nothing to do */
            if (!$product_id)
                continue;

			$ids[] = $product_id;

        }
		$product_class = hikashop_get('class.product');
		$product_class->delete ($ids);
    }

    function sell_courses_on_hikashop ($courses)
    {
        require_once( JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php' );
        $params = &JComponentHelper::getParams( 'com_joomdle' );
        $courses_category = $params->get( 'courses_category' );

        $db           =& JFactory::getDBO();

        foreach ($courses as $sku)
        {
            $query = 'SELECT product_code ' .
                    ' FROM #__hikashop_product' .
                    ' WHERE product_code =' . $db->Quote($sku);
            $db->setQuery($query);
            $products = $db->loadObjectList();
            if (count ($products))
            {
                /* Product already on Hikashop, just publish it */
                $query = "UPDATE  #__hikashop_product SET product_published = '1' where product_code = ". $db->Quote($sku);
                $db->setQuery($query);
                if (!$db->query()) {
                    return JError::raiseWarning( 500, $db->getError() );
                 }
                continue;
            }

            /* New product to add to Hikashop */
			if (strncmp ($sku, 'bundle_', 7) == 0)
			{
				//bundle
				$bundle_id = substr ($sku, 7);
				$bundle = JoomdleHelperShop::get_bundle_info ($bundle_id);
				$name = $bundle['name'];
				$desc = $bundle['description'];
				$cost = $bundle['cost'];
				$currency = $bundle['currency'];
			}
			else
			{
				//Course
				$course_info = JoomdleHelperContent::getCourseInfo ($sku);
				$name = $course_info['fullname'];
				$desc = $course_info['summary'];
				$cost = $course_info['cost'];
				$currency = $course_info['currency'];
			}

			$product_class = hikashop_get('class.product');
			$element->categories = array ($courses_category);
			$element->related = array();
			$element->options = array();
			$element->product_name = $name;
			$element->product_description = $desc;
			$element->product_code = $sku;
			$element->product_published = 1;

			$query = "SELECT category_id FROM #__hikashop_category WHERE category_namekey='default_tax'";
			$db->setQuery($query);
			$tax_id = $db->loadResult();
			if ($tax_id)
			{
				$element->product_tax_id = $tax_id;

				$query = "SELECT tax_namekey FROM #__hikashop_taxation WHERE category_namekey='default_tax'";
				$db->setQuery($query);
				$tax_namekey = $db->loadResult();

				$query = "SELECT tax_rate FROM #__hikashop_tax WHERE tax_namekey=".$db->Quote($tax_namekey);
				$db->setQuery($query);
				$tax_rate = $db->loadResult();

				$div = $tax_rate + 1;
				$price_without_tax = $cost / $div;
				$cost = $price_without_tax;
			}
			$element->prices[0]->price_value = $cost;

			$query = "SELECT currency_id FROM #__hikashop_currency WHERE currency_code = '$currency'";
			$db->setQuery($query);
			$currency_id = $db->loadResult();
			$element->prices[0]->price_currency_id = $currency_id;
			$element->prices[0]->price_min_quantity = 0;

			$status = $product_class->save($element);
			if ($status)
			{
				$product_class->updateCategories($element,$status);
				$product_class->updatePrices($element,$status);
			}
        }
    }



    function dont_sell_courses_on_hikashop ($courses)
    {
        $db           =& JFactory::getDBO();

        foreach ($courses as $sku)
        {
            $query = "UPDATE  #__hikashop_product SET product_published = '0' where product_code = " . $db->Quote($sku);
            $db->setQuery($query);
            if (!$db->query()) {
                return JError::raiseWarning( 500, $db->getError() );
             }
        }
    }

	function create_bundle_on_hikashop ($bundle)
	{
        require_once( JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php' );
		$params = &JComponentHelper::getParams( 'com_joomdle' );
		$courses_category = $params->get( 'courses_category' );

		$db           =& JFactory::getDBO();

		$bundle_id = 'bundle_'.$bundle['id'];
		$sku = $bundle_id;

		$query = 'SELECT product_code ' .
				' FROM #__hikashop_product' .
				' WHERE product_code =' . $db->Quote($sku);
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if (count ($products))
		{
			/* Product already on Hikashop, just publish it */
			$query = "UPDATE  #__hikashop_product SET product_published = '1' where product_code = ". $db->Quote($sku);
			$db->setQuery($query);
			if (!$db->query()) {
				return JError::raiseWarning( 500, $db->getError() );
			 }
			return;
		}

		/* New product to add to Hikashop */
		$name = $bundle['name'];
		$desc = $bundle['description'];
		$cost = $bundle['cost'];
		$currency = $bundle['currency'];

		$product_class = hikashop_get('class.product');
		$element->categories = array ($courses_category);
		$element->related = array();
		$element->options = array();
		$element->product_name = $name;
		$element->product_description = $desc;
		$element->product_code = $sku;
		$element->product_published = 1;

		$element->prices[0]->price_value = $cost;
		$query = "SELECT currency_id FROM #__hikashop_currency WHERE currency_code = '$currency'";
		$db->setQuery($query);
		$currency_id = $db->loadResult();
		$element->prices[0]->price_currency_id = $currency_id;
		$element->prices[0]->price_min_quantity = 0;

		$status = $product_class->save($element);
		if ($status)
		{
			$product_class->updateCategories($element,$status);
			$product_class->updatePrices($element,$status);
		}
	}

	/* General functions */
	function send_confirmation_email ($email, $course_id)
	{
		$app = JFactory::getApplication();

		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$linkstarget = $comp_params->get( 'linkstarget' );
		$moodle_url = $comp_params->get( 'MOODLE_URL' );
		$email_subject = $comp_params->get( 'enrol_email_subject' );
		$email_text = $comp_params->get( 'enrol_email_text' );


		if ($linkstarget == 'wrapper')
		{
			/* XXX After and hour tryng and searching I could not find the GOOD way
			   to do this, so I do this kludge and it seems to work ;) 
			   */
			$url            = JURI::base();
			$pos =  strpos ($url, '/administrator/');
			if ($pos)
				$url = substr ($url, 0, $pos);
			$url = trim ($url, '/');
			$url            = $url.'/index.php?option=com_joomdle&view=wrapper&moodle_page_type=course&id='.$course_id;
		} else {
			$url = $moodle_url.'/course/view.php?id='.$course_id;
		}

		$course_info = JoomdleHelperContent::getCourseInfo ((int) $course_id);
		$name = $course_info['fullname'];

		$email_text = str_replace ('COURSE_NAME', $name, $email_text);
		$email_text = str_replace ('COURSE_URL', $url, $email_text);
		$email_subject = str_replace ('COURSE_NAME', $name, $email_subject);
		$email_subject = str_replace ('COURSE_URL', $url, $email_subject);


		// Set the e-mail parameters
		$from           = $app->getCfg('mailfrom');
        $fromname       = $app->getCfg('fromname');


		// Send the e-mail
		$mail_class = JMail::getInstance ();
		if (!$mail_class->sendMail($from, $fromname, $email, $email_subject, $email_text))
		{
				$this->setError('ERROR_SENDING_CONFIRMATION_EMAIL');
				return false;
		}

		return true;
	}

    function enrol_bundle ($username, $sku)
    {
		$user_id = JUserHelper::getUserId ($username);
        $user = JFactory::getUser ($user_id);
		$email =  $user->email;

        $bundle_id = substr ($sku, 7);
        $bundle = JoomdleHelperShop::get_bundle_info ($bundle_id);
        $courses = explode (',', $bundle['courses']);

		$comp_params = &JComponentHelper::getParams( 'com_joomdle' );
		$send_bundle_emails = $comp_params->get( 'send_bundle_emails' );
        $c = array ();
        foreach ($courses as $course_id)
        {
			if ($send_bundle_emails)
				JoomdleHelperShop::send_confirmation_email ($email, $course_id);
            $course['id'] = (int) $course_id;
            $c[] = $course;
        }
    
        JoomdleHelperContent::call_method ('multiple_enrol', $username, $c, 5);
    }
}
