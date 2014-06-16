<?php
namespace stratease\AssetFly\Filter\Filters;
use stratease\AssetFly\Asset\AssetBase;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Filter\ConsoleFilterBase;



class Sass extends ConsoleFilterBase
{
    protected $shellCmd = 'sass';




    public static function isPrecompiler()
    {
        return true;
    }

    /**
     * @param AssetInterface $asset
     * @return AssetInterface
     * @throws \Exception
     */
    public function processAsset(AssetInterface $asset)
    {
        $pb = $this->getProcessBuilder();
        if($asset->getFileType() === AssetBase::F_SCSS) {
            $pb->add('--scss');
        }

        // file input
        $pb->add($asset->getSourcePath());
        $proc = $pb->getProcess();
        $code = $proc->run();

        // process err?
        if ($code !== 0) {
            throw new \Exception(__METHOD__." failed to filter '".$asset->getSourcePath(). "' - ".substr($proc->getOutput(), 0, 100), E_USER_WARNING);
        }

        return $asset->iterateNewAsset($proc->getOutput());
    }




}