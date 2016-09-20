<?php
/**
 * Created by IntelliJ IDEA.
 * User: kzhu
 * Date: 5/2/16
 * Time: 12:07 PM
 */
namespace CentralDesktop\API;

use GuzzleHttp\RequestOptions;
use PhpCollection\Sequence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract
class PaginationCommand extends Command {
    use WithClient;

    protected
    function configure() {
        $this
            ->addOption("debug", "d", InputOption::VALUE_REQUIRED, false)
            ->addOption("pages", "p", InputOption::VALUE_OPTIONAL, 'number of pages to retrieve', 10)
            ->addOption("size", "s", InputOption::VALUE_OPTIONAL, 'page size', 3)
            ->addOption("filter", "f", InputOption::VALUE_OPTIONAL, 'page size', '');
    }

    protected
    abstract
    function get_endpoint();

    public
    function getResult(InputInterface $input, OutputInterface $output) {
        try {
            $items = new Sequence();

            $page_num     = 1;
            $client       = $this->getClient();
            $page_size    = $input->getOption('size');
            $page_num_max = $input->getOption('pages');
            $filter       = $input->getOption('filter');

            if ($page_size > 25) {
                $output->writeln("$page_size exceeds page size limit 25, setting it to 25 ...");
                $page_size = 25;
            }

            $url = $this->get_endpoint();
            $r   = $client->get($url, [
                'query'               => [
                    'limit'  => $page_size,
                    'filter' => $filter
                ],
                RequestOptions::DEBUG =>
                    boolval($input->getOption('debug')) ? fopen('php://stdout',
                                                                'w') : false
            ]);

            $result = json_decode($r->getBody()->getContents());
            $items->addAll($result->items);


            while (isset($result->links) && !empty($result->links->next) && $page_num < $page_num_max) {
                $url = $result->links->next;
                $r   = $client->get($url);
                $page_num++;
                $result = json_decode($r->getBody()->getContents());
                $items->addAll($result->items);
            }

            return $items->all();
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
            $body     = $response->getBody()->getContents();
            $output->writeln("Failed to grab token: " . $body);
            throw $e;
        }

    }


    protected
    function execute(InputInterface $input, OutputInterface $output) {
        $items = $this->getResult($input, $output);

        $output->writeln(json_encode($items, JSON_PRETTY_PRINT));
    }


}