<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 9/23/16
 * Time: 2:46 PM
 */

namespace CentralDesktop\API\Reports;


use CentralDesktop\API\WithClient;
use GuzzleHttp\Exception\ClientException;
use League\Csv\Writer;
use PhpCollection\Sequence;
use SplTempFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command {
    use WithClient;


    protected
    function configure() {
        parent::configure();
        $this
            ->setName('report:export')
            ->addArgument('reportId', InputArgument::REQUIRED, 'ID of the report')
            ->addArgument('outputPath', InputArgument::REQUIRED, 'Path to write the file')
            ->setDescription('Exports a report to CSV');
    }


    function getResult(InputInterface $input, OutputInterface $output) {
        try {

            $reportid = $input->getArgument('reportId');
            $client   = $this->getClient();

            $r = $client->get("/v1/reports/{$reportid}/records");

            return $r->getBody()->getContents();
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
        $outfile = $input->getArgument('outputPath');
        $result = $this->getResult($input, $output);

        $json    = \GuzzleHttp\json_decode($result);
        $items   = new Sequence($json->items);

        $out = new \SplFileObject($outfile,'w+');
        $csv = Writer::createFromFileObject($out);
        $csv->setDelimiter(',');
        $items->first()->map(function ($f) use ($csv) {
            $row        = new Sequence($f->fields);
            $fieldNames = $row->map(function ($r) {
                return preg_replace("/\./", '__', $r->fieldName);
            });
            $csv->insertOne($fieldNames->all());
        });

        $items->drop(1)->map(function ($row) use ($csv) {
            $fields = new Sequence($row->fields);
            $values = $fields->map(function ($f) {
                return $f->value;
            });
            $csv->insertOne($values->all());
        });
    }

}