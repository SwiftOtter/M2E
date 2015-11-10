<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults
    extends Ess_M2ePro_Model_Synchronization_Task_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS;
    }

    /**
     * @return null
     */
    protected function getNick()
    {
        return NULL;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Defaults_Processing') ? false : $result;
        $result = !$this->processTask('Defaults_DeletedProducts') ? false : $result;
        $result = !$this->processTask('Defaults_AddedProducts') ? false : $result;
        $result = !$this->processTask('Defaults_StopQueue') ? false : $result;
        $result = !$this->processTask('Defaults_Inspector') ? false : $result;

        return $result;
    }

    //########################################
}