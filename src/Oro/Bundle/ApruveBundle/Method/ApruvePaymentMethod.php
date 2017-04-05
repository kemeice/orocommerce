<?php

namespace Oro\Bundle\ApruveBundle\Method;

use Oro\Bundle\ApruveBundle\Method\Config\ApruveConfigInterface;
use Oro\Bundle\ApruveBundle\Method\PaymentAction\Executor\PaymentActionExecutor;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class ApruvePaymentMethod implements PaymentMethodInterface
{
    const TYPE = 'apruve';

    const COMPLETE = 'complete';
    const CANCEL = 'cancel';

    /**
     * @var ApruveConfigInterface
     */
    protected $config;

    /**
     * @var PaymentActionExecutor
     */
    protected $paymentActionExecutor;

    /**
     * @param ApruveConfigInterface $config
     * @param PaymentActionExecutor $paymentActionExecutor
     */
    public function __construct(ApruveConfigInterface $config, PaymentActionExecutor $paymentActionExecutor)
    {
        $this->config = $config;
        $this->paymentActionExecutor = $paymentActionExecutor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        return $this->paymentActionExecutor->execute($action, $this->config, $paymentTransaction);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($actionName)
    {
        return $this->paymentActionExecutor->supports($actionName);
    }
}
