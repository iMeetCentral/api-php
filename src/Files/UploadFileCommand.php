<?php

namespace CentralDesktop\API\Files;


use CentralDesktop\API\WithClient;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Get a "deferred asset" for a file.  This is the _first_ step of placing it in a workspace.')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false)
            ->addArgument("filePath", InputArgument::REQUIRED, "Path to a file");
    }


    function getResult(InputInterface $input, OutputInterface $output) {
        try {

            $client = $this->getClient();
            $path = $input->getArgument("filePath");
            $name = basename($path);
            $body = fopen($path, "r");

            $r = $client->post(self::ENDPOINT_POST, [
                'body' => $body,
                'headers' => [
                    "Content-Disposition" => "attachment; filename=$name"
                ]
            ]);

            return $r->getBody()->getContents();
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $uuid = $this->getResult($input, $output);
        $output->writeln("Deferred Asset UUID:" . $uuid);
    }

}
