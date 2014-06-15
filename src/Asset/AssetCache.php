<?php
namespace stratease\AssetFly\Asset;
use stratease\AssetFly\Util\ConfiguratorTrait;
use stratease\AssetFly\Asset\AssetInterface;
abstract class AssetCache
{
    use ConfiguratorTrait;

    
    public function __construct(AssetInterface $asset, array $filters, array $options = [])
    {        
        $this->loadOptions($options);
        $this->setAsset($asset);
        $this->setFilters($filters);
    }
    
}