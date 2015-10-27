<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Attribute_Builder
{
    const TYPE_TEXT            = 'text';
    const TYPE_TEXTAREA        = 'textarea';
    const TYPE_SELECT          = 'select';
    const TYPE_MULTIPLE_SELECT = 'multiselect';
    const TYPE_BOOLEAN         = 'boolean';
    const TYPE_PRICE           = 'price';
    const TYPE_DATE            = 'date';

    const SCOPE_STORE   = 0;
    const SCOPE_GLOBAL  = 1;
    const SCOPE_WEBSITE = 2;

    const CODE_MAX_LENGTH = 30;

    /** @var Mage_Eav_Model_Entity_Attribute */
    private $attributeObj = null;

    private $code;
    private $primaryLabel;
    private $inputType;

    private $entityTypeId;

    private $options = array();
    private $params = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveAttribute();
    }

    // ---------------------------------------

    private function init()
    {
        if (is_null($this->entityTypeId)) {
            $this->entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if (is_null($this->inputType)) {
            $this->inputType = self::TYPE_TEXT;
        }

        $this->attributeObj = Mage::getModel('eav/entity_attribute')
                                        ->loadByCode($this->entityTypeId, $this->code);

        return $this;
    }

    private function saveAttribute()
    {
        if ($this->attributeObj->getId()) {
            return array('result' => true,
                         'obj'    => $this->attributeObj,
                         'code'   => $this->attributeObj->getAttributeCode());
        }

        if (!$this->validate()) {
            return array('result' => false, 'error' => 'Attribute builder. Validation failed.');
        }

        $data = $this->params;
        $data['attribute_code'] = $this->code;
        $data['frontend_label'] = array(Mage_Core_Model_App::ADMIN_STORE_ID => $this->primaryLabel);
        $data['frontend_input'] = $this->inputType;

        $data['source_model']  = Mage::helper('catalog/product')->getAttributeSourceModelByInputType($this->inputType);
        $data['backend_model'] = Mage::helper('catalog/product')->getAttributeBackendModelByInputType($this->inputType);

        !isset($data['is_global'])               && $data['is_global'] = self::SCOPE_STORE;
        !isset($data['is_configurable'])         && $data['is_configurable'] = 0;
        !isset($data['is_filterable'])           && $data['is_filterable'] = 0;
        !isset($data['is_filterable_in_search']) && $data['is_filterable_in_search'] = 0;

        $this->attributeObj = Mage::getModel('catalog/resource_eav_attribute');

        if (is_null($this->attributeObj->getIsUserDefined()) || $this->attributeObj->getIsUserDefined() != 0) {
            $data['backend_type'] = $this->attributeObj->getBackendTypeByInput($this->inputType);
        }

        // default value
        if (empty($data['default_value'])) {
            unset($data['default_value']);
        }
        // ---------------------------------------

        !isset($data['apply_to']) && $data['apply_to'] = array();

        // prepare options
        foreach ($this->options as $optionValue) {

            $code = 'option_'.substr(sha1($optionValue), 0, 6);
            $data['option']['value'][$code] = array(Mage_Core_Model_App::ADMIN_STORE_ID => $optionValue);
        }
        // ---------------------------------------

        $this->attributeObj->addData($data);

        $this->attributeObj->setEntityTypeId($this->entityTypeId);
        $this->attributeObj->setIsUserDefined(1);

        try {
            $this->attributeObj->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true,
                     'obj'    => $this->attributeObj,
                     'code'   => $this->attributeObj->getAttributeCode());
    }

    private function validate()
    {
        $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
        if (!$validatorAttrCode->isValid($this->code)) {
            return false;
        }

        if (empty($this->primaryLabel)) {
            return false;
        }

        /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
        $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
        if (!$validatorInputType->isValid($this->inputType)) {
            return false;
        }

        if (in_array($this->inputType, array(self::TYPE_MULTIPLE_SELECT)) && empty($this->options)) {
            return false;
        }

        return true;
    }

    public function generateCodeByLabel()
    {
        $attributeCode = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->primaryLabel);
        $attributeCode = preg_replace('/[^0-9a-z]/i','_', $attributeCode);

        $abc = 'abcdefghijklmnopqrstuvwxyz';
        if (preg_match('/^\d{1}/', $attributeCode, $matches)) {
            $index = $matches[0];
            $attributeCode = $abc[$index].'_'.$attributeCode;
        }

        if (strlen($attributeCode) > self::CODE_MAX_LENGTH) {

            $originalAttributeHash = sha1($attributeCode);
            $attributeCode  = substr($attributeCode, 0, self::CODE_MAX_LENGTH - 5);
            $attributeCode .= '_' . substr($originalAttributeHash, 0, 4);
        }

        $attributeCode = strtolower($attributeCode);

        // system reserved values
        $systemValues = array('sku', 'store', 'type', 'visibility', 'attribute_set', 'price', 'name', 'description',
                              'weight', 'status', 'qty', 'image', 'small_image', 'thumbnail', 'media_gallery',
                              'options');

        in_array($attributeCode, $systemValues) && $attributeCode = 'esc_' . $attributeCode;
        // ---------------------------------------

        $this->code = $attributeCode;
        return $this;
    }

    //########################################

    public function setCode($value)
    {
        $this->code = $value;
        return $this;
    }

    public function setLabel($value)
    {
        $this->primaryLabel = $value;
        return $this;
    }

    public function setInputType($value)
    {
        $this->inputType = $value;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->params = $value;
        return $this;
    }

    public function setDefaultValue($value)
    {
        $this->params['default_value'] = $value;
        return $this;
    }

    public function setScope($value)
    {
        $this->params['is_global'] = $value;
        return $this;
    }

    public function setEntityTypeId($value)
    {
        $this->entityTypeId = $value;
        return $this;
    }

    //########################################
}