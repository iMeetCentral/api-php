<?php

namespace CentralDesktop\API\Workspaces;


use CentralDesktop\API\WithClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkspacesCommand extends Command  {
    use WithClient;

    const ENDPOINT_LIST = '/v1/workspaces';

    protected
    function configure() {
        $this
            ->setName('workspaces:list')
            ->setDescription('List workspaces')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        try {

            $client = $this->getClient();

            $r = $client->get(self::ENDPOINT_LIST);

            $output->writeln($r->getBody()->getContents());
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }

}