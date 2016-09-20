<?php

namespace CentralDesktop\API\Workspaces;

use CentralDesktop\API\PaginationCommand;

class WorkspaceListCommand extends PaginationCommand  {

    protected
    function configure() {
        parent::configure();
        $this
            ->setName('workspaces:list')
            ->setDescription('List workspaces');
    }

    protected
    function get_endpoint() { return '/v1/workspaces'; }

}