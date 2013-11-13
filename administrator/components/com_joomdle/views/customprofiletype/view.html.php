<?php
/**
 * @author Antonio Durán Terrés
 * @package Joomdle
 * @license GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
// Import Joomla! libraries
jimport( 'joomla.application.component.view');
require_once( JPATH_COMPONENT.'/helpers/content.php' );
require_once( JPATH_COMPONENT.'/helpers/profiletypes.php' );

class JoomdleViewCustomprofiletype extends JViewLegacy {

    protected $form;

    protected $item;

    function display($tpl = null) {
        global $mainframe, $option;

        $this->form         = $this->get('Form');
        $this->item         = $this->get('Item');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        parent::display($tpl);
        $this->addToolbar();

    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);

        JToolbarHelper::title(JText::_('COM_JOOMDLE_VIEW_PROFILETYPES_TITLE'), 'customprofiletype');
        JToolbarHelper::apply('customprofiletype.apply');
        JToolbarHelper::save('customprofiletype.save');

        if (empty($this->item->id))  {
            JToolbarHelper::cancel('customprofiletype.cancel');
        } else {
            JToolbarHelper::cancel('customprofiletype.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}

/*

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
	    global $mainframe, $option;

	$mainframe = JFactory::getApplication();

	$id = JRequest::getVar( 'profiletype_id' );
	$task = JRequest::getVar( 'task' );
	$params = &JComponentHelper::getParams( 'com_joomdle' );

	if (!$params->get( 'use_profiletypes' ))
	{
		echo JText::_('COM_JOOMDLE_PROFILE_TYPES_INTEGRATION_NOT_ENABLED');
		return;
	}

	if ($task == 'edit')
    {
        $id = JRequest::getVar( 'profiletype_id' );
        $this->profile = JoomdleHelperProfiletypes::get_profiletype_data ($id);

        $roles_moodle = JoomdleHelperContent::call_method ('get_roles');

        $roles = array ();
        $roles[] = JHTML::_('select.option',  0, '- '. JText::_( 'COM_JOOMDLE_SELECT_ROLE' ) .' -');

        foreach ($roles_moodle as $role)
            $roles[] = JHTML::_('select.option',  $role['id'], $role['name']);

        $lists['roles']  = JHTML::_('select.genericlist',   $roles, 'roles', 'class="inputbox" size="1"', 'value', 'text', $this->profile->moodle_role );

        $this->assignRef('lists',               $lists);


        $tpl = 'item';
        parent::display($tpl);
        return;
    }

}
*/
?>
