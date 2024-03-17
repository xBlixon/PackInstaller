<?php

namespace Velsym\Composer\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getComposer(): Composer
    {
        $composer = new Composer();
        $composer->setPackage(new RootPackage('root/pkg', '1.0.0.0', '1.0.0'));

        $composer->setConfig(new Config(false));

        $dm = $this->getMockBuilder(DownloadManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composer->setDownloadManager($dm);

        $im = $this->getMockBuilder(InstallationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composer->setInstallationManager($im);

        $rm = $this->getMockBuilder(RepositoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composer->setRepositoryManager($rm);

        return $composer;
    }

    protected function getMockIO(): IOInterface
    {
        return $this->getMockBuilder(IOInterface::class)->getMock();
    }
}