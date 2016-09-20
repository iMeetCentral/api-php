<?php

namespace CentralDesktop\API\WorkspaceGroups;

use CentralDesktop\API\PaginationCommand;

class WorkspaceGroupListCommand extends PaginationCommand  {

    protected
    function configure() {
        parent::configure();
        $this
            ->setName('workspacegroups:list')
            ->setDescription('List workspace groups');
    }

    protected
    function get_endpoint() { return '/v1/workspaceGroups'; }

}