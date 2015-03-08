<?php
namespace stratease\AssetFly;
use stratease\AssetFly\Asset\AssetBase;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Asset\AssetCache;
use stratease\AssetFly\Asset\Assets\TextFile;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Filter\Filters\UglifyCss;
use stratease\AssetFly\Filter\Filters\Sass;
use stratease\AssetFly\Filter\Filters\UglifyJs;
use stratease\AssetFly\Filter\Filters\CssRelativeRewrite;

class AssetLoader
{
    use ConfiguratorTrait;

    /**
     * @var string Directory relative to the web root where we dump compiled css files
     */
    protected static $dumpCssDirectory = '/assets/css/';
    /**
     * @var string Directory relative to the web root where we dump compiled js files
     */
    protected static $dumpJsDirectory = '/assets/js/';
    /**
     * @var bool Flag to cache the compiled file for subsequent requests
     */
    protected static $cache = true;
    /**
     * @var string Path to the web/document root. Various other relative paths will use this as the prepend
     */
    protected static $webDirectory;

    /**
     * @var bool Flag to output raw and precompiled files, no minification or concatting done
     */
    protected static $debug = false;
    /**
     * @var bool Flag whether init was already run
     */
    protected static $wasInitd = false;
    /**
     *@var bool internal flag to determine if we compiled filters yet
     */
    protected static $isCompiled = false;
    protected static $filters = [];
    protected static $assets = [];
    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
        self::init();
    }

    /**
     * Loads a standard set of filters
     * @throws \Exception
     */
    public static function init()
    {
        if(!self::$wasInitd) {
            // setup predefined filter groups
            // vanilla css
            self::addFilter('css', new CssRelativeRewrite()); // first fix relative urls
            self::addFilter('css', new UglifyCss());

            // sass
            $sass = new Sass();
            $sass->setIfDebugCallable([$sass, 'addDebugFlags']);
            self::addFilter('sass', $sass);
            self::addFilter('sass', new CssRelativeRewrite());
            self::addFilter('sass', new UglifyCss());
            // js
            self::addFilter('js', new UglifyJs());
            self::$wasInitd = true;
        }
    }
    /**
     * @param bool $value Flag for caching
     * @return $this
     */
    public static function setCache($value)
    {
        self::$cache = $value;
    }

    /**
     * @return bool Flag for caching
     */
    public static function doCache()
    {
        return self::$cache;
    }

    /**
     * @param string $value Absolute path to web root
     * @return $this
     */
    public static function setWebDirectory($value)
    {
        self::$webDirectory = $value;
    }

    /**
     * @return string Absolute path to webs document root
     */
    public static function getWebDirectory()
    {
        // default?
        if(!self::$webDirectory
            && isset($_SERVER['DOCUMENT_ROOT'])) {
            self::$webDirectory = $_SERVER['DOCUMENT_ROOT'];
        }
		if(!self::$webDirectory
		   || !realpath(self::$webDirectory)) {			
			throw new \Exception("Unable to locate the web directory '".self::$webDirectory."'. You must define a valid web directory.");
		}

        return self::$webDirectory;
    }

    /**
     * @param $filterGroup
     * @param FilterInterface $filter
     * @throws \Exception
     
     */
    public static function addFilter($filterGroup, FilterInterface $filter)
    {
        // filter group must be alpha numeric, underscored
        if(preg_match('/[^a-zA-Z0-9_]/', $filterGroup)) {
           throw new \Exception("Filter Group '".$filterGroup."' name is invalid. It may only be alpha numeric with underscores.");
        }
        $filters = isset(self::$filters[$filterGroup]) ? self::$filters[$filterGroup] : [];
        $filters[] = $filter;
        self::$filters[$filterGroup] = $filters;
    }

    /**
     * @todo File type details of this method should be contained within an 'asset/filter collection', as it is more abstracted from the specific file handling (asset job)
     * @param $filterGroup
     * @param $outputGroup
     * @param AssetInterface $asset
     * @throws \Exception
     */
    public static function addAsset($filterGroup, $outputGroup, AssetInterface $asset)
    {
        $asset->setFilterGroup($filterGroup);

        $assets = isset(self::$assets[$outputGroup]) ? self::$assets[$outputGroup] : [];
        $assets[] = $asset;

        self::$assets[$outputGroup] = $assets;
    }

    /**
     *
     * @param $value
     */
    public static function setDebug($value)
    {
        self::$debug = $value;
    }

    /**
     * @param null $filterGroup
     * @return array
     */
    public static function getFilters($filterGroup = null)
    {
        if($filterGroup === null) {
          
            return self::$filters;
        } else {
          
            return isset(self::$filters[$filterGroup]) ? self::$filters[$filterGroup] : [];
        }
    }

    /**
     * @param null $outputGroup
     * @return array
     */
    public static function getAssets($outputGroup = null)
    {
        if($outputGroup === null) {
          
            return self::$assets;
        } else {
          
            return isset(self::$assets[$outputGroup]) ? self::$assets[$outputGroup] : [];
        }
    }

    /**
     * @param $outputGroup
     * @return array
     * @throws \Exception
     */
    public function compile($outputGroup)
    {
        $assets = self::getAssets($outputGroup);

        // process all the assets 
        foreach($assets as $i => $asset)
        {
            if($filters = self::getFilters($asset->getFilterGroup())) {
                // check if cached
                $dir = str_replace("//", "/", self::getWebDirectory().'/'.$asset->getDumpDirectory());
                $assetCache = new AssetCache($dir, $asset, $filters);
                // cached ??
                if(self::doCache() === true
                    && ($aCache = $assetCache->getCache())) {
                    $asset = $aCache;
                // else not cached, process
                } else {
                    // if debug (dev mode) only do precompilers
                    if(self::getDebug()) {
                        foreach($filters as $filter)
                        {
                            if($filter::isPrecompiler()) {
                                $asset = $filter->processAsset($asset);
                            }
                        }
                    } else {
                        foreach($filters as $filter)
                        {
                            // do our magic!
                            $asset = $filter->processAsset($asset);
                        }
                    }
                    // save file
                    $asset->generateOutputName($filters)->dumpToOutput();
                }
                // overwrite with processed asset
                unset($assets[$i]);
                $assets[$i] = $asset;
            }
        }
        
        return $assets;
    }

    /**
     * @todo temporary ... function doesn't belong here!!
     * @param array $assets
     * @return null|TextFile
     */
    public function concat(array $assets)
    {
        if(isset($assets[0])) {
            // our concat'd file name?
            switch($assets[0]->getFileType())
            {
                case AssetBase::F_LESS:
                case AssetBase::F_SASS:
                case AssetBase::F_SCSS:
                case AssetBase::F_CSS:
                    $ext = 'css';
                    break;
                case AssetBase::F_JS:
                    $ext = 'js';
                    break;
                default:
                    return null;
            }
            // our concat file path
            $filePath = self::getWebDirectory().'/'.
                    $assets[0]->getDumpDirectory().'/'.
                    sha1(json_encode($assets)).'_cat.'.$ext;

            // check if file exists...
            if(is_file($filePath)) {
                $asset = new TextFile($filePath);
            }
            else {
                // if it doesn't generate...
                foreach($assets as $ass) {
                    file_put_contents($filePath, $ass->getContent()."\n", FILE_APPEND);
                }
                $asset = new TextFile($filePath);
            }

            // return our new asset
            return $asset;
        }
    }

    /**
     * @param $outputGroup
     * @return array
     */
    public static function getAssetUrls($outputGroup)
    {
        $urls = [];
        $assetLoader = new AssetLoader();
        $assets = $assetLoader->compile($outputGroup);
        // if debug output each asset separately
        // else concat 'em
        if(!self::getDebug()) {
            // @todo This should be encapsulated into a post processing hook, as this depends on the file type, more of an asset collection responsibility.
            if($asset = $assetLoader->concat($assets)) {
                $assets = [$asset];
            }

        }

        // get urls
        foreach($assets as $asset)
        {
            // then give url...
            $urls[] = $asset->getOutputPath();
        }
        return $urls;
    }

    /**
     * Gets debug
     * @return debug
     */
    public static function getDebug()
    {
        return self::$debug;
    }


    public static function setDumpCssDirectory($dir)
    {
        self::$dumpCssDirectory = $dir;
    }

    public static function setDumpJsDirectory($dir)
    {
        self::$dumpJsDirectory = $dir;
    }

    public static function getDumpJsDirectory()
    {
        return self::$dumpJsDirectory;
    }

    public static function getDumpCssDirectory()
    {
        return self::$dumpCssDirectory;
    }
}