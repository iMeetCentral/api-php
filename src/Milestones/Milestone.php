<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 9/7/16
 * Time: 4:18 PM
 */

namespace CentralDesktop\API\Milestones;


class Milestone {


    public $title = "Default Milestone Title";
    public $dueDate;
    public $description;

//    public $milestoneTemplate;

    public
    function __construct() {
        $this->dueDate = (new \DateTime())->format("Y-m-d");
    }
}