<?php

/**
 * Created by IntelliJ IDEA.
 * User: wqiu
 * Date: 28/09/15
 * Time: 15:14
 */

namespace weblicht\OAuth\ResourceServer;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit_Framework_TestCase;

class UnityResourceServerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException weblicht\OAuth\ResourceServer\ResourceServerException
     * @expectedExceptionMessage invalid_token
     */
    public function testValidToken(){
        $pluginToken = new MockPlugin();
        $pluginToken->addResponse(new Response(401, null, null));
        $pluginUser = new MockPlugin();
        $pluginUser->addResponse(new Response(401, null, null));
        $clientToken = new Client("https://auth.example.org/tokeninfo");
        $clientUser = new Client("https://auth.example.org/tokeninfo");
        $clientToken->addSubscriber($pluginToken);
        $clientUser->addSubscriber($pluginUser);
        $rs = new UnityResourceServer($clientToken, $clientUser);
        $rs->setAuthorizationHeader("Bearer KO6LhTjR2RZlviaVxDvSp-xuGU0LuC2qW1tpY52wplE");
        echo("Haha");
        $rs->verifyToken();
    }

}