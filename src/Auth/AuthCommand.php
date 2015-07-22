<?php

namespace CentralDesktop\API\Auth;

use CentralDesktop\API\ClientFactory;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 7/21/15
 * Time: 5:24 PM
 */
class AuthCommand extends Command {
    protected
    function configure() {
        $this
            ->setName('auth:token')
            ->setDescription('Generate a bearer token')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        try {
            ClientFactory::$debug = $input->getOption('debug');

            $token = ClientFactory::getAuthToken();
            $output->writeln("Your access token is $token");
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }
}