<?php
/**
 * Created by IntelliJ IDEA.
 * User: wqiu
 * Date: 25/09/15
 * Time: 15:45
 */

namespace weblicht\OAuth\ResourceServer;


class ResourceServerException extends \Exception
{

    private $description;
    private $realm;

    public function __construct($message, $description, $code = 0, Exception $previous = null)
    {
        $this->description = $description;
        $this->realm = "Resource Server";

        parent::__construct($message, $code, $previous);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setRealm($resourceServerRealm)
    {
        if (is_string($resourceServerRealm) && 0 < strlen($resourceServerRealm)) {
            $this->realm = $resourceServerRealm;
        }
    }

    public function getRealm()
    {
        return $this->realm;
    }

    public function getStatusCode()
    {
        switch ($this->message) {
            case "invalid_request":
                return 400;
            case "no_token":
                return 401;
            case "invalid_token":
                return 401;
            case "insufficient_scope":
                return 403;
            case "internal_server_error":
                return 500;
            default:
                return 400;
        }
    }

    public function getAuthenticateHeader()
    {
        if ("internal_server_error" === $this->getMessage()) {
            // no need for WWW-Authenticate header
            return null;
        }
        if ("no_token" === $this->getMessage()) {
            // no authorization header is a special case, the client did not
            // know authentication was required, so tell it now without giving
            // back an explicit error message
            return sprintf('Bearer realm="%s"', $this->getRealm());
        } else {
            return sprintf(
                'Bearer realm="%s",error="%s",error_description="%s"',
                $this->getRealm(),
                $this->getMessage(),
                $this->getDescription()
            );
        }
    }

}