#!/usr/bin/env php
<?php


require __DIR__.'/../vendor/autoload.php';

use CentralDesktop\API\Files\UploadFileCommand;
use CentralDesktop\API\Users\UsersCommand;
use CentralDesktop\API\Workspaces\WorkspacesCommand;
use Symfony\Component\Console\Application;
use CentralDesktop\API\Auth\AuthCommand;
use CentralDesktop\API\Times\TimesCommand;

$application = new Application();
$application->add(new AuthCommand());
$application->add(new WorkspacesCommand());
$application->add(new UsersCommand());
$application->add(new UploadFileCommand());
$application->add(new TimesCommand());

$application->run();

