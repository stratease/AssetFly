<?php
namespace stratease\AssetFly\Filter;
use stratease\AssetFly\Asset\AssetInterface;
use stratease\AssetFly\Filter\ConsoleFilterBase;
use Symfony\Component\Process\ProcessBuilder;


class Sass extends ConsoleFilterBase
{
    /**
     * @param ProcessBuilder $value The symfony process builder that manages this filer.
     * @return $this
     */
    public function setProcessBuilder($value)
    {
        $this->processBuilder = $value;

        return $this;
    }

    /**
     * @return ProcessBuilder The symfony process builder that manages this filer.
     */
    public function getProcessBuilder()
    {
        // default to options passed if we weren't explicitly defined..
        if(!$this->processBuilder) {
            $this->setProcessBuilder(new ProcessBuilder($this->getOptions()));
        }
        return $this->processBuilder;
    }

    /**
     * @param AssetInterface $asset
     * @return AssetInterface The updated asset object
     */
    public function processAsset(AssetInterface $asset)
    {

        $pb = $this->getProcessBuilder();



        $pb->setTimeout($this->getTimeout());

        if ($dir = dirname($asset->getSourcePath())) {
            $pb->add('--load-path')->add($dir);
        }


        if (pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION) === 'scss') {
            $pb->add('--scss');
        }

        // file input
        $pb->add($asset->getSourcePath());

        $proc = $pb->getProcess();
        $code = $proc->run();
        var_dump($code, $proc->getOutput());

        if (0 !== $code) {
            throw new \Exception($asset->getSource()." failed. ".$proc->getOutput());
        }

        $asset->setContent($proc->getOutput());

        return $asset;
    }




}