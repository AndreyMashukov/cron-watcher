<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CronWatcherTest extends TestCase
{
    /**
     * @var string
     */
    private $scriptPath;

    public function setUp()
    {
        $script = "<?php\n"
            . "require __DIR__ . '/../vendor/autoload.php';\n\n"
            . "\\AM\\CronWatcher\\CronWatcher::run();\n\n"
            . "echo 'script started';\n"
            . '\\sleep(50);';

        $this->scriptPath = __DIR__ . '/test_script.php';

        \file_put_contents($this->scriptPath, $script);
    }

    public function tearDown()
    {
        \unlink($this->scriptPath);
    }

    /**
     * Should allow to run script by CLI only one time.
     *
     * @group integration
     */
    public function testShouldAllowToRunScriptByCliOnlyOneTime()
    {
        $process = new Process('php ' . $this->scriptPath);
        $process->start();

        \sleep(5);

        $this->assertEquals('script started', $process->getOutput());

        $secondProcess = new Process('php ' . $this->scriptPath);
        $secondProcess->start();

        \sleep(5);

        $this->assertNotEquals('script started', $secondProcess->getOutput());

        $process->stop();
        $secondProcess->stop();
    }

    /**
     * Should allow to run script by CLI only one time.
     *
     * @group unit
     *
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    public function testUnitShouldAllowToRunScriptByCliOnlyOneTime()
    {
        $argv    = $_SERVER['argv'];
        $name    = \md5(\implode('_', $argv));
        $tmpPath = \sys_get_temp_dir();

        $path = $tmpPath . '/' . $name;

        if (true === \file_exists($path)) {
            \unlink($path);
        }

        $this->assertFileNotExists($path);

        \AM\CronWatcher\CronWatcher::run();

        $this->assertFileExists($path);

        $pid = \file_get_contents($path);
        $this->assertRegExp('/\d+/ui', $pid);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Script already ran!');
        \AM\CronWatcher\CronWatcher::run();
    }
}
