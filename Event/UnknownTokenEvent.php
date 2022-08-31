<?php

namespace Sherlockode\UserConfirmationBundle\Event;

use Symfony\Component\HttpFoundation\Response;

class UnknownTokenEvent
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     *
     * @return $this
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response|null $response
     *
     * @return $this
     */
    public function setResponse(?Response $response): self
    {
        $this->response = $response;

        return $this;
    }
}
