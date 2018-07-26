<?php

use PHPUnit\Framework\TestCase;

error_reporting(E_ALL);
if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}
require_once __DIR__ . '/../vendor/autoload.php';
// PHPUnit 6 introduced a breaking change that
// removed PHPUnit_Framework_TestCase as a base class,
// and replaced it with \PHPUnit\Framework\TestCase
if (!class_exists(\PHPUnit_Framework_TestCase::class) && class_exists(TestCase::class)) {
    class_alias(TestCase::class, \PHPUnit_Framework_TestCase::class);
}