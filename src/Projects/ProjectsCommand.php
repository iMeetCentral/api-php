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
            ->addArgument("filePath", InputArgument::OPTIONAL, "Path to a file");
    }

    protected
    function execute(InputInterface $input, OutputInterface $output) {
        $client = ClientFactory::getClient();

        $wsGL       = new WorkspaceGroupListCommand();
        $groups     = new Sequence($wsGL->getResult(new ArrayInput([], $wsGL->getDefinition()), $output));
        $notGrouped = $groups->filter(function ($group) {
            return $group->details->workspaceGroupName == 'Not Grouped';
        })->first();

        error_log(print_r($notGrouped));
        $notGroupedHash = $notGrouped->get()->id;

        $create                = new Create();
        $create->workspaceName = $input->getArgument('name');
        $create->urlShortName  = $input->getArgument('shortName');

        error_log(print_r($create, true));
        error_log(json_encode($create));
        $createSpace = $client->post("/v1/workspaceGroups/$notGroupedHash/workspaces", [
            "json" => $create,
            //            "debug" => true
        ]);

        $spaceId = trim($createSpace->getBody());
        error_log("Workspace $spaceId");

        $milestone        = new Milestone();
        $milestone->title = "Project Scoping";

        error_log(json_encode($milestone));

        $createMilestone = $client->post("/v1/workspaces/$spaceId/milestones", [
            "json"  => $milestone,
            "debug" => true
        ]);

        $milestoneId = trim($createMilestone->getBody());

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

        error_log(json_encode($assets));

        $attachFile = $client->post("/v1/milestones/$milestoneId/files", [
            "json"  => $assets,
            "debug" => true
        ]);


    }
}