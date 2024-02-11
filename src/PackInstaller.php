<?php

namespace Velsym\Composer;

use Composer\Installer\InstallerInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class PackInstaller extends LibraryInstaller
{
    private static string $dependenciesFilePath;

    public static function getDependenciesFilePath(): string
    {
        return self::$dependenciesFilePath;
    }

    public static function setDependenciesFilePath($dependenciesFilePath): void
    {
        self::$dependenciesFilePath = $dependenciesFilePath;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $velsym_dependencies_path = realpath($this->getInstallPath($package)."/velsym-dependencies/dependencies.php");
        $dependenciesFileArray = file(self::$dependenciesFilePath);
        $lastKey = array_key_last($dependenciesFileArray) ?? -1;
        $dependenciesFileArray[$lastKey] = "    require('$velsym_dependencies_path'),\n";
        $dependenciesFileArray[$lastKey+1] = "];";
        file_put_contents(self::$dependenciesFilePath, $dependenciesFileArray);
        return parent::install($repo, $package);
    }

    public function supports(string $packageType)
    {
        return "velsym-pack" === $packageType;
    }
}