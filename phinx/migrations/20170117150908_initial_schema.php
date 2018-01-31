<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class InitialSchema extends AbstractMigration
{
    public function change()
    {
        $this->createUsers();

        $this->createCronHistory();
        $this->createFiles();
        $this->createFlextimes();
        $this->createFonts();
        $this->createHolidays();
        $this->createPayments();
        $this->createPaymentAccept();
        $this->createPosts();
        $this->createPress();
        $this->createPrograms();
        $this->createReceipts();
        $this->createRoomEvents();
        $this->createRooms();
        $this->createSupportBusinessCard();
        $this->createSupportDepot();
        $this->createSupportDevice();
        $this->createSupportFamilyEvent();
        $this->createSupportGiftCard();
        $this->createUserPrograms();
    }

    private function createCronHistory()
    {
        $this->table('cron_history')
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('signature', 'string', ['length' => 255])
            ->addIndex(['signature', 'reg_date'])
            ->create();
    }

    private function createFiles()
    {
        $this->table('files')
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('group', 'string', ['length' => 32])
            ->addColumn('key', 'string', ['length' => 32])
            ->addColumn('original_filename', 'string', ['length' => 255])
            ->addColumn('location', 'string', ['length' => 255])
            ->addColumn('reg_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('is_delete', 'boolean', ['null' => true])
            ->addForeignKey('uid', 'users', 'uid')
            ->create();
    }

    private function createFlextimes()
    {
        $this->table('flextimes')
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('manager_uid', 'integer', ['signed' => false])
            ->addColumn('keeper_uid', 'integer', ['signed' => false])
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date')
            ->addColumn('start_time', 'time')
            ->addColumn('weekdays', 'string', ['length' => 20])
            ->addTimestamps()
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('manager_uid', 'users', 'uid')
            ->addForeignKey('keeper_uid', 'users', 'uid')
            ->create();
    }

    private function createFonts()
    {
        $this->table('fonts')
            ->addColumn('font', 'string', ['length' => 255])
            ->addColumn('able', 'boolean')
            ->create();
    }

    private function createHolidays()
    {
        $this->table('holidays', ['id' => false, 'primary_key' => ['holidayid']])
            ->addColumn('holidayid', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('request_date', 'timestamp')
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('manager_uid', 'integer', ['signed' => false])
            ->addColumn('yearly', 'integer', ['signed' => false])
            ->addColumn('type', 'string', ['length' => 20])
            ->addColumn('date', 'date')
            ->addColumn('cost', 'float')
            ->addColumn('keeper_uid', 'integer', ['signed' => false])
            ->addColumn('phone_emergency', 'string', ['length' => 20])
            ->addColumn('memo', 'text')
            ->addColumn('hidden', 'boolean', ['default' => false])
            ->addForeignKey('manager_uid', 'users', 'uid')
            ->addForeignKey('keeper_uid', 'users', 'uid')
            ->create();
    }

    private function createPaymentAccept()
    {
        $this->table('payment_accept')
            ->addColumn('paymentid', 'integer', ['signed' => true])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('user_type', 'string')
            ->addColumn('created_datetime', 'timestamp')
            ->addForeignKey('paymentid', 'payments', 'paymentid')
            ->addForeignKey('uid', 'users', 'uid')
            ->create();
    }

    private function createPayments()
    {
        $this->table('payments', ['id' => false, 'primary_key' => ['paymentid']])
            ->addColumn('paymentid', 'integer', ['identity' => true, 'signed' => true])
            ->addColumn('uuid', 'biginteger', ['signed' => true, 'default' => 0])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('manager_uid', 'integer', ['signed' => false])
            ->addColumn('request_date', 'datetime')
            ->addColumn('month', 'string', ['length' => 7])
            ->addColumn('team', 'string', ['length' => 255])
            ->addColumn('product', 'string', ['length' => 255])
            ->addColumn('category', 'string', ['length' => 255])
            ->addColumn('desc', 'string', ['length' => 255])
            ->addColumn('company_name', 'string', ['length' => 255])
            ->addColumn('bank', 'string', ['length' => 255])
            ->addColumn('bank_account', 'string', ['length' => 255])
            ->addColumn('bank_account_owner', 'string', ['length' => 255])
            ->addColumn('price', 'integer', ['signed' => true, 'default' => 0])
            ->addColumn('pay_date', 'datetime')
            ->addColumn('tax', 'string', ['length' => 255])
            ->addColumn('tax_export', 'string', ['length' => 3, 'null' => true])
            ->addColumn('tax_date', 'date', ['null' => true])
            ->addColumn('is_account_book_registered', 'enum', ['values' => ['Y', 'N']])
            ->addColumn('note', 'string', ['length' => 255])
            ->addColumn('paytype', 'string', ['length' => 255, 'default' => '미정'])
            ->addColumn('status', 'enum', ['values' => ['결제 완료', '삭제', '결제 대기중'], 'default' => '결제 대기중'])
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('manager_uid', 'users', 'uid')
            ->addIndex('request_date')
            ->addIndex('tax_date')
            ->create();
    }

    private function createPress()
    {
        $this->table('press')
            ->addColumn('date', 'string', ['length' => 10])
            ->addColumn('media', 'string', ['length' => 255])
            ->addColumn('title', 'string', ['length' => 255])
            ->addColumn('link_url', 'string', ['length' => 255])
            ->addColumn('note', 'string', ['length' => 255])
            ->create();
    }

    private function createPrograms()
    {
        $this->table('programs')
            ->addColumn('program', 'string', ['length' => 255])
            ->addColumn('able', 'boolean')
            ->addIndex('program', ['unique' => true])
            ->create();
    }

    private function createReceipts()
    {
        $this->table('receipts', ['id' => false, 'primary_key' => ['receiptid']])
            ->addColumn('receiptid', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('date', 'date')
            ->addColumn('title', 'string', ['length' => 100])
            ->addColumn('scope', 'string', ['length' => 20])
            ->addColumn('type', 'string', ['length' => 20])
            ->addColumn('cost', 'integer')
            ->addColumn('note', 'string', ['length' => 100])
            ->addColumn('payment', 'string', ['length' => 20])
            ->addForeignKey('uid', 'users', 'uid')
            ->addIndex(['uid', 'date'])
            ->create();
    }

    private function createRoomEvents()
    {
        $this->table('room_events')
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('room_id', 'integer')
            ->addColumn('desc', 'string', ['length' => 255])
            ->addColumn('from', 'datetime')
            ->addColumn('to', 'datetime')
            ->addColumn('deleted', 'boolean', ['default' => false])
            ->addForeignKey('uid', 'users', 'uid')
            ->addIndex('room_id', ['name' => 'rid'])
            ->create();
    }

    private function createRooms()
    {
        $this->table('rooms')
            ->addColumn('type', 'string', ['length' => 20])
            ->addColumn('name', 'string', ['length' => 255])
            ->addColumn('is_visible', 'boolean')
            ->create();
    }

    private function createSupportBusinessCard()
    {
        $this->table('support_business_card')
            ->addColumn('uuid', 'string', ['length' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('is_accepted', 'boolean')
            ->addColumn('accept_uid', 'integer', ['signed' => false])
            ->addColumn('accepted_datetime', 'datetime')
            ->addColumn('is_completed', 'boolean')
            ->addColumn('completed_uid', 'integer', ['signed' => false])
            ->addColumn('completed_datetime', 'datetime')
            ->addColumn('receiver_area', 'string', ['length' => 10])
            ->addColumn('receiver_uid', 'integer', ['signed' => false])
            ->addColumn('name', 'string', ['length' => 255])
            ->addColumn('name_in_english', 'string', ['length' => 255])
            ->addColumn('team', 'string', ['length' => 255])
            ->addColumn('team_detail', 'string', ['length' => 255])
            ->addColumn('grade_korean', 'string', ['length' => 255])
            ->addColumn('grade_english', 'string', ['length' => 255])
            ->addColumn('call_interal', 'string', ['length' => 255])
            ->addColumn('call_extenal', 'string', ['length' => 255])
            ->addColumn('email', 'string', ['length' => 100])
            ->addColumn('fax', 'string', ['length' => 255])
            ->addColumn('address', 'string', ['length' => 255])
            ->addColumn('count', 'integer')
            ->addColumn('count_detail', 'integer')
            ->addColumn('date', 'date')
            ->addColumn('is_deleted', 'boolean')
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('accept_uid', 'users', 'uid')
            ->addForeignKey('completed_uid', 'users', 'uid')
            ->addForeignKey('receiver_uid', 'users', 'uid')
            ->create();
    }

    private function createSupportDepot()
    {
        $this->table('support_depot')
            ->addColumn('uuid', 'string', ['length' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('is_accepted', 'boolean')
            ->addColumn('accept_uid', 'integer')
            ->addColumn('accepted_datetime', 'datetime')
            ->addColumn('is_completed', 'boolean')
            ->addColumn('completed_uid', 'integer')
            ->addColumn('completed_datetime', 'datetime')
            ->addColumn('receiver_area', 'string', ['length' => 10])
            ->addColumn('receiver_uid', 'integer')
            ->addColumn('name', 'string', ['length' => 255])
            ->addColumn('category', 'string', ['length' => 255])
            ->addColumn('detail', 'string', ['length' => 255])
            ->addColumn('request_date', 'date')
            ->addColumn('note', 'string', ['length' => 255])
            ->addColumn('is_exist', 'string', ['length' => 4])
            ->addColumn('label', 'string', ['length' => 255])
            ->addColumn('is_deleted', 'boolean')
            ->addForeignKey('uid', 'users', 'uid')
            ->create();
    }

    private function createSupportDevice()
    {
        $this->table('support_device')
            ->addColumn('uuid', 'string', ['length' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('is_completed', 'boolean')
            ->addColumn('completed_uid', 'integer')
            ->addColumn('completed_datetime', 'datetime')
            ->addColumn('team', 'string', ['length' => 20])
            ->addColumn('category', 'string', ['length' => 50])
            ->addColumn('detail', 'string', ['length' => 255])
            ->addColumn('request_date', 'date')
            ->addColumn('note', 'string', ['length' => 255])
            ->addColumn('is_deleted', 'boolean')
            ->addForeignKey('uid', 'users', 'uid')
            ->create();
    }

    private function createSupportFamilyEvent()
    {
        $this->table('support_family_event')
            ->addColumn('uuid', 'string', ['length' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('is_accepted', 'boolean')
            ->addColumn('accept_uid', 'integer', ['signed' => false])
            ->addColumn('accepted_datetime', 'datetime')
            ->addColumn('is_completed', 'boolean')
            ->addColumn('completed_uid', 'integer', ['signed' => false])
            ->addColumn('completed_datetime', 'datetime')
            ->addColumn('receiver_area', 'string', ['length' => 10])
            ->addColumn('outer_receiver_business', 'string', ['length' => 255])
            ->addColumn('outer_receiver_name', 'string', ['length' => 255])
            ->addColumn('outer_receiver_detail', 'string', ['length' => 255])
            ->addColumn('team', 'string', ['length' => 50])
            ->addColumn('receiver_worker_uid', 'integer', ['signed' => false])
            ->addColumn('category', 'string', ['length' => 50])
            ->addColumn('category_detail', 'string', ['length' => 255])
            ->addColumn('request_date', 'date')
            ->addColumn('cash', 'integer')
            ->addColumn('flower_category', 'string', ['length' => 20])
            ->addColumn('flower_category_detail', 'string', ['length' => 255])
            ->addColumn('flower_receiver', 'string', ['length' => 20])
            ->addColumn('flower_call', 'string', ['length' => 20])
            ->addColumn('flower_address', 'string', ['length' => 200])
            ->addColumn('flower_datetime', 'datetime')
            ->addColumn('note', 'string', ['length' => 255])
            ->addColumn('is_deleted', 'boolean')
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('accept_uid', 'users', 'uid')
            ->addForeignKey('completed_uid', 'users', 'uid')
            ->addForeignKey('receiver_worker_uid', 'users', 'uid')
            ->create();
    }

    private function createSupportGiftCard()
    {
        $this->table('support_gift_card')
            ->addColumn('uuid', 'string', ['length' => 32])
            ->addColumn('reg_date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('uid', 'integer', ['signed' => false])
            ->addColumn('is_accepted', 'boolean')
            ->addColumn('accept_uid', 'integer', ['signed' => false])
            ->addColumn('accepted_datetime', 'datetime')
            ->addColumn('is_completed', 'boolean')
            ->addColumn('completed_uid', 'integer', ['signed' => false])
            ->addColumn('completed_datetime', 'datetime')
            ->addColumn('category', 'string', ['length' => 50])
            ->addColumn('cash', 'integer')
            ->addColumn('expire_date', 'datetime')
            ->addColumn('count', 'integer')
            ->addColumn('random_file', 'string', ['length' => 255])
            ->addColumn('request_date', 'date')
            ->addColumn('note', 'string', ['length' => 255])
            ->addColumn('image_file', 'string', ['length' => 255])
            ->addColumn('is_deleted', 'boolean')
            ->addForeignKey('uid', 'users', 'uid')
            ->addForeignKey('accept_uid', 'users', 'uid')
            ->addForeignKey('completed_uid', 'users', 'uid')
            ->create();
    }

    private function createUserPrograms()
    {
        $this->table('userprograms', ['id' => false, 'primary_key' => ['pk_id']])
            ->addColumn('pk_id', 'integer')
            ->addColumn('timestamp', 'timestamp')
            ->addColumn('name', 'string', ['length' => 255])
            ->addColumn('computer_name', 'string', ['length' => 255])
            ->addColumn('ip', 'string', ['length' => 15])
            ->addColumn('programs', 'text')
            ->addColumn('fonts', 'text')
            ->create();
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
            ->addColumn('team_detail', 'string', ['length' => 60, 'null' => true])
            ->addColumn('position', 'string', ['length' => 10, 'null' => true])
            ->addColumn('outer_call', 'string', ['length' => 20, 'null' => true])
            ->addColumn('inner_call', 'string', ['length' => 20, 'null' => true])
            ->addColumn('mobile', 'string', ['length' => 20, 'null' => true])
            ->addColumn('birth', 'date', ['null' => true])
            ->addColumn('image', 'string', ['length' => 100, 'null' => true])
            ->addColumn('on_date', 'date', ['default' => '9999-01-01'])
            ->addColumn('off_date', 'date', ['default' => '9999-01-01'])
            ->addColumn('extra', 'text', ['null' => true])
            ->addColumn('personcode', 'integer', ['null' => true])
            ->addColumn('ridibooks_id', 'string', ['length' => 32, 'null' => true])
            ->addColumn('is_admin', 'boolean', ['default' => false])
            ->addColumn('comment', 'string', ['null' => true])
            ->addIndex('id', ['unique' => true, 'name' => 'login'])
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
