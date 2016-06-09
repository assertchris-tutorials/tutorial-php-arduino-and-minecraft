<?php

require __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Finder\Finder;

$path = "/Users/Christopher/Library/Application Support/minecraft/logs";

$finder = new Finder();

$finder->files()
    ->name("*.log")
    ->depth(0)
    ->in($path);

use Yosymfony\ResourceWatcher\ResourceCacheFile;
use Yosymfony\ResourceWatcher\ResourceWatcher;

$cache = new ResourceCacheFile(__DIR__ . "/cache.php");

$watcher = new ResourceWatcher($cache);
$watcher->setFinder($finder);

use Carica\Io;
use Carica\Firmata;

$board = new Firmata\Board(
    Io\Stream\Serial\Factory::create(
        "/dev/tty.usbmodem1421", 57600
    )
);

$loop = Io\Event\Loop\Factory::get();

$board
    ->activate()
    ->done(
        function() use ($board, $loop, $watcher) {
            $pin = $board->pins[9];
            $pin->mode = Firmata\Pin::MODE_PWM;

            $loop->setInterval(
                function() use ($pin, $watcher) {
                    $watcher->findChanges();
                    $changes = $watcher->getUpdatedResources();

                    if (count($changes) > 0) {
                        $first = $changes[0];

                        $lines = file($first);

                        for ($i = count($lines) - 1; $i > -1; $i--) {
                            if (stristr($lines[$i], "CHAT")) {
                                if (stristr($lines[$i], "closed")) {
                                    $pin->analog = 0;
                                }

                                if (stristr($lines[$i], "open")) {
                                    $pin->analog = 1;
                                }

                                break;
                            }
                        }
                    }
                }, 1000
            );
        }
    );

$loop->run();
