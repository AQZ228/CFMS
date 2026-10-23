<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStaffStores extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('staff_stores', [
        'id' => false,
        'primary_key' => ['staff_id', 'store_id'],
    ]);

    $table
        ->addColumn('staff_id', 'integer', [
            'signed' => false,
        ])
        ->addColumn('store_id', 'integer', [
            'signed' => false,
        ])
        ->addForeignKey('staff_id', 'staff', 'id', [
            'delete' => 'RESTRICT',
            'update' => 'CASCADE',
        ])
        ->addForeignKey('store_id', 'stores', 'id', [
            'delete' => 'RESTRICT',
            'update' => 'CASCADE',
        ])
        ->create();
    }
}
