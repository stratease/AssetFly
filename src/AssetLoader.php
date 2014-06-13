<?php
namespace stratease\AssetFly;
use stratease\AssetFly\Util\ConfiguratorTrait;

class AssetLoader
{
    use ConfiguratorTrait;
    public function __construct(array $options = array())
    {
        $this->loadOptions($options);
    }
}