<?php
/**
 * Created by IntelliJ IDEA.
 * User: kzhu
 * Date: 5/2/16
 * Time: 12:57 PM
 */
namespace CentralDesktop\API\Times;

use CentralDesktop\API\PaginationCommand;

class TimesCommand extends PaginationCommand  {

    protected
    function configure() {
        parent::configure();
        $this
            ->setName('times:list')
            ->setDescription('List times');
    }

    protected
    function get_endpoint() { return '/v1/times'; }
}