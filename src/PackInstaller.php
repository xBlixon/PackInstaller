<?php

namespace Velsym\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class PackInstaller extends LibraryInstaller
{
    private string $dependenciesFilePath;

    private function setDependenciesFilePath(): void
    {
        $dir = $this->findDirWithVendor();
        $config = "$dir/config.php";
        $this->dependenciesFilePath = (require($config))['dependency-list'];
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->setDependenciesFilePath();
        $this->insertRequire($package);
        return parent::install($repo, $package);
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->setDependenciesFilePath();
        $this->removeRequire($package);
        return parent::uninstall($repo, $package);
    }

    private function substringFirstOccurrenceKey(array $array, string $substring): int
    {
        foreach ($array as $key => $value) {
            if (stripos($value, $substring) !== false) {
                return $key;
            }
        }
        return -1;
    }

    public function supports(string $packageType): bool
    {
        return "velsym-pack" === $packageType;
    }

    private function insertRequire(PackageInterface $package): void
    {
        $path = $this->getInstallPath($package) . "/velsym-dependencies/dependencies.php";
        $dependenciesFileArray = file($this->dependenciesFilePath);
        $lastKey = array_key_last($dependenciesFileArray) ?? -1;
        $dependenciesFileArray[$lastKey] = "    require('$path'),\n";
        $dependenciesFileArray[$lastKey + 1] = "];";
        file_put_contents($this->dependenciesFilePath, $dependenciesFileArray);
    }

    private function removeRequire(PackageInterface $package): void
    {
        $packagePathPartial = $package->getName() . "/velsym-dependencies/dependencies.php";
        $dependenciesFileArray = file($this->dependenciesFilePath);
        $key = $this->substringFirstOccurrenceKey($dependenciesFileArray, $packagePathPartial);
        unset($dependenciesFileArray[$key]);
        file_put_contents($this->dependenciesFilePath, $dependenciesFileArray);
    }

    private function findDirWithVendor($dir = __DIR__): string
    {
        while (!file_exists($dir . "/vendor")) {
            $dir = realpath("$dir/../");
        }
        return $dir;
    }

}