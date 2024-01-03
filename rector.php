<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;


return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // $rectorConfig->sets([
    //     PHPUnitSetList::PHPUNIT_100,
    //     LevelSetList::UP_TO_PHP_82
    // ]);

    $rectorConfig
        ->ruleWithConfiguration(
            RenameNamespaceRector::class,
            [
                'Norgul' => 'Cjtaylor',
                // 'OldNamespace' => 'NewNamespace',
                // 'OldNamespaceWith\OldSplitNamespace' => 'NewNamespaceWith\NewSplitNamespace',
                // 'Old\Long\AnyNamespace' => 'Short\AnyNamespace',
                // 'PHPUnit_Framework_' => 'PHPUnit\Framework',
                // 'Foo\Bar' => 'Foo\Tmp',
                // 'App\Repositories' => 'App\Repositories\Example',
            ]
        );

    //
    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_71
    //    ]);
};
