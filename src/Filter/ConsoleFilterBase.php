<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\Filter\FilterInterface;
use stratease\AssetFly\Util\ConfiguratorTrait;
abstract class ConsoleFilterBase implements ConsoleFilterInterface
{
    use ConfiguratorTrait;
    /**
     * @var string The cli args
     */
    protected $options; 
    public function __construct($shPath = null, array $options = [])
    {
        $this->shellPath = $shPath;
        $this->loadOptions($options);
    }
    
    public function setOptions($args)
    {
        // stringify it...
        if(is_array($args)) {
            $args = implode(" ", $args);
        }
        $this->options = $args;
        
        return $this;
    }
}