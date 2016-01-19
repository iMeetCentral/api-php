<?php

namespace CentralDesktop\API\Files;


use CentralDesktop\API\WithClient;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadFileCommand extends Command  {
    use WithClient;

    const ENDPOINT_POST = '/v1/files/upload';

    protected
    function configure() {
        $this
            ->setName('files:upload')
            ->setDescription('Upload a file')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        try {

            $client = $this->getClient();
            $body = fopen(__DIR__ . "/../../demo_files/mars_1.jpg", "r");

            $r = $client->post(self::ENDPOINT_POST, [
                'body' => $body,
                'headers' => [
                    "Content-Disposition" => "attachment; filename=mars_1.jpg"
                ]
            ]);

            $output->writeln("Deferred Asset UUID:" . $r->getBody()->getContents());
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }

}
