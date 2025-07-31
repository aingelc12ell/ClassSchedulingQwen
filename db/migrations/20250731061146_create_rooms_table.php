<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRoomsTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('rooms');
        $table->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('capacity', 'integer')
            ->addIndex(['name'], ['unique' => true])
            ->create();
        /*
         $table->addColumn('id', 'string', ['limit' => 50])
            ->addColumn('capacity', 'integer')
            ->addIndex(['id'], ['unique' => true])
            ->create();
         */
    }

    public function down()
    {
        $this->table('rooms')->drop()->save();
    }
}
