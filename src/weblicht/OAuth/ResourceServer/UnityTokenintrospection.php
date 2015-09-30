<?php
/**
 * Created by IntelliJ IDEA.
 * User: wqiu
 * Date: 25/09/15
 * Time: 15:53
 */

namespace weblicht\OAuth\ResourceServer;

use fkooman\OAuth\Common\Scope;

class UnityTokenintrospection
{
    private $responseToken;
    private $responseUser;

    public function __construct(array $responseToken, array $responseUser)
    {
        $this->responseToken = $responseToken;
        $this->responseUser= $responseUser;
    }

    /**
     * REQUIRED.  Boolean indicator of whether or not the presented
     * token is currently active.
     */
    public function getActive()
    {
        return true;
    }

    /**
     * OPTIONAL.  Integer timestamp, measured in the number of
     * seconds since January 1 1970 UTC, indicating when this token will
     * expire.
     */
    public function getExpiresAt()
    {
        return $this->getKeyValue('expires_at');
    }

    /**
     * OPTIONAL.  Integer timestamp, measured in the number of
     * seconds since January 1 1970 UTC, indicating when this token was
     * originally issued.
     */
    public function getIssuedAt()
    {
        return $this->getKeyValue('iat');
    }

    /**
     * OPTIONAL.  A space-separated list of strings representing the
     * scopes associated with this token, in the format described in
     * Section 3.3 of OAuth 2.0 [RFC6749].
     *
     * @return fkooman\OAuth\Common\Scope
     */
    public function getScope()
    {
        $scopeValue = $this->getKeyValue('scope');
        $scopeString = join(" ", $scopeValue);
        if (false === $scopeValue) {
            return new Scope();
        }

        return Scope::fromString($scopeString);
    }

    /**
     * OPTIONAL.  Client Identifier for the OAuth Client that
     * requested this token.
     */
    public function getClientId()
    {
        return $this->getKeyValue('client_id');
    }

    /**
     * OPTIONAL.  Local identifier of the Resource Owner who authorized
     * this token.
     */
    public function getSub()
    {
        $principal = $this->getKeyValue('sub');
        if (false === $principal) {
            return false;
        } else {
            if (isset($principal['name'])) {
                return $principal['name'];
            } else {
                return false;
            }
        }
    }

    /**
     * OPTIONAL.  Service-specific string identifier or list of string
     * identifiers representing the intended audience for this token.
     */
    public function getAud()
    {
        return $this->getKeyValue('audience');
    }

    /**
     * OPTIONAL.  Type of the token as defined in OAuth 2.0
     * section 5.1.
     */
    public function getTokenType()
    {
        return $this->getKeyValue('token_type');
    }

    /**
     * Get the complete response from the tokeninfo endpoint
     */
    public function getTokeninfo()
    {
        return $this->responseToken;
    }


    /**
     * @return array
     * Get the complete response from the userinfo endpoint
     */
    public function getUserinfo()
    {
        return $this->responseUser;
    }

    private function getKeyValue($key)
    {
        if (isset($this->responseToken[$key])) {
            return $this->responseToken[$key];
        } else {
            if (isset($this->responseUser[$key])) {
                return $this->responseUser[$key];
            }
        }

        return false;
    }
}