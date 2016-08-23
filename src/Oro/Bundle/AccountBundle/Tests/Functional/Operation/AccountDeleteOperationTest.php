<?php

namespace Oro\Bundle\AccountBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\AccountBundle\Entity\Account;

/**
 * @dbIsolation
 */
class AccountDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());

        $this->loadFixtures(
            [
                'Oro\Bundle\AccountBundle\Tests\Functional\DataFixtures\LoadAccounts'
            ]
        );
    }

    public function testDelete()
    {
        /** @var Account $account */
        $account = $this->getReference('account.orphan');

        $this->assertDeleteOperation($account->getId(), 'orob2b_account.entity.account.class', 'orob2b_account_index');
    }
}