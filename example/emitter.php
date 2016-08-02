#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Amp\Coroutine;
use Amp\Emitter;
use Amp\Observable;
use Amp\Observer;
use Amp\Pause;
use Amp\Loop\NativeLoop;

Amp\execute(function () {
    try {
        $emitter = new Emitter(function (callable $emit) {
            yield $emit(1);
            yield $emit(new Pause(500, 2));
            yield $emit(3);
            yield $emit(new Pause(300, 4));
            yield $emit(5);
            yield $emit(6);
            yield $emit(new Pause(1000, 7));
            yield $emit(8);
            yield $emit(9);
            yield $emit(new Pause(600, 10));
            yield Coroutine::result(11);
        });

        $generator = function (Observable $observable) {
            $observer = new Observer($observable);

            while (yield $observer->next()) {
                printf("Observable emitted %d\n", $observer->getCurrent());
                yield new Pause(100); // Observer consumption takes 100 ms.
            }

            printf("Observable result %d\n", $observer->getResult());
        };

        yield new Coroutine($generator($emitter));

    } catch (\Exception $exception) {
        printf("Exception: %s\n", $exception);
    }
}, $loop = new NativeLoop());
