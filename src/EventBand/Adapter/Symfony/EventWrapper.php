<?php
/**
 * @LICENSE_TEXT
 */

namespace EventBand\Adapter\Symfony;

use EventBand\Event;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class EventWrapper
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class EventWrapper implements Event
{
    /**
     * Name of the event. Used for async events dispatching.
     * Since the name property of symfony events is deprecated, we moved it here.
     * It will not break any symfony concept, because will be set under the hood of symfony adapter.
     * No need to set it from application logic
     *
     * @var string
     */
    protected $name;

    /**
     * @var SymfonyEvent
     */
    private $symfonyEvent;

    /**
     * @param SymfonyEvent $symfonyEvent
     */
    public function __construct(SymfonyEvent $symfonyEvent)
    {
        $this->symfonyEvent = $symfonyEvent;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}