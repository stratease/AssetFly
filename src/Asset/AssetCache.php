<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\Asset\AssetInterface;
class AssetCache
{
    use ConfiguratorTrait;

    protected $asset;
    protected $filters = [];
    public function __construct($cacheDir, AssetInterface $asset, array $filters, array $options = [])
    {        
        $this->loadOptions($options);
        $this->setCacheDirectory($cacheDir);
        $this->setAsset($asset);
        $this->setFilters($filters);
    }
    
    public function setCacheDirectory($dir)
    {
        $this->cacheDirectory = $dir;
        if(is_dir($this->cacheDirectory) === false) {
            if(@mkdir($this->cacheDirectory, 0755, true) === false) {
                throw new \Exception("Unable to create cache directory '".$this->cacheDirectory."', permission denied!");
            }
        }
        
        return $this;
    }
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        
        return $this;
    }
    
    public function setAsset(AssetInterface $asset)
    {
        $this->asset = $asset;
        
        return $this;
    }
    
    public function getCache()
    {
        // do we have a file?    
        $name = $this->asset->generateOutputName($this->filters)->getOutputName();
        
        if($path = realpath($this->cacheDirectory.'/'.$name)) {
            $asset = clone $this->asset;
            // @todo better mechanism to hook into content?
            $asset->setContent(
                        file_get_contents($path));
            
            return $asset;
        }
        
        return false;
    }
}