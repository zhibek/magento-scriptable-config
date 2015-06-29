<?php

// Non-standard path required to reference shell/abstract.php within magento vendor directory
require_once __DIR__ . '/../../../../../../../../magento/core/shell/abstract.php';

class Zhibek_Migration_Shell_Install extends Mage_Shell_Abstract
{
    
    const DEFAULT_CONFIG_LOCALE = 'en_GB';
    const DEFAULT_CONFIG_TIMEZONE = 'Europe/London';
    const DEFAULT_CONFIG_DEFAULT_CURRENCY = 'GBP';
    
    protected $_defaultConfig = array(
        'license_agreement_accepted' => true,
        'locale' => self::DEFAULT_CONFIG_LOCALE,
        'timezone' => self::DEFAULT_CONFIG_TIMEZONE,
        'default_currency' => self::DEFAULT_CONFIG_DEFAULT_CURRENCY,
        'db_model' => null,
        'db_host' => null, // required
        'db_name' => null, // required
        'db_user' => null, // required
        'db_pass' => null, // required
        'db_prefix' => null,
        'url' => null, // required
        'skip_url_validation' => true,
        'use_rewrites' => true,
        'use_secure' => false,
        'secure_base_url' => null, // required
        'use_secure_admin' => false,
        'admin_lastname' => null, // required
        'admin_firstname' => null, // required
        'admin_email' => null, // required
        'admin_username' => null, // required
        'admin_password' => null, // required
        'encryption_key' => null,
        'session_save' => null,
        'admin_frontname' => null,
        'enable_charts' => null,
    );
    
    /**
     * Run script
     */
    public function run()
    {
        if ($this->getArg('install')) {
            
            // use path in arguments if provided (for configuration options)
            if ($this->getArg('path')) {
                $path = $this->getArg('path');
                if (!is_file($path)) {
                    printf('Specified path "%s" does not exist or is not a file.', $path);
                    exit;
                }
            
            // otherwise default to app/etc/install.xml (for configuration options)
            } else {
                $path = realpath(Mage::getRoot() . DS . 'etc');
                if (!$path) {
                    print('Problem finding application root.');
                    exit;
                }
                $path .= DS . 'install.xml';
                if (!is_file($path)) {
                    printf('Install file path "%s" does not exist or is not a file.', $path);
                    exit;
                }
            }
            
            $app = Mage::app('default');

            /* @var $installer Mage_Install_Model_Installer_Console */
            $installer = Mage::getSingleton('install/installer_console');
            
            // generate config based on defaults in $this->_defaultConfig 
            // and the options in the configuration file (see above)
            $config = $this->_getConfig($path);

            // run the installer and hope for success
            if ($installer->init($app) && $installer->setArgs($config) && $installer->install()) {
                print 'SUCCESS: ' . $installer->getEncryptionKey() . "\n";
                exit;
            // otherwise print errors
            } else {
                if ($installer->getErrors()) {
                    print "\nFAILED\n";
                    foreach ($installer->getErrors() as $error) {
                        print $error . "\n";
                    }
                }
                exit;
            }
        
        } else {
            print $this->usageHelp();
            exit;
        }
    }
    
    /**
     * Combine default config with options in config file
     * 
     * @param string $path
     * @return array
     */
    protected function _getConfig($path)
    {
        $config = new Zend_Config_Xml($path);
        return array_merge($this->_defaultConfig, $config->toArray());
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f install.php -- [options]
        php -f install.php install

  install           Install Magento
  help              This help

USAGE;
    }
}

$shell = new Zhibek_Migration_Shell_Install();
$shell->run();