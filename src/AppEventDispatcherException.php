<?php

namespace Ostrolucky\AppEventDispatcher;

class AppEventDispatcherException extends \DomainException
{
    /**
     * @param string $message
     * @param string $replacement
     */
    public function __construct($message, $replacement)
    {
        parent::__construct(sprintf($message, $replacement));
    }
}
