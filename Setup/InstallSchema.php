<?php

namespace SoftBuild\HitPay\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package SoftBuild\HitPay\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('softbuild_hitpay_payments')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('softbuild_hitpay_payments')
            )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Post ID'
                )
                ->addColumn(
                    'payment_id',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Payment ID'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    false,
                    [],
                    'Status'
                )
                ->addColumn(
                    'cart_id',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Cart Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Order Id'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_DECIMAL,
                    '20,2',
                    [],
                    'Order Amount'
                )
                ->addColumn(
                    'currency_id',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Post URL Key'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    11,
                    [],
                    'Post URL Key'
                )
                ->addColumn(
                    'is_paid',
                    Table::TYPE_BOOLEAN,
                    false,
                    [],
                    'Post Post Content'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Payments Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('softbuild_hitpay_payments'),
                $setup->getIdxName(
                    $installer->getTable('softbuild_hitpay_payments'),
                    ['id', 'payment_id'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['id', 'payment_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            );
        }
        $installer->endSetup();
    }
}