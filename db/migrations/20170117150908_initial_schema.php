<?php

use Phinx\Migration\AbstractMigration;

class InitialSchema extends AbstractMigration
{
    public function change()
	{
		$this->createUsers();
		$this->createPosts();
	}

	private function createUsers()
	{
    	$this->table('users', ['id' => false, 'primary_key' => ['uid']])
			->addColumn('uid', 'integer', ['identity' => true, 'signed' => false])
			->addColumn('id', 'string', ['length' => 20])
			->addColumn('pass', 'string', ['length' => 60])
			->addColumn('name', 'string')
			->addColumn('email', 'string')
			->addColumn('team', 'string')
			->addColumn('team_detail', 'string')
			->addColumn('position', 'string', ['length' => 10])
			->addColumn('outer_call', 'string', ['length' => 20])
			->addColumn('inner_call', 'string', ['length' => 20, 'null' => true])
			->addColumn('mobile', 'string', ['length' => 20])
			->addColumn('birth', 'date')
			->addColumn('image', 'string', ['length' => 100])
			->addColumn('on_date', 'date', ['default' => '9999-01-01'])
			->addColumn('off_date', 'date', ['default' => '9999-01-01'])
			->addColumn('extra', 'text')
			->addColumn('personcode', 'integer')
			->addColumn('ridibooks_id', 'string', ['length' => 32])
			->addColumn('is_admin', 'boolean')
			->addColumn('comment', 'string', ['null' => true])
			->addIndex('id', ['unique' => true])
			->create();
    }

    private function createPosts()
	{
		$this->table('posts')
			->addColumn('group', 'string', ['length' => 20])
			->addColumn('title', 'string', ['length' => 200])
			->addColumn('uid', 'integer', ['signed' => false])
			->addColumn('is_sent', 'boolean')
			->addColumn('content_html', 'text')
			->addTimestamps()
			->addColumn('deleted_at', 'timestamp', ['null' => true])
			->addForeignKey('uid', 'users', 'uid')
			->create();
	}
}
