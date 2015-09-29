<?php
/**
 * Created by IntelliJ IDEA.
 * User: wqiu
 * Date: 25/09/15
 * Time: 15:40
 */

namespace weblicht\OAuth\ResourceServer;


use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Client;

class UnityResourceServer
{
    /* @var Client */
    private $httpClientToken;

    /* @var Client */
    private $httpClientUser;

    /* @var string|null */
    private $authorizationHeader;

    /**
     * @param Client $httpClientToken
     * the client pointing to the tokeninfo endpoint
     * @param Client $httpClientUser
     * the client pointing to the userinfo endpoint
     */
    public function __construct(Client $httpClientToken, Client $httpClientUser)
    {
        $this->httpClientToken = $httpClientToken;
        $this->httpClientUser = $httpClientUser;
        $this->authorizationHeader = null;
    }

    public function setAuthorizationHeader($authorizationHeader)
    {
        // must be string
        if (!is_string($authorizationHeader)) {
            return;
        }
        // string should have at least length 8
        if (7 >= strlen($authorizationHeader)) {
            return;
        }
        // string should start with "Bearer "
        if (0 !== stripos($authorizationHeader, "Bearer ")) {
            return;
        }
        $this->authorizationHeader = $authorizationHeader;
    }


    public function verifyToken()
    {
        // one type should at least be set
        if (null === $this->authorizationHeader ) {
            throw new ResourceServerException("no_token", "missing token");
        }

        $this->validateTokenSyntax(substr($this->authorizationHeader, 7));

        try {
            $requestTokeninfo = $this->httpClientToken->get();
            $requestTokeninfo->addHeader("Authroization", $this->authorizationHeader);
            $requestUserinfo = $this->httpClientUser->get();
            $requestUserinfo->addHeader("Authorization", $this->authorizationHeader);
            $responseTokeninfo = $requestTokeninfo->send();
            $responseUserinfo = $requestUserinfo->send();

            /* Unity AS returns HTTP 401 if the token is not valid or has expired */
            if ($responseTokeninfo->getStatusCode() !== 200) {
                throw new ResourceServerException("invalid_token", "the access token has expired or not active");
            }

            $responseDataTokeninfo = $responseTokeninfo->json();
            $responseDataUserinfo = $responseUserinfo->json();
            if (!is_array($responseDataTokeninfo) || !is_array($responseDataUserinfo)) {
                throw new ResourceServerException(
                    "internal_server_error",
                    "malformed response data from introspection endpoint"
                );
            }

            $tokenIntrospection = new UnityTokeninfo($responseData);

            return $tokenIntrospection;
        } catch (RuntimeException $e) {
            throw new ResourceServerException(
                "internal_server_error",
                "unable to contact introspection endpoint or malformed response data"
            );
        }
    }

    private function validateTokenSyntax($token)
    {
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        if (1 !== preg_match('|^[[:alpha:][:digit:]-._~+/]+=*$|', $token)) {
            throw new ResourceServerException(
                "invalid_token",
                "the access token is not a valid b64token"
            );
        }
    }
}