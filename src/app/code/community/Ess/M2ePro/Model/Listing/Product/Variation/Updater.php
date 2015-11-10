<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    //########################################

    abstract public function process(Ess_M2ePro_Model_Listing_Product $listingProduct);

    //########################################

    public function beforeMassProcessEvent() {}

    public function afterMassProcessEvent() {}

    //########################################
}