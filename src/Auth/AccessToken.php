<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 8/11/15
 * Time: 6:41 PM
 */

namespace CentralDesktop\API\Auth;


use DateInterval;
use DateTime;

class AccessToken {
    const TOKEN_FUDGE_FATCTOR_SECONDS = 5;
    public $accessToken;
    /**
     * @var DateTime Time when this token expires
     */
    public $expires;
    public $type;


    public function __construct($token, $type, $expiresInSeconds){
        $this->accessToken = $token;
        $this->type = $type;

        $expires = (new DateTime())->add(
            new DateInterval('PT' . ($expiresInSeconds - self::TOKEN_FUDGE_FATCTOR_SECONDS) . 'S')
        );

        $this->expires = $expires;
    }

    public function isFresh(){
        $now  = new DateTime();

        $diff = $now->diff($this->expires);
        $seconds = $diff->format('%R%s');

        return ($seconds > 0);
    }

    public function __toString() {
        return ucfirst($this->type)."({$this->accessToken} {$this->expires->format(DATE_ISO8601)})";
    }
}