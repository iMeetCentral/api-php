<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 7/21/15
 * Time: 6:43 PM
 */

namespace CentralDesktop\API;


trait WithClient {
    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(){
        return ClientFactory::getClient();
    }
}