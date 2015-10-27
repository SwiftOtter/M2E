AttributeCreator = Class.create();
AttributeCreator.prototype = {

    popupObj:  null,
    selectObj: null,

    // it is for close callback [in order to rest selected option for selectObj]
    attributeWasCreated: false,

    formId:         'general_create_new_attribute_form',
    addOptionValue: 'new-one-attribute',

    // ---------------------------------------

    initialize: function() {},

    // ---------------------------------------

    setSelectObj: function(selectObj)
    {
        this.selectObj = selectObj;
    },

    // ---------------------------------------

    showPopup: function()
    {
        var self = this,
            params = {};

        if (self.selectObj.getAttribute('allowed_attribute_types')) {
            params['allowed_attribute_types'] = self.selectObj.getAttribute('allowed_attribute_types');
        }

        if (self.selectObj.getAttribute('apply_to_all_attribute_sets') == '0') {
            params['apply_to_all_attribute_sets'] = '0';
        }

        if (self.selectObj.getAttribute('show_code_input') == '1') {
            params['show_code_input'] = '1';
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_general/getCreateAttributeHtmlPopup'), {
            method: 'post',
            asynchronous: true,
            parameters: params,
            onSuccess: function(transport) {

                self.popupObj = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: M2ePro.translator.translate('Creation of New Magento Attribute'),
                    top: 160,
                    maxHeight: 520,
                    width: 550,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show,
                    onOk: function() {
                        return self.onOkPopupCallback();
                    },
                    onCancel: function() {
                        return self.onCancelPopupCallback();
                    },
                    onClose: function() {
                        return self.onClosePopupCallback();
                    }
                });

                self.attributeWasCreated = false;
                self.popupObj.options.destroyOnClose = true;
                self.autoHeightFix();

                $('modal_dialog_message').insert(transport.responseText);
                $('modal_dialog_message').evalScripts();
            }
        });
    },

    create: function(attributeParams)
    {
        var self = this;

        MagentoMessageObj.clearAll();

        new Ajax.Request(M2ePro.url.get('adminhtml_general/createAttribute'), {
            method: 'post',
            asynchronous: true,
            parameters: attributeParams,
            onSuccess: function(transport) {

                var result = transport.responseText.evalJSON();

                if (!result || !result['result']) {
                    MagentoMessageObj.addError(result['error']);
                    self.onCancelPopupCallback();
                    return;
                }

                MagentoMessageObj.addSuccess(M2ePro.translator.translate('Attribute has been created.'));

                self.chooseNewlyCreatedAttribute(attributeParams, result);
            }
        });
    },

    onOkPopupCallback: function()
    {
        if (!new varienForm(this.formId).validate()) {
            return false;
        }

        this.create($(this.formId).serialize(true));
        this.attributeWasCreated = true;

        return true;
    },

    onCancelPopupCallback: function()
    {
        this.selectObj.value = this.selectObj.select('option').first().value;
        return true;
    },

    onClosePopupCallback: function()
     {
         if (!this.attributeWasCreated) {
             this.onCancelPopupCallback();
         }
         return true;
     },

    chooseNewlyCreatedAttribute: function(attributeParams, result)
    {
        var optionsTitles = [];
        this.selectObj.select('option').each(function(el) {
            el.removeAttribute('selected');
            optionsTitles.push(trim(el.innerHTML));
        });
        optionsTitles.push(attributeParams['store_label']);
        optionsTitles.sort();

        var neededOptionPosition = optionsTitles.indexOf(attributeParams['store_label']),
            beforeOptionTitle = optionsTitles[neededOptionPosition - 1];

        if (this.haveOptgroup()) {

            var option = new Element('option', {
                attribute_code: result['code'],
                class: 'simple_mode_disallowed',
                value: this.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup').down('option').value
            });

        } else {

            var option = new Element('option', { value: result['code']});
        }

        this.selectObj.select('option').each(function(el){

            if (trim(el.innerHTML) == beforeOptionTitle) {
                $(el).insert({after: option});
                return true;
            }
        });

        option.update(attributeParams['store_label']);
        option.setAttribute('selected', 'selected');

        this.selectObj.simulate('change');
    },

    // ---------------------------------------

    injectAddOption: function()
    {
        var self = this;

        var option = new Element('option', {
            style: 'color: brown;',
            value: this.addOptionValue
        }).update(M2ePro.translator.translate('Create a New One...'));

        self.haveOptgroup() ? self.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup').appendChild(option)
                            : self.selectObj.appendChild(option);

        $(self.selectObj).observe('change', function(event) {

            if (this.value == self.addOptionValue) {
                self.showPopup();
            }
        });
    },

    // ---------------------------------------

    autoHeightFix: function()
    {
        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '650px';
        }, 50);
    },

    haveOptgroup: function()
    {
        return Boolean(this.selectObj.down('optgroup.M2ePro-custom-attribute-optgroup'));
    },

    alreadyHaveAddedOption: function()
    {
        return Boolean(this.selectObj.down('option[value="' + this.addOptionValue + '"]'));
    }

    // ---------------------------------------
};