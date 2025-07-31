<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSubjectsTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('subjects');
        $table->addColumn('code', 'string', ['limit' => 50])
            ->addColumn('title', 'string', ['limit' => 150])
            ->addColumn('units', 'integer')
            ->addColumn('weekly_hours', 'integer')
            ->addIndex(['code'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('subjects')->drop()->save();
    }
}
