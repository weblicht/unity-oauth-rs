<?php

/**
 * Created by IntelliJ IDEA.
 * User: wqiu
 * Date: 28/09/15
 * Time: 15:14
 */

namespace weblicht\OAuth\ResourceServer;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use PHPUnit_Framework_TestCase;

class UnityResourceServerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException weblicht\OAuth\ResourceServer\ResourceServerException
     * @expectedExceptionMessage invalid_token
     */
    public function testExpiredToken(){
        $mock = new MockHandler([
            new Response(401)
        ]);
        $handler = HandlerStack::create($mock);

//        $clientToken = new Client(['base_uri'=> 'http://weblicht.sfs.uni-tuebingen.de/oauth2/tokeninfo', 'handler' => $handler]);
        $clientToken = new Client(['base_uri'=> 'https://weblicht.sfs.uni-tuebingen.de/oauth2/tokeninfo']);
//        $clientUser = new Client(['base_uri'=> 'http://example.org/oauth2/userinfo', 'handler' => $handler]);
        $clientUser = new Client(['base_uri'=> 'https://weblicht.sfs.uni-tuebingen.de/oauth2/userinfo']);
        $rs = new UnityResourceServer($clientToken, $clientUser);
        $rs->setAuthorizationHeader("Bearer ThFD5mMv5nTcytNlEDa3N5pfBasy7C4gAFL8X9ffwrQ");
        echo("Haha");
        $rs->verifyToken();
    }

    public function testValidToken(){
        $mock = new MockHandler([
            new Response(200, [], Psr7\stream_for(json_encode(['exp' => 1443604107,
                                                                'sub' => "ea4e2dfd-95b1-4469-aec6-a8e7c2e1c015",
                                                                'scope' => ['profile'],
                                                                'client_id' => null]))),
            new Response(200, [], Psr7\stream_for(json_encode(['sub' => "ea4e2dfd-95b1-4469-aec6-a8e7c2e1c015",
                                                                'cn' => 'Wei Qiu',
                                                                'email' => 'wei@qiu.es'])))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['base_uri'=> 'http://weblicht.sfs.uni-tuebingen.de/oauth2/', 'handler' => $handler]);
//        $clientToken = new Client(['base_uri'=> 'https://weblicht.sfs.uni-tuebingen.de/oauth2/tokeninfo']);
//        $clientUser = new Client(['base_uri'=> 'https://weblicht.sfs.uni-tuebingen.de/oauth2/userinfo']);
        $rs = new UnityResourceServer($client);
        $rs->setAuthorizationHeader("Bearer urn8IyQktx_o4_upDnewvc2N4Gda55UT-TY0tnR4guw");
        $this->assertInstanceOf("weblicht\\OAuth\\ResourceServer\\UnityTokenIntrospection", $rs->verifyToken());
    }

    public function testValidTokenRequestHistory(){
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create();
        $stack->push($history);
        $client = new Client(['base_uri'=> 'https://weblicht.sfs.uni-tuebingen.de/', 'handler' => $stack]);
        $authorizationHeader = 'Bearer urn8IyQktx_o4_upDnewvc2N4Gda55UT-TY0tnR4guw';
        $responseTokeninfo = $client->get('', ['headers' => ['Authorization' => 'haha']]);
        $responseUserinfo = $client->get('', ['headers' => ['Authorization' => $authorizationHeader]]);

    }
}