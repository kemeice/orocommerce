<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\MigrationConstraintTrait;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroAccountBundleStage2 implements Migration, RenameExtensionAwareInterface, OrderedMigrationInterface
{
    use MigrationConstraintTrait;

    /**
     * @var RenameExtension
     */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameAccountUserSidebarWidget($schema);
        $this->renameAccountUserSidebarState($schema);
        $this->renameCustomerSettings($schema);
        $this->renameAccountUserAddressToAddressType($schema);
        $this->renameAccountAddressToAddressType($schema);
    }

    /**
     * @param Schema $schema
     */
    private function renameAccountUserSidebarWidget(Schema $schema)
    {
        $table = $schema->getTable("oro_customer_user_sdbar_wdg");

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_account_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'fk_customer_user_id'
        );
        $table->addIndex(['position'], 'oro_cus_sdar_wdgs_pos_idx', []);
        $table->addIndex(['customer_user_id', 'placement'], 'oro_cus_sdbr_wdgs_usr_place_idx', []);
    }

    /**
     * @param Schema $schema
     */
    private function renameAccountUserSidebarState(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_user_sdbar_st');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_account_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );

        $table->addUniqueIndex(['customer_user_id', 'position'], 'oro_cus_sdbar_st_unq_idx');
    }

    /**
     * @param Schema $schema
     */
    private function renameCustomerSettings(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_user_settings');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_account_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addUniqueIndex(['customer_user_id', 'website_id'], 'unique_acc_user_website');
    }

    /**
     * @param Schema $schema
     */
    private function renameAccountUserAddressToAddressType(Schema $schema)
    {
        $table = $schema->getTable('oro_cus_usr_adr_to_adr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user_address'),
            ['customer_user_address_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addUniqueIndex(['customer_user_address_id', 'type_name'], 'oro_customer_user_adr_id_type_name_idx');
    }

    /**
     * @param Schema $schema
     */
    private function renameAccountAddressToAddressType(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_adr_adr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_address'),
            ['customer_address_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addUniqueIndex(['customer_address_id', 'type_name'], 'oro_customer_adr_id_type_name_idx');
    }
    
    /**
     * Sets the RenameExtension
     *
     * @param RenameExtension $renameExtension
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * Get the order of this migration
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}