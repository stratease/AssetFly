<?php
namespace stratease\AssetFly;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\Filter\FilterInterface;

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
    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
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
        $filters = isset($this->filters[$filterGroup]) ? $this->filters[$filterGroup] : [];
        $filters[] = $filter;
        $this->filters[$filterGroup] = $filters;

        return $this;
    }

    public function addAsset($filterGroup, AssetInterface $asset)
    {
        $assets = isset($this->assets[$filterGroup]) ? $this->assets[$filterGroup] : [];
        $assets[] = $filter;
        $this->assets[$filterGroup] = $assets;
        
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
    public function getAssets($filterGroup = null)
    {
        if($filterGroup === null) {
          
            return $this->assets;
        } else {
          
            return isset($this->assets[$filterGroup]) ? $this->assets[$filterGroup] : [];
        }
    }
    public function compile($filterGroup)
    {
        $assets = $this->getAssets($filterGroup);
        $filters = $this->getFilters($filterGroup);
        
        // process all the assets 
        foreach($assets as $i => $asset)
        {
            foreach($filters as $filter)
            {
                // overwrite with processed asset
                $asset = $filter->processAsset($asset);
            }
            unset($assets[$i]);
            $assets[$i] = $asset;
        }
        
        // @todo do we concat ?
        
        return $assets;
    }
    public function getAssetUrls($filterGroup)
    {
        $urls = [];
        
        $assets = $this->compile($filterGroup);
        foreach($assets as $asset)
        {
            $urls[] = $asset->getUrl();
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