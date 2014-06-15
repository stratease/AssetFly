<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
abstract class AssetBase implements AssetInterface
{
    use ConfiguratorTrait;

    /**
     * @var string Absolute path to source file
     */
    protected $sourcePath;
    /**
     * @var string The filter group this asset is bound to
     */
    protected $filterGroup;
    /**
     * @param string $sourceFile Absolute path to source file
     * @param array $options
     */
    public function __construct($sourceFile, array $options = [])
    {        
        $this->loadOptions($options);
        $this->setSourcePath($sourceFile);
    }
    
    /**
     * Are we a precompiler like Sass, Less, Coffeescript etc.. ?
     * @return bool
     */
    public static function isPrecompiler()
    {
        return false;
    }

    public function setFilterGroup($group)
    {
        $this->filterGroup = $group;

        return $this;
    }

    public function getFilterGroup()
    {
        return $this->filterGroup;
    }

    public function generateOutputName(array $filters)
    {
        $fileName = basename($this->getSourcePath());
        return sha1(json_encode($filters).filemtime($this->getSourcePath())).'_'.$fileName;
    }

    /**
     * @param $sourceFile
     * @return $this
     * @throws \Exception
     */
    public function setSourcePath($sourceFile)
    {
        $this->sourcePath = realpath($sourceFile);
        // check for our file
        if(is_file($this->sourcePath) === false) {
            throw new \Exception("Unable to locate assets source file '".$sourceFile."'.");
        }
        
        return $this;
    }
    
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    public function getTempName()
    {
        return sha1($this->getSourcePath()).'_'.basename($this->getSourcePath());
    }

}