<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupSwitcher
 * @copyright  Copyright (c) 2009 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupSwitcher
 * @author     Vinai Kopp <vinai@netzarbeiter.com>
 */
class Netzarbeiter_GroupSwitcher_Block_Adminhtml_Groupswitcher_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'GroupSwitcher';
		$this->_controller = 'adminhtml_groupswitcher_rules';
		$this->_mode = 'edit';

		$this->_updateButton('save', 'label', Mage::helper('GroupSwitcher')->__('Save Rule'));
		$this->_updateButton('delete', 'label', Mage::helper('GroupSwitcher')->__('Delete Rule'));

		$this->_addButton('save_and_continue', array(
				'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick' => 'saveAndContinueEdit()',
				'class' => 'save',
		), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('form_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'edit_form');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
    }

	public function getHeaderText()
	{
		if (Mage::registry('groupswitcher_data') && Mage::registry('groupswitcher_data')->getId())
		{
			return Mage::helper('GroupSwitcher')->__('Edit Rule "%s"', $this->htmlEscape(Mage::registry('groupswitcher_data')->getName()));
		}
		else
		{
			return Mage::helper('GroupSwitcher')->__('New Rule');
		}
	}
}