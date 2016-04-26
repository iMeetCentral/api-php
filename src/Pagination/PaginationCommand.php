<?php

namespace CentralDesktop\API\Pagination;


use CentralDesktop\API\WithClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PaginationCommand extends Command  {
    use WithClient;

    const ENDPOINT_LIST = '/v1/times';

    protected
    function configure() {
        $this
            ->setName('times:Paging')
            ->setDescription('List time entries by page')
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false)
            ->addOption("pages", "p", InputOption::VALUE_OPTIONAL, 'number of pages to retrieve', 10)
            ->addOption("size", "s", InputOption::VALUE_OPTIONAL, 'page size', 1);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        try {
            $page_num = 1;
            $client = $this->getClient();
            $page_size = $input->getOption('size');
            $page_num_max = $input->getOption('pages');

            if ($page_size > 25) {
                $output->writeln("$page_size exceeds page size limit 25, setting it to 25 ...");
                $page_size = 25;
            }

            $url = self::ENDPOINT_LIST."?limit=$page_size";

            do {
                $output->writeln("Loading page $page_num of results");
                $r = $client->get($url);
                $result = json_decode($r->getBody()->getContents());
                $url = isset($result->links) && !empty($result->links->next) ? $result->links->next : $url;

                $output->writeln(print_r($result,true));
                $output->writeln("----------------");

                $page_num++;
            } while (isset($result->links) && !empty($result->links->next) && $page_num < $page_num_max);
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }

}
