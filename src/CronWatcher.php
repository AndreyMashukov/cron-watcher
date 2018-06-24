<?php

namespace AM\CronWatcher;

use Symfony\Component\Process\Exception\LogicException;

class CronWatcher
{
    /**
     * Check process signal and kill current process if script already run.
     *
     * @param null|string $name
     *
     * @see {http://man7.org/linux/man-pages/man7/signal.7.html}
     *
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    public function run(?string $name = null)
    {
        $tmpPath = \sys_get_temp_dir();

        if (null === $name) {
            $argv = $_SERVER['argv'];
            $name = \md5(\implode('_', $argv));
        }

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
