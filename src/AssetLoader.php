<?php
namespace stratease\AssetFly;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Asset\AssetCache;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Filter\UglifyCss;

class AssetLoader
{
    use ConfiguratorTrait;

    /**
     * @var string Directory relative to the web root where we dump compiled css files
     */
    protected $dumpCssDirectory = 'css/';
    /**
     * @var string Directory relative to the web root where we dumpe compiled js files
     */
    protected $dumpJsDirectory = 'js/';
    /**
     * @var bool Flag to cache the compiled file for subsequent requests
     */
    protected $cache = true;
    /**
     * @var string Path to the web/document root. Various other relative paths will use this as the prepend
     */
    protected $webDirectory;

    /**
     * @var bool Flag to output raw and precompiled files, no minification or concatting done
     */
    protected $debug = false;
    
    /**
     *@var bool internal flag to determine if we compiled filters yet
     */
    protected $isCompiled = false;
    protected $filters = [];
    protected $assets = [];
    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
        $this->init();
    }

    public function init()
    {
        // setup predefined filter groups
        $this->addFilter('css', new UglifyCss());
    }
    /**
     * @param bool $value Flag for caching
     * @return $this
     */
    public function setCache($value)
    {
        $this->cache = $value;

        return $this;
    }

    /**
     * @return bool Flag for caching
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param string $value Absolute path to web root
     * @return $this
     */
    public function setWebDirectory($value)
    {
        $this->webDirectory = $value;

        return $this;
    }

    /**
     * @return string Absolute path to web root
     */
    public function getWebDirectory()
    {
        return $this->webDirectory;
    }

    /**
     * @param $filterGroup
     * @param FilterInterface $filter
     * @return $this
     */
    public function addFilter($filterGroup, FilterInterface $filter)
    {
        // filter group must be alpha numeric, underscored
        if(preg_match('/[^a-zA-Z0-9_]/', $filterGroup)) {
           throw new \Exception("Filter Group '".$filterGroup."' name is invalid. It may only be alpha numeric with underscores.");
        }
        $filters = isset($this->filters[$filterGroup]) ? $this->filters[$filterGroup] : [];
        $filters[] = $filter;
        $this->filters[$filterGroup] = $filters;

        return $this;
    }

    public function addAsset($filterGroup, $outputGroup, AssetInterface $asset)
    {
        $asset->setFilterGroup($filterGroup);
        $assets = isset($this->assets[$outputGroup]) ? $this->assets[$outputGroup] : [];
        $assets[] = $asset;

        $this->assets[$outputGroup] = $assets;
        
        return $this;
    }
    /**
     *
     * @param $value
     * @return $this
     */
    public function setDebug($value)
    {
        $this->debug = $value;

        return $this;
    }
    
    public function getFilters($filterGroup = null)
    {
        if($filterGroup === null) {
          
            return $this->filters;
        } else {
          
            return isset($this->filters[$filterGroup]) ? $this->filters[$filterGroup] : [];
        }
    }
    public function getAssets($outputGroup = null)
    {
        if($outputGroup === null) {
          
            return $this->assets;
        } else {
          
            return isset($this->assets[$outputGroup]) ? $this->assets[$outputGroup] : [];
        }
    }
    public function compile($outputGroup)
    {
        $assets = $this->getAssets($outputGroup);

        // process all the assets 
        foreach($assets as $i => $asset)
        {
            if($filters = $this->getFilters($asset->getFilterGroup())) {
                // check if cached
                $dir = $this->getWebDirectory();
                switch($asset::getFileType())
                {
                    case AssetBase::F_CSS:
                        $dir = $dir."/".$this->getDumpCssDirectory();
                        break;
                    case AssetBase::F_JS:
                        $dir = $dir."/".$this->getDumpJsDirectory();
                        break;
                    default:
                        $dir = null;
                        throw new \Exception("Invalid asset file type '".$asset::getFileType()."'");
                }
                $assetCache = new AssetCache($dir, $asset, $filters);
                // cached ??
                if($aCache = $assetCache->getCache()) {
                    $asset = $aCache;
                // else not cached, process
                } else {
                    // if debug (dev mode) only do precompilers
                    if($this->getDebug()) {
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
                }
                // overwrite with processed asset
                unset($assets[$i]);
                $assets[$i] = $asset;
            }
        }
        
        return $assets;
    }

    public function getAssetUrls($outputGroup)
    {
        $urls = [];
        
        $assets = $this->compile($outputGroup);
        // if debug output each asset separately
        // else concat 'em
        if(!$this->getDebug()) {
            $assets = AssetBase::concat($assets);
        }
        // get urls
        foreach($assets as $asset)
        {
            // write ...
            // then give url...
            $urls[] = ...
        }
        return $urls;
    }

    /**
     * Gets debug
     * @return debug
     */
    public function getDebug()
    {
        return $this->debug;
    }


    public function setDumpCssDirectory($dir)
    {
        $this->dumpCssDirectory = $dir;
        return $this;
    }

    public function setDumpJsDirectory($dir)
    {
        $this->dumpJsDirectory = $dir;
        return $this;
    }

    public function getDumpJsDirectory()
    {
        return $this->dumpJsDirectory;
    }

    public function getDumpCssDirectory()
    {
        return $this->dumpCssDirectory;
    }
}