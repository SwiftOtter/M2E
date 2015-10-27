<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_GeneralId
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        $generalId = $this->getBuyListingProduct()->getGeneralId();
        if (empty($generalId)) {
            $generalId = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

            if (!empty($generalId)) {
                $this->data['general_id_mode'] = $this->getBuyListing()->getGeneralIdMode();
            }
        }

        // M2ePro_TRANSLATIONS
        // Product cannot be Listed because Rakuten.com SKU is not specified.
        if (empty($generalId)) {
            $this->addMessage('Product cannot be Listed because Rakuten.com SKU is not specified.');
            return false;
        }

        $this->data['general_id'] = $generalId;

        return true;
    }

    //########################################
}