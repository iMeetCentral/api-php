<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 9/7/16
 * Time: 3:59 PM
 */

namespace CentralDesktop\API\Projects;

use CentralDesktop\API\ClientFactory;
use CentralDesktop\API\Files\DeferredAsset;
use CentralDesktop\API\Files\UploadFileCommand;
use CentralDesktop\API\Milestones\Milestone;
use CentralDesktop\API\WithClient;
use CentralDesktop\API\WorkspaceGroups\WorkspaceGroupListCommand;
use CentralDesktop\API\Workspaces\Create;
use PhpCollection\Sequence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectsCommand extends Command {
    use WithClient;


    protected
    function configure() {
        $this
            ->setName('projects:create')
            ->setDescription('Create a project from a brief')
            ->addArgument("shortName", InputArgument::REQUIRED, "The short name that will show up in the URL")
            ->addArgument("name", InputArgument::REQUIRED, "Project name")
            ->addArgument("filePath", InputArgument::OPTIONAL, "Path to a file")
            ->addOption("debug", 'd', InputOption::VALUE_OPTIONAL, "Show debugging", false);
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        $debug  = $input->getOption('debug');
        $client = ClientFactory::getClient();

        $output->write("Looking up workspace group... ");
        $wsGL       = new WorkspaceGroupListCommand();
        $groups     = new Sequence($wsGL->getResult(new ArrayInput([], $wsGL->getDefinition()), $output));
        $notGrouped = $groups->filter(function ($group) {
            return $group->details->workspaceGroupName == 'Not Grouped';
        })->first();

//        error_log(print_r($notGrouped));
        $notGroupedHash = $notGrouped->get()->id;

        $output->writeln("Found Workspace Group ID $notGroupedHash");

        $create                = new Create();
        $create->workspaceName = $input->getArgument('name');
        $create->urlShortName  = $input->getArgument('shortName');

        $output->write("Creating workspace {$create->workspaceName}... ");
//        error_log(print_r($create, true));
//        error_log(json_encode($create));
        $createSpace = $client->post("/v1/workspaceGroups/$notGroupedHash/workspaces", [
            "json"  => $create,
            "debug" => $debug
        ]);

        $spaceId = trim($createSpace->getBody());
//        error_log("Workspace $spaceId");
        $output->writeln("Done ($spaceId)");

        $output->write("Creating milestone ... ");

        $milestone              = new Milestone();
        $milestone->title       = "Project Scoping";
        $milestone->description = "Please go ahead and scope this project";

//        error_log(json_encode($milestone));

        $createMilestone = $client->post("/v1/workspaces/$spaceId/milestones", [
            "json"  => $milestone,
            "debug" => $debug
        ]);

        $milestoneId = trim($createMilestone->getBody());

        $output->writeln("Done ($milestoneId");

        $grabMilestone = $client->get("/v1/milestones/{$milestoneId}", ["debug" => $debug]);
        $milestoneDetail = \GuzzleHttp\json_decode($grabMilestone->getBody()->getContents());
//        print_r($milestoneDetail);
        $milestoneLink = $milestoneDetail->permalink;

        $output->write("Attaching brief ... ");
        $filePath = $input->getArgument('filePath');
        if (strlen($filePath) > 0) {
            $uploadCommand = new UploadFileCommand();
        }

        $uuid = $uploadCommand->getResult(
            new ArrayInput(['filePath' => $filePath], $uploadCommand->getDefinition()),
            $output
        );

        $assets                   = new DeferredAsset();
        $assets->deferredAssetIds = [$uuid];

        $attachFile = $client->post("/v1/milestones/$milestoneId/files", [
            "json"  => $assets,
            "debug" => $debug
        ]);

        $output->writeln("Done");

        $output->writeln("Check it out $milestoneLink");
    }
}