<?php
/**
 * Created by IntelliJ IDEA.
 * User: kzhu
 * Date: 5/2/16
 * Time: 12:07 PM
 */
namespace CentralDesktop\API;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PaginationCommand extends Command {
    use WithClient;

    protected
    function configure() {
        $this
            ->addOption("debug","d", InputOption::VALUE_REQUIRED, false)
            ->addOption("pages", "p", InputOption::VALUE_OPTIONAL, 'number of pages to retrieve', 10)
            ->addOption("size", "s", InputOption::VALUE_OPTIONAL, 'page size', 3);
    }

    protected
    abstract function get_endpoint();

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

            $url = $this->get_endpoint();
            $r = $client->get($url, ['query' => ['limit' => $page_size]]);
            $result = json_decode($r->getBody()->getContents());
            $this->printResult($page_num, $result, $output);

            while (isset($result->links) && !empty($result->links->next) && $page_num < $page_num_max) {
                $url = $result->links->next;
                $r = $client->get($url);
                $page_num++;
                $result = json_decode($r->getBody()->getContents());
                $this->printResult($page_num, $result, $output);
            }
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }
    }

    private
    function printResult($page_num, $result, OutputInterface $output) {
        $output->writeln("Loading page $page_num of results");
        $output->writeln(print_r($result,true));
        $output->writeln("----------------");
    }
}