<?php

namespace OroB2B\Bundle\AccountBundle\Layout\DataProvider;

use Oro\Component\Layout\ContextInterface;
use Oro\Component\Layout\AbstractServerRenderDataProvider;

use OroB2B\Bundle\AccountBundle\Entity\AccountUser;

class FrontendAccountAddressGridProvider extends AbstractServerRenderDataProvider
{
    const ADDRESS_DIPLAY_CHANGE_THRESHOLD = 8;

    /**
     * {@inheritdoc}
     */
    public function getData(ContextInterface $context)
    {
        /** @var AccountUser $accountUser */
        $accountUser = $context->data()->get('entity');
        $account = $accountUser->getAccount();

        return $account->getAddresses()->count() >= self::ADDRESS_DIPLAY_CHANGE_THRESHOLD;
    }
}
