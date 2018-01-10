<?php
declare(strict_types=1);

use Intra\Service\Mail\MailRecipient;
use Intra\Service\User\UserPolicy;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class AddSupportUsb extends AbstractMigration
{
    public function change()
    {
        $this->table('support_usb')
            ->addColumn('uuid', 'string', [
                'limit' => 32,
                'comment' => '일련번호'
            ])
            ->addColumn('reg_date', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => '등록일'
            ])
            ->addColumn('uid', 'integer', [
                'signed' => false,
                'null' => false,
                'comment' => 'user id'
            ])
            ->addColumn('email', 'string', [
                'length' => 100,
                'comment' => '신청자 email'
            ])
            ->addColumn('is_accepted', 'integer', [
                'limit' => MysqlAdapter::INT_TINY,
                'default' => false,
                'comment' => '승인여부'
            ])
            ->addColumn('accept_uid', 'integer', [
                'signed' => false,
                'default' => 0,
                'comment' => '승인자'
            ])
            ->addColumn('accepted_datetime', 'datetime', [
                'default' => '0000-00-00 00:00:00',
                'comment' => '승인 시각'
            ])
            ->addColumn('is_completed', 'integer', [
                'limit' => MysqlAdapter::INT_TINY,
                'default' => false,
                'comment' => '인사팀 승인여부'
            ])
            ->addColumn('completed_uid', 'integer', [
                'signed' => false,
                'default' => 0,
                'comment' => '인사팀 승인 user id'
            ])
            ->addColumn('completed_datetime', 'datetime', [
                'default' => '0000-00-00 00:00:00',
                'comment' => '인사팀 승인 시각'
            ])
            ->addColumn('usb_start_date', 'datetime', [
                'comment' => 'USB 사용 시작일'
            ])
            ->addColumn('usb_end_date', 'datetime', [
                'comment' => 'USB 사용 종료일'
            ])
            ->addColumn('is_deleted', 'integer', [
                'limit' => MysqlAdapter::INT_TINY,
                'default' => false,
                'comment' => '레코드 삭제 여부'
            ])
            ->create();

        $this->table('policy')
            ->insert(['keyword' => UserPolicy::SUPPORT_ADMIN_USB, 'name' => '지원요청 - USB신청'])
            ->save();

        $this->table('recipients')
            ->insert(['keyword' => MailRecipient::SUPPORT_USB, 'name' => '지원요청 - USB신청'])
            ->save();
    }
}
