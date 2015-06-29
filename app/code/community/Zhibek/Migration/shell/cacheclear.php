<?php

// Non-standard path required to reference shell/abstract.php within magento vendor directory
require_once __DIR__ . '/../../../../../../../../magento/core/shell/abstract.php';

class Zhibek_Migration_Shell_CacheClear extends Mage_Shell_Abstract
{

    /**
     * Cleans image cache using catalog/product_image model.
     */
    protected function cleanImageCache()
    {
        try {
            echo "Cleaning image cache... ";
            flush();
            echo Mage::getModel('catalog/product_image')->clearCache();
            echo "[OK]" . PHP_EOL . PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }

    /**
     * Cleans magento data cache:
     * - config,
     * - layout,
     * - block_html
     * - translate,
     * - collections,
     * - eav,
     * - config_api
     */
    protected function cleanDataCache()
    {
        try {
            echo "Cleaning data cache:" . PHP_EOL;
            flush();
            $types = Mage::app()->getCacheInstance()->getTypes();
            foreach ($types as $type => $data) {
                echo "Removing $type ... ";
                echo Mage::app()->getCacheInstance()->clean($data["tags"]) ? "[OK]" : "[ERROR]";
                echo PHP_EOL;
            }
            echo PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }

    protected function cleanMergedJsCss()
    {
        try {
            echo "Cleaning merged JS/CSS... ";
            flush();
            Mage::getModel('core/design_package')->cleanMergedJsCss();
            Mage::dispatchEvent('clean_media_cache_after');
            echo "[OK]" . PHP_EOL . PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }

    protected function cleanStoredCache()
    {
        try {
            echo "Cleaning stored cache... ";
            flush();
            echo Mage::app()->getCacheInstance()->clean() ? "[OK]" : "[ERROR]";
            echo PHP_EOL . PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }

    /**
     * Does a rmdir on:
     * - cache,
     * - var/full_page_cache
     * - var/minifycache
     * - session dir
     */
    protected function cleanFiles()
    {
        try {
            echo "Cleaning files:" . PHP_EOL;
            flush();
            echo "Cache... ";
            $this->_rRmDirContent(Mage::getBaseDir('cache'));
            echo "[OK]" . PHP_EOL;
            echo "Full page cache... ";
            $this->_rRmDirContent(Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'full_page_cache');
            echo "[OK]" . PHP_EOL;
            echo "Minify cache... ";
            $this->_rRmDirContent(Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . '/minifycache');
            echo "[OK]" . PHP_EOL;
            echo "Session... ";
            $this->_rRmDirContent(Mage::getBaseDir('session'));
            echo "[OK]" . PHP_EOL;
            echo PHP_EOL;
        } catch (Exception $e) {
            die("[ERROR:" . $e->getMessage() . "]" . PHP_EOL);
        }
    }

    protected function cleanAll()
    {
        $this->cleanImageCache();
        $this->cleanDataCache();
        $this->cleanStoredCache();
        $this->cleanMergedJsCss();
        $this->cleanFiles();
    }

    /**
     * Run script
     */
    public function run()
    {
        ini_set("display_errors", 1);
        Mage::app('admin')->setUseSessionInUrl(false);
        Mage::getConfig()->init();
        $caches = array('image', 'data', 'stored', 'js_css', 'files');
        if ($this->getArg('info')) {
            echo 'Allowed caches: ' . PHP_EOL;
            foreach ($caches as $cache) {
                echo "\t" . $cache . PHP_EOL;
            }
            die();
        }

        if ($this->getArg('all')) {
            $this->cleanAll();
            die();
        }

        if ($this->getArg('clean') && in_array($this->getArg('clean'), $caches)) {
            switch ($this->getArg('clean')) {
                case 'image':
                    $this->cleanImageCache();
                    break;
                case 'data':
                    $this->cleanDataCache();
                    break;
                case 'stored':
                    $this->cleanStoredCache();
                    break;
                case 'js_css':
                    $this->cleanMergedJsCss();
                    break;
                case 'files':
                    $this->cleanFiles();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Removes all elements contained in the given directory
     * @param string $dir directory containing elements to remove
     */
    private function _rRmDirContent($dir)
    {
        $items = array_diff(scandir($dir), array('..', '.'));
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->_rRmDir($path) : unlink($path);
        }
    }

    /**
     * Removes a directory and all elements contained
     * @param string $dir directory to remove
     */
    private function _rRmDir($dir)
    {
        if (is_dir($dir)) {
            $objects = array_diff(scandir($dir), array('..', '.'));
            foreach ($objects as $object) {
                $path = $dir . DIRECTORY_SEPARATOR . $object;
                is_dir($path) ? $this->_rRmDir($path) : unlink($path);
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cacheclear.php -- [options]
        php -f cacheclear.php -- all

    --clean <cache>          Clean <cache>. Any of [image|data|stored|js_css|files]
    all                      Clean all caches
    info                     Show allowed caches
    help                     This help


USAGE;
    }

}

$shell = new Zhibek_Migration_Shell_CacheClear();
$shell->run();