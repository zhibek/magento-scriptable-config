<?php

class Zhibek_Migration_Helper_Data
{
    
    /**
     * Set a config value based on a path and scope
     * 
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Mage_Core_Model_Config_Data
     * @throws Exception
     */
    public function setConfig($path, $value, $scope = 'default', $scopeId = null)
    {
        $config = Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('path', $path);

        if ('default' !== $scope) {
            $config->addFieldToFilter('scope_id', $scopeId);
        }

        if (1 < $config->count()) {
            throw new Exception('Path/scope match multiple config elements.');
        } else if (1 === $config->count()) {
            $node = $config->getFirstItem();
        } else {
            $node = Mage::getModel('core/config_data')
                ->setScope($scope)
                ->setPath($path);
            if ('default' !== $scope) {
                $node->setScopeId($scopeId);
            }
        }
        
        // NOTE: triggers - model_config_data_save_before
        return $node
            ->setValue($value)
            ->save();
    }
    
}