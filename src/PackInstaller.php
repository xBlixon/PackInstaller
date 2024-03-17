<?php

namespace Velsym\Composer;

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

    public static function setDependenciesFilePath(string $dependenciesFilePath): void
    {
        self::$dependenciesFilePath = $dependenciesFilePath;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->insertRequire($package);
        return parent::install($repo, $package);
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
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
        $path = $this->getInstallPath($package)."/velsym-dependencies/dependencies.php";
        $dependenciesFileArray = file(self::$dependenciesFilePath);
        $lastKey = array_key_last($dependenciesFileArray) ?? -1;
        $dependenciesFileArray[$lastKey] = "    require('$path'),\n";
        $dependenciesFileArray[$lastKey+1] = "];";
        file_put_contents(self::$dependenciesFilePath, $dependenciesFileArray);
    }

    private function removeRequire(PackageInterface $package): void
    {
        $packagePathPartial = $package->getName()."/velsym-dependencies/dependencies.php";
        $dependenciesFileArray = file(self::$dependenciesFilePath);
        $key = $this->substringFirstOccurrenceKey($dependenciesFileArray, $packagePathPartial);
        unset($dependenciesFileArray[$key]);
        file_put_contents(self::$dependenciesFilePath, $dependenciesFileArray);
    }
}