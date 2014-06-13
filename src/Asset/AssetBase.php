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
     * @param string $sourceFile Absolute path to source file
     * @param array $options
     */
    public function __construct($sourceFile, array $options = [])
    {        
        $this->loadOptions($options);
        $this->setSourceFile($sourceFile);
    }
    
    /**
     * Are we a precompiler like Sass, Less, Coffeescript etc.. ?
     * @return bool
     */
    public static function isPrecompiler()
    {
        return false;
    }

    /**
     * @param $sourceFile
     * @return $this
     * @throws \Exception
     */
    public function setSourcePath($sourceFile)
    {
        $this->sourcePath = $sourceFile;
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
}