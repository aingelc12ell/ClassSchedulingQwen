<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateConflictExemptionsTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('conflict_exemptions');
        $table->addColumn('type', 'string', ['limit' => 20]) // student, teacher, room
        ->addColumn('entity_id', 'integer')
            ->addColumn('conflict_type', 'string', ['limit' => 20]) // schedule, capacity
            ->addColumn('reason', 'text')
            ->addColumn('expires_at', 'timestamp', ['null' => true])
            ->addIndex(['type', 'entity_id', 'conflict_type'], ['unique' => true])
            ->create();
    }

    public function down()
    {
        $this->table('conflict_exemptions')->drop()->save();
    }
}
