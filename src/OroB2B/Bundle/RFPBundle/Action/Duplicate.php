<?php

namespace OroB2B\Bundle\RFPBundle\Action;

use Oro\Bundle\ActionBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;

use OroB2B\Bundle\RFPBundle\Factory\DuplicatorFactory;
use OroB2B\Component\Duplicator\Duplicator;

class Duplicate extends AbstractAction
{
    const OPTION_KEY_TARGET = 'target';
    const OPTION_KEY_SETTINGS = 'settings';
    const OPTION_KEY_ATTRIBUTE = 'attribute';

    /**
     * @var Duplicator
     */
    protected $duplicator;

    /**
     * @var DuplicatorFactory
     */
    protected $duplicatorFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $target = $context->getEntity();
        $settings = [];
        if (isset($this->options[self::OPTION_KEY_TARGET])) {
            $target = $this->contextAccessor->getValue($context, $this->options[self::OPTION_KEY_TARGET]);
        }
        if (isset($this->options[self::OPTION_KEY_SETTINGS])) {
            $settings = $this->options[self::OPTION_KEY_SETTINGS];
        }
        $copyObject = $this->getDuplicator()->duplicate($target, $settings);
        $this->contextAccessor->setValue($context, $this->options[self::OPTION_KEY_ATTRIBUTE], $copyObject);
    }

    /**
     * Initialize action based on passed options.
     *
     * @param array $options
     * @return ActionInterface
     * @throws InvalidParameterException
     */
    public function initialize(array $options)
    {
        if (!empty($options[self::OPTION_KEY_TARGET]) && !is_string($options[self::OPTION_KEY_TARGET])) {
            throw new InvalidParameterException('Option \'target\' should be string');
        }
        if (!empty($options[self::OPTION_KEY_SETTINGS]) && !is_array($options[self::OPTION_KEY_SETTINGS])) {
            throw new InvalidParameterException('Option \'settings\' should be array');
        }
        if (empty($options[self::OPTION_KEY_ATTRIBUTE])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        $this->options = $options;

        return $this;
    }

    /**
     * @return Duplicator
     */
    protected function getDuplicator()
    {
        if (!$this->duplicator) {
            $this->duplicator = $this->duplicatorFactory->create();
        }

        return $this->duplicator;
    }

    /**
     * @param DuplicatorFactory $duplicatorFactory
     */
    public function setDuplicatorFactory($duplicatorFactory)
    {
        $this->duplicatorFactory = $duplicatorFactory;
    }
}
