<?php

require __DIR__ . "/vendor/autoload.php";

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
        function() use ($board, $loop) {
            $pin = $board->pins[13];
            $pin->mode = Firmata\Pin::MODE_OUTPUT;

            $loop->setInterval(
                function() use ($pin) {
                    $pin->digital = !$pin->digital;
                }, 1000
            );
        }
    );

$loop->run();
