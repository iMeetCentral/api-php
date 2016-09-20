<?php
/**
 * Created by IntelliJ IDEA.
 * User: thyde
 * Date: 7/21/15
 * Time: 6:06 PM
 */

namespace CentralDesktop\API;

use CentralDesktop\API\Auth\AccessToken;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use JWT;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ClientFactory {
    public static $debug = false;
    private static $token = null;
    /** @var Container Configuration Container */
    private static $container = null;

    private static
    function setup() {
        if (!is_null(self::$container)) {
            return self::$container;
        }

        $container = new ContainerBuilder();
        $loader    = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../config"));

        $loader->load('client_config.yml');
        $loader->load('config.yml');


        if (empty($container->getParameter('client.id')) || empty($container->getParameter('client.key'))) {
            throw new Exception("Please configure client.id and client.key in client_config.yml");
        }

        self::$container = $container;

        return $container;
    }

    public static
    function getClient() {
        $container = self::setup();

        $stack = new HandlerStack();
        $stack->setHandler(\GuzzleHttp\choose_handler());
//        $stack->setHandler( Proxy::wrapSync(new CurlMultiHandler(), new CurlHandler()));
//        $stack->setHandler(new StreamHandler());

        $token = self::getAuthToken();


        $stack->push(Middleware::mapRequest(function (RequestInterface $r) use ($token) {
            return $r->withHeader('Authorization', 'Bearer ' . $token->accessToken);
        }, 'oauth_bearer'));


        $client = new Client(
            [
                'handler'  => $stack,
                'base_uri' => $container->getParameter('edge.base_url'),
                'verify'   => false
            ]
        );

        return $client;
    }


    public static
    function getAuthToken() {
        if (self::$token instanceof AccessToken && self::$token->isFresh()) {
            return self::$token;
        }

        $container = self::setup();

        $client_id = $container->getParameter('client.id');
        $key       = $container->getParameter('client.key');

        $user = [
            "iss" => $client_id,
            "aud" => $container->getParameter('auth.cd.issuer'),
            "exp" => time() + 600000,
            "iat" => time() - 15000,
            "scp" => $container->getParameter('auth.cd.scp')
        ];

        $auth_token = JWT::encode($user, $key, 'RS256');

        $form_params = [
            'grant_type' => $container->getParameter('auth.cd.grant_type'),
            'assertion'  => $auth_token
        ];

        $client        = new Client();
        $http_response = $client->post(
            $container->getParameter('auth.cd.auth_url'),
            ['headers' => ['Content-Type' => 'application/json'],
             'debug'   => self::$debug,
             'body'    => json_encode($form_params),
             'verify'  => false
            ]

        );

        $json_response = json_decode($http_response->getBody()->getContents());

        self::$token = new AccessToken($json_response->access_token,
                                       $json_response->token_type,
                                       $json_response->expires_in);

        return self::$token;
    }
}
