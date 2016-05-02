<?php

namespace CentralDesktop\API\Users;

use CentralDesktop\API\PaginationCommand;

class UsersCommand extends PaginationCommand  {

    protected
    function configure() {
        parent::configure();
        $this
            ->setName('users:list')
            ->setDescription('List users');
    }

    protected
    function get_endpoint() { return '/v1/users'; }
}