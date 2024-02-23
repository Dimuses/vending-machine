#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use app\commands\BuyProductCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$application = new Application();

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load(__DIR__ . '/app/config/config.yaml');
$container->compile();

$application->add($container->get(BuyProductCommand::class));

$application->run();