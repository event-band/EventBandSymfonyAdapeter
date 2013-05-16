<?php
/*
 * Copyright (c)
 * Kirill chEbba Chebunin <iam@chebba.org>
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 */
/** @var $loader \Composer\Autoload\ClassLoader */
$loader = include __DIR__ . '/../vendor/autoload.php';
$loader->add('EventBand\Adapter\Symfony\Tests', __DIR__, true);
