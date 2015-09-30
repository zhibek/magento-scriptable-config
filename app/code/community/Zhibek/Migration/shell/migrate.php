<?php

// Non-standard path required to reference shell/abstract.php in /magento directory (outside of /vendor)
require_once __DIR__ . '/../../../../../../../../../magento/shell/abstract.php';

class Zhibek_Migration_Shell_Migrate extends Mage_Shell_Abstract
{
    
    /**
     * Run script
     */
    public function run()
    {
        if ($this->getArg('migrate')) {
                
            Mage::app();
            $updates = Mage_Core_Model_Resource_Setup::applyAllUpdates();

            if ($updates) {
                print('Migrations executed successfully.' . PHP_EOL);
                exit(0);
            } else {
                print('Migrations did not return success code.' . PHP_EOL);
                exit(1);
            }
        
        } else {
            print $this->usageHelp();
            exit(1);
        }
        
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f migrate.php -- [options]
        php -f migrate.php migrate

  migrate           Run Magento resource migrations
  help              This help

USAGE;
    }
}

$shell = new Zhibek_Migration_Shell_Migrate();
$shell->run();