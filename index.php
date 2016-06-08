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

while(true) {
    sleep(1);

    $watcher->findChanges();
    $changes = $watcher->getUpdatedResources();

    if (count($changes) > 0) {
        $first = $changes[0];

        $lines = file($first);

        for ($i = count($lines) - 1; $i > -1; $i--) {
            if (stristr($lines[$i], "CHAT")) {
                if (stristr($lines[$i], "closed")) {
                    print "closed!";
                }

                if (stristr($lines[$i], "open")) {
                    print "open!";
                }

                break;
            }
        }
    }
}
