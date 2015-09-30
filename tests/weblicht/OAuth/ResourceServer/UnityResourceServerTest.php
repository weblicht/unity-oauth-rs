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

        $clientToken = new Client(['base_uri'=> 'http://example.org/tokeninfo', 'handler' => $handler]);
        $clientUser = new Client(['base_uri'=> 'http://example.org/userinfo', 'handler' => $handler]);
        $rs = new UnityResourceServer($clientToken, $clientUser);
        $rs->setAuthorizationHeader("Bearer KO6LhTjR2RZlviaVxDvSp-xuGU0LuC2qW1tpY52wplE");
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
        $clientToken = new Client(['base_uri'=> 'http://example.org/tokeninfo', 'handler' => $handler]);
        $clientUser = new Client(['base_uri'=> 'http://example.org/userinfo', 'handler' => $handler]);
        $rs = new UnityResourceServer($clientToken, $clientUser);
        $rs->setAuthorizationHeader("Bearer KO6LhTjR2RZlviaVxDvSp-xuGU0LuC2qW1tpY52wplE");
        $this->assertInstanceOf("weblicht\\OAuth\\ResourceServer\\UnityTokenIntrospection", $rs->verifyToken());
    }

}