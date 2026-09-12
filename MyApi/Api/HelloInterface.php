<?php
namespace Codilar\MyApi\Api;

interface HelloInterface
{
    /**
     * Returns a greeting message.
     *
     * @param string $name
     * @return string
     */
    public function getGreeting($name);
}
