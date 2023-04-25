<?php

namespace Webteractive\EE;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Storage
{
    protected $name;
    protected $cwdPath;
    protected $addonPath;
    protected $stubsPath;

    public function __construct($name, $cwd = null)
    {
        $this->name = $name;
        $this->cwdPath = $cwd ?? getcwd();
        $this->addonPath = ($cwd ?? getcwd()) . '/' . $name;
        $this->stubsPath = __DIR__ . '/../stubs/';
    }

    public function cwd()
    {
        return new Filesystem(
            new LocalFilesystemAdapter($this->cwdPath)
        );
    }

    public function addon()
    {
        return new Filesystem(
            new LocalFilesystemAdapter($this->addonPath)
        );
    }

    public function stubs()
    {
        return new Filesystem(
            new LocalFilesystemAdapter($this->stubsPath)
        );
    }

    public function getCwdPath()
    {
        return $this->cwdPath;
    }

    public function getAddonPath()
    {
        return $this->addonPath;
    }

    public function getStubsPath()
    {
        return $this->stubsPath;
    }
}
