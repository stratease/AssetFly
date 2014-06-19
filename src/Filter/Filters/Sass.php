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
            throw new \Exception(__METHOD__." - ".$proc->getErrorOutput().". Failed to filter '".$asset->getSourcePath()."\n".$proc->getCommandLine(), E_USER_WARNING);
        }

        return $asset->iterateNewAsset($proc->getOutput());
    }

    public function addDebugFlags()
    {
        $this->processBuilder->add('--debug-info');
        $this->processBuilder->add('--line-numbers');
    }



}