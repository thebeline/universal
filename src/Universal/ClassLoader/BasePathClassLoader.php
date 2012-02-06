<?php 
namespace Universal\ClassLoader;

if( class_exists('\Universal\ClassLoader\BasePathClassLoader') )
    return;

/**
 * Base Path Classloader:
 *
 * In base path classloader, we dont check namespace
 *
 * $loader = new BasePathClassLoader( array( 
 *      'vendor/pear', 'external_vendor/src'
 * ) );
 * $loader->register();
 *
 */
class BasePathClassLoader 
{
    /**
     * library paths
     */
    public $paths = array();

    /**
     * use php include path ?
     *
     * @var boolean 
     */
    public $useIncludePath;

    public function __construct($paths)
    {
        $this->paths = $paths;
    }


    /**
     * extract PHP5LIB paths from env 
     */
    public function useEnvPhpLib()
    {
        $lib = getenv('PHP5LIB');
        if( $lib ) {
            $paths = explode( ':' , $lib );
            foreach( $paths as $path )
                $this->paths[] = $path;
        }
    }

    /**
     * find class file path
     *
     * @param string $fullclass
     */
    public function findClassFile($fullclass)
    {
        $fullclass = ltrim($fullclass,'\\');
        # echo "Fullclass: " . $fullclass . "\n";

        $subpath = null;
        if( ($r = strrpos($fullclass,'\\')) !== false ) {
            $namespace = substr($fullclass,0,$r);
            $classname = substr($fullclass,$r + 1);
            $subpath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace )
                    . DIRECTORY_SEPARATOR . str_replace( '_' , DIRECTORY_SEPARATOR , $classname ) 
                    . '.php';

            # echo "namespace: $ns in $namespace\n";
            foreach( $this->paths as $d ) {
                $path = $d . DIRECTORY_SEPARATOR . $subpath;
                if( file_exists($path) )
                    return $path;
            }
        }
        else {
            // use prefix to load class (pear style), convert _ to DIRECTORY_SEPARATOR.
            $subpath = str_replace('_', DIRECTORY_SEPARATOR, $fullclass).'.php';
            foreach ($this->paths as $dir ) {
                $file = $dir.DIRECTORY_SEPARATOR.$subpath;
                if (file_exists($file))
                    return $file;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($subpath))
            return $file;
    }

    public function loadClass($class)
    {
        if ($file = $this->findClassFile($class)) {
            # echo "File: $file.\n";
            require $file;
        }
    }


    /**
     * register to spl_autoload_register
     *
     * @param boolean $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }


    /**
     * unregister the spl autoloader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }


    /**
     * use include path
     *
     * @param boolean $bool
     */
    public function useIncludePath($bool)
    {
        $this->useIncludePath = $bool;
    }

}

