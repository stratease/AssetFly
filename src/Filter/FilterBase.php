<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\AssetLoader;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
use Symfony\Component\Process\ProcessBuilder;
abstract class FilterBase implements FilterInterface
{
    use ConfiguratorTrait;

    /**
     * @var AssetLoader
     */
    protected $assetLoader;

    public function __construct(AssetLoader $assetLoader, array $options = [])
    {
        $this->setAssetLoader($assetLoader);
        $this->loadOptions($options);
    }

    /**
     * @param AssetLoader $value The loader
     * @return $this
     */
    public function setAssetLoader($value)
    {
        $this->assetLoader = $value;

        return $this;
    }

    /**
     * @return AssetLoader The loader
     */
    public function getAssetLoader()
    {
        return $this->assetLoader;
    }

    /**
     * @param callable $value A callback to be run during processing, if the debug flag is turned on
     * @return $this
     */
    public function setIfDebugCallable($value)
    {
        $this->ifDebugCallable = $value;

        return $this;
    }

    /**
     * @return callable A callback to be run during processing, if the debug flag is turned on
     */
    public function getIfDebugCallable()
    {
        return $this->ifDebugCallable;
    }
}