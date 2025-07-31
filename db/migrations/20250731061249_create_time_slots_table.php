<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTimeSlotsTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('time_slots');
        /*$table->addColumn('id', 'integer')
            ->addColumn('label', 'string', ['limit' => 100])
            ->addColumn('start_time', 'time')
            ->addColumn('end_time', 'time')
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addIndex(['id'], ['unique' => true])
            ->create();*/
        $table->addColumn('label', 'string', ['limit' => 100])
            ->addColumn('start_time', 'time')
            ->addColumn('end_time', 'time')
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addIndex(['label'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('time_slots')->drop()->save();
    }
}
