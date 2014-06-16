<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\AssetLoader;
abstract class AssetBase implements AssetInterface
{
    use ConfiguratorTrait;
    const F_CSS = 'css';
    const F_JS = 'js';
    const F_LESS = 'less';
    const F_SASS = 'sass';
    const F_SCSS = 'scss';

    /**
     * @var string Separates the hash id from the file name for generated names
     */
    protected $hashNameSeparator = '_';
    /**
     * @var string Absolute path to source file
     */
    protected $sourcePath;
    /**
     * @var string The filter group this asset is bound to
     */
    protected $filterGroup;
    /**
     * @var string
     */
    protected $fileType;
    /**
     * @var string
     */
    protected $dumpDirectory;
    /**
     * @var string
     */
    protected $outputName;
    /**
     * @param AssetLoader $assetLoader The loader
     * @param string $sourceFile Absolute path to source file
     * @param array $options
     */
    public function __construct(AssetLoader $assetLoader, $sourceFile, array $options = [])
    {
        $this->setAssetLoader($assetLoader);
        $this->setSourcePath($sourceFile);
        $this->loadOptions($options);
    }

    /**
     * @param AssetLoader $value
     * @return $this
     */
    public function setAssetLoader($value)
    {
        $this->assetLoader = $value;

        return $this;
    }

    /**
     * @return
     */
    public function getAssetLoader()
    {
        return $this->assetLoader;
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
        // create file name
        $fileName = pathinfo($this->getSourcePath(), PATHINFO_FILENAME);
        // extension...
        switch($this->getFileType())
        {
            case self::F_SCSS:
            case self::F_LESS:
            case self::F_SASS:
                $ext = 'css';
                break;
            default:
                $ext = pathinfo($this->getSourcePath(), PATHINFO_EXTENSION);
                break;
        }
        $fileName = $fileName.'.'.$ext;

        // @todo This isn't the best/optimized way to uniquely ID filters changes. Create a filter ID which changes based on param uses
        $hash = sha1(json_encode($filters).
            filemtime($this->getSourcePath()).
            $this->getSourcePath());
        $path = $this->cleanOutputName($hash.$this->hashNameSeparator.$fileName);

        $this->setOutputName($path);

        return $this;
    }

    /**
     * @param string $value The file name to be output
     * @return $this
     */
    public function setOutputName($value)
    {
        $this->outputName = $value;

        return $this;
    }

    /**
     * @return string The file name to be output
     */
    public function getOutputName()
    {
        return $this->outputName;
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

    /**
     * @param mixed $value Constant definition for the type of file being processed
     * @return $this
     */
    public function setFileType($value)
    {
        $this->fileType = $value;

        return $this;
    }

    /**
     * @return mixed Constant definition for the type of file being processed
     */
    public function getFileType()
    {
        if(!$this->fileType) {

            // try and guess
            return pathinfo($this->getSourcePath(), PATHINFO_EXTENSION);
        } else {

            return $this->fileType;
        }
    }

    /**
     * @param string $value Web path this file is to be output
     * @return $this
     */
    public function setDumpDirectory($value)
    {
        $this->dumpDirectory = $value;

        return $this;
    }

    /**
     * @return string Web path this file is to be output
     */
    public function getDumpDirectory()
    {
        return $this->dumpDirectory;
    }

    /**
     * @return mixed Asset path relative to the web root
     */
    public function getOutputPath()
    {
        return str_replace("//", "/", $this->getDumpDirectory().'/'.$this->getOutputName());
    }

    /**
     * Saves current content to path specified
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function save($path)
    {
        if(!file_put_contents($path, $this->getContent())) {
            throw new \Exception("Unable to save '".$path."'", E_USER_WARNING);
        }
        $this->setSourcePath($path);

        return $this;
    }


    /**
     * @param $path
     * @return mixed
     */
    public function cleanOutputName($path)
    {
        $pathSplit = explode("_", basename($path));

        if(count($pathSplit) > 2)
        {
            $a = array_pop($pathSplit);
            $b = array_pop($pathSplit);
            $path = dirname($path).'/'.$b.$this->hashNameSeparator.$a;
        }

        return str_replace("//", "/", $path);
    }
    /**
     * Helper method to generate a new asset cloned from this one, with new content and source paths. Useful to create a chain of assets across change steps
     * @param $content mixed
     * @return AssetBase
     */
    public function iterateNewAsset($content)
    {
        // new asset
        $compiledAsset = clone $this;

        // build new output name
        return $compiledAsset->generateOutputName([$this])
        // update our asset content
            ->setContent($content)
        // output
            ->dumpToOutput();
    }

    public function dumpToOutput()
    {
        $this->save($this->assetLoader->getWebDirectory().'/'.$this->getOutputPath());

        return $this;
    }
}