<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTeachersTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('teachers');
        /*$table->addColumn('id', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('qualified_subject_ids', 'text', ['null' => true]) // JSON stored as text
            ->addIndex(['id'], ['unique' => true])
            ->create();*/
        $table->addColumn('code', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('qualified_subject_ids', 'text', ['null' => true]) // JSON stored as text
            ->addIndex(['code'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('teachers')->drop()->save();
    }
}
