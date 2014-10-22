<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\AssetLoader;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
use Symfony\Component\Process\ProcessBuilder;
abstract class FilterBase implements FilterInterface
{
    use ConfiguratorTrait;
    protected $ifDebugCallable;
    /**
     * @var AssetLoader
     */
    protected $assetLoader;

    public function __construct(array $options = [])
    {
        $this->loadOptions($options);
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