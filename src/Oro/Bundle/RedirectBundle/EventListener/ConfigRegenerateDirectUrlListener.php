<?php

namespace Oro\Bundle\RedirectBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\RedirectBundle\Async\Topics;
use Oro\Bundle\RedirectBundle\Form\Storage\RedirectStorage;
use Oro\Bundle\RedirectBundle\Model\MessageFactoryInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Run Direct URLs regeneration for given entityClass when configParameter is changed.
 */
class ConfigRegenerateDirectUrlListener
{
    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @var string
     */
    private $configParameter;

    /**
     * @var RedirectStorage
     */
    private $redirectStorage;

    /**
     * @var MessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param MessageProducerInterface $messageProducer
     * @param RedirectStorage $redirectStorage
     * @param MessageFactoryInterface $messageFactory
     * @param string $configParameter
     * @param string $entityClass
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        RedirectStorage $redirectStorage,
        MessageFactoryInterface $messageFactory,
        $configParameter,
        $entityClass
    ) {
        $this->messageProducer = $messageProducer;
        $this->redirectStorage = $redirectStorage;
        $this->messageFactory = $messageFactory;
        $this->configParameter = $configParameter;
        $this->entityClass = $entityClass;
    }

    /**
     * @param ConfigUpdateEvent $event
     */
    public function onUpdateAfter(ConfigUpdateEvent $event)
    {
        if ($event->isChanged($this->configParameter)) {
            $createRedirect = $this->redirectStorage->getPrefixByKey($this->configParameter)->getCreateRedirect();

            $message = $this->messageFactory->createMassMessage($this->entityClass, [], $createRedirect);
            $this->messageProducer->send(Topics::REGENERATE_DIRECT_URL_FOR_ENTITY_TYPE, $message);
        }
    }
}