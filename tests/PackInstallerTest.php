<?php

use Composer\Composer;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use Velsym\Composer\PackInstaller;
use Velsym\Composer\Tests\TestCase;

#[CoversClass(PackInstaller::class)]
class PackInstallerTest extends TestCase
{
    readonly private string $dependencies;

    readonly private string $startingFile;

    private PackInstaller $installer;

    private function resetDependencies(): void
    {
        $this->startingFile = <<<FILE
        <?php
        return [
        ];
        FILE;

        file_put_contents($this->dependencies, $this->startingFile);
    }

    protected function setUp(): void
    {
        $this->dependencies = (require(__DIR__."/../config.php"))['install-dependencies'];
        $this->installer = new PackInstaller(
            $this->getMockIO(),
            $this->getComposer()
        );
    }

    /**
     * Tests if Installer differentiates
     * between valid and invalid package.
     */
    #[Test]
    #[TestDox("Package type support")]
    public function supportsPackage(): void
    {
        /** @var MockObject|PackageInterface $package */
        $package = $this->createMock(PackageInterface::class);
        $package->method('getType')
            ->willReturn("velsym-pack");
        $this->assertFalse($this->installer->supports("non-velsym-pack"));
        $this->assertTrue($this->installer->supports($package->getType()));
    }

    /**
     * Tests if Installer puts "require"
     * directive into the dependencies list file.
     */
    #[Test]
    #[TestDox("Package installation")]
    public function packageInstallation(): void
    {
        $this->resetDependencies();
        $package = new Package("foobar", "1.0.0.0", "1.0.0");
        $package->setType("velsym-pack");
        $repository = $this->createMock(InstalledRepositoryInterface::class);
        $this->installer->install($repository, $package);
        $this->assertTrue(in_array($this->getPackageRequire($package), $this->openDependencies()));
    }

    /**
     * Tests if Installer removes "require"
     * directive when package is removed from project.
     */
    #[Test]
    #[TestDox("Package uninstallation")]
    public function packageRemoval(): void
    {
        $this->resetDependencies();
        $package = new Package("foobar", "1.0.0.0", "1.0.0");
        $package->setType("velsym-pack");
        /** @var MockObject $repository */
        $repository = $this->createMock(InstalledRepositoryInterface::class);
        $repository->method('hasPackage')->with($package)->willReturn(true);
        $repository->method('removePackage')->with($package);
        $this->insertRequire($package);

        $this->installer->uninstall($repository, $package);

        $this->assertStringEqualsFile($this->dependencies, $this->startingFile);
    }

    private function openDependencies(): array
    {
        return file($this->dependencies);
    }

    private function getPackageRequire(PackageInterface $package): string
    {
        $dependencyPath = $this->installer->getInstallPath($package) . "/velsym-dependencies/dependencies.php";
        return "    require('$dependencyPath'),\n";
    }

    private function insertRequire(PackageInterface $package): void
    {
        $path = $this->installer->getInstallPath($package)."/velsym-dependencies/dependencies.php";
        $dependenciesFileArray = file($this->dependencies);
        $lastKey = array_key_last($dependenciesFileArray) ?? -1;
        $dependenciesFileArray[$lastKey] = "    require('$path'),\n";
        $dependenciesFileArray[$lastKey+1] = "];";
        file_put_contents($this->dependencies, $dependenciesFileArray);
    }
}