<?php


namespace Maba\Bundle\OAuthCommerceAccountsBundle\Exception;


class ApiException extends \Exception
{
    protected $errorCode;
    protected $errorDescription;
    protected $statusCode;

    public function __construct($errorCode, $errorDescription = '', $statusCode = 400, $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
        $this->statusCode = $statusCode;
        parent::__construct('Api Exception ' . $errorCode . ' ' . $errorDescription, 0, $previous);
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }


}