<?php

namespace Velsym\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PackInstallerPlugin implements PluginInterface
{

    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new PackInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}