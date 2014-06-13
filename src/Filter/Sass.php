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

        if ($dir = $asset->getSourceDirectory()) {
            $pb->add('--load-path')->add($dir);
        }

        if ($this->unixNewlines) {
            $pb->add('--unix-newlines');
        }

        if (pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION) === 'scss') {
            $pb->add('--scss');
        }

        if ($this->style) {
            $pb->add('--style')->add($this->style);
        }

        if ($this->quiet) {
            $pb->add('--quiet');
        }

        if ($this->debugInfo) {
            $pb->add('--debug-info');
        }

        if ($this->lineNumbers) {
            $pb->add('--line-numbers');
        }

        foreach ($this->loadPaths as $loadPath) {
            $pb->add('--load-path')->add($loadPath);
        }

        if ($this->cacheLocation) {
            $pb->add('--cache-location')->add($this->cacheLocation);
        }

        if ($this->noCache) {
            $pb->add('--no-cache');
        }

        if ($this->compass) {
            $pb->add('--compass');
        }

// input
        $pb->add($input = tempnam(sys_get_temp_dir(), 'assetic_sass'));
        file_put_contents($input, $asset->getContent());

        $proc = $pb->getProcess();
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            throw new \Exception($asset->getSource()." failed. ".$proc->getOutput());
        }

        $asset->setContent($proc->getOutput());

        return $asset;
    }




}