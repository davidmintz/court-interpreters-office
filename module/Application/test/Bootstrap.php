<?php


/**
 * http://framework.zend.com/manual/current/en/tutorials/unittesting.html
 */


namespace ApplicationTest;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

putenv('environment=testing');

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        static::initAutoloader();

        // use ModuleManager to load this module and its dependencies
        $config = array(
            'module_listener_options' => array(
                'module_paths' => $zf2ModulePaths,
                'config_glob_paths' => array(
                    // real application config
                   // 'config/autoload/{,*.}{global,local}.php',
                    // test application config
                    __DIR__.'/config/autoload/{,*.}{global,local}.php',
                ),
            ),
            'modules' => array(

                'Application',
                'Requests',
                
            ),
            
        );
        
        $serviceManager = new ServiceManager([]);
        $serviceManager->setService('ApplicationConfig', $config);
        
        //$serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
        //FixtureManager::start();
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);

    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }
    /**
     * see https://gist.github.com/Saeven/cae4902dd656466bcff5
     * for a tweak that is required for ZF 2.5.*
     * 
     */
    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        }
        // this makes it find Doctrine demo stuff from https://github.com/samsonasik/DoctrineFixtureDemo
        // see also https://samsonasik.wordpress.com/2015/03/24/using-doctrine-data-fixture-for-testing-querybuilder-inside-repository/
        //$loader->add('DoctrineFixtureDemotest',__DIR__);
        //$loader->add('DoctrineFixtureDemo',__DIR__);
        
        include $vendorPath . '/zendframework/zend-loader/src/AutoloaderFactory.php';
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                    // trying thus to register another namespace doesn't work 
                    //'DoctrineFixtureDemotest' => __DIR__,
                    //'DoctrineFixtureDemo' => __DIR__.'/src',
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}


Bootstrap::init();
Bootstrap::chroot();

$serviceManager = Bootstrap::getServiceManager();
printf("\$serviceManager is a %s\n",get_class($serviceManager));

print_r(
    get_class_methods($serviceManager)
);
$em = $serviceManager->get('entity-manager');
/*
$serviceManager = Bootstrap::getServiceManager();
$services = $serviceManager->getRegisteredServices();
//print_r(array_keys($services));
print_r($services);
*/
/*
print_r(get_class_methods($serviceManager));
echo "allow override: " ;var_dump($serviceManager->getAllowOverride());

echo gettype($serviceManager->getRegisteredServices());
*/
//putenv('environment=testing');
//echo "\nleaving ".__FILE__ . "\n";

//\DoctrineFixtureDemotest\FixtureManager::start();

