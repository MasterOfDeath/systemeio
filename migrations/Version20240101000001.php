<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create products and coupons tables';
    }

    public function up(Schema $schema): void
    {
        $products = $schema->createTable('products');
        $products->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $products->addColumn('name', Types::STRING, ['length' => 255]);
        $products->addColumn('price', Types::INTEGER);
        $products->setPrimaryKey(['id']);

        $coupons = $schema->createTable('coupons');
        $coupons->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $coupons->addColumn('code', Types::STRING, ['length' => 50]);
        $coupons->addColumn('type', Types::STRING, ['length' => 20]);
        $coupons->addColumn('value', Types::INTEGER);
        $coupons->setPrimaryKey(['id']);
        $coupons->addUniqueIndex(['code']);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('coupons')) {
            $schema->dropTable('coupons');
        }

        if ($schema->hasTable('products')) {
            $schema->dropTable('products');
        }
    }
}
