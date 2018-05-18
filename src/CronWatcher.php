<?php

namespace AM\CronWatcher;

use Symfony\Component\Process\Exception\LogicException;

class CronWatcher
{
    /**
     * Check process signal and kill current process if script already run.
     *
     * @see {http://man7.org/linux/man-pages/man7/signal.7.html}
     */
    public static function run()
    {
        $argv    = $_SERVER['argv'];
        $tmpPath = \sys_get_temp_dir();

        $name = \md5(\implode('_', $argv));
        $path = $tmpPath . '/' . $name;

        if (true === \file_exists($path)) {
            $pid = \file_get_contents($path);

            if (\posix_kill($pid, 0)) {
                throw new LogicException('Script already ran!');
            } //end if
        } //end if

        $pid = \posix_getpid();
        \file_put_contents($path, $pid);
    }
}
