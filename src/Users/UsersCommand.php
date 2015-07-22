<?php

namespace CentralDesktop\API\Users;


use CentralDesktop\API\WithClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UsersCommand extends Command  {
    use WithClient;

    const ENDPOINT_LIST = '/v1/users';

    protected
    function configure() {
        $this
            ->setName('users:list')
            ->setDescription('List users')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        try {

            $client = $this->getClient();
            $r = $client->get(self::ENDPOINT_LIST);

            $result = json_decode($r->getBody()->getContents());

            $output->writeln(print_r($result->items,true));
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }

}