<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddRoomEventGroups extends AbstractMigration
{
    public function change()
    {
        $room_events = $this->table('room_events');
        $room_events->addColumn('deleted_at', 'timestamp', [
            'null' => true,
            'comment' => '삭제 시간'
        ])->save();

        $this->execute('UPDATE room_events SET deleted_at = CURRENT_TIMESTAMP() WHERE deleted = 1');

        $this->table('room_event_groups')
            ->addColumn('uid', 'integer', [
                'signed' => false,
                'comment' => '유저 id (users)'
            ])
            ->addColumn('room_id', 'integer', [
                'comment' => '회의실 id (rooms)'
            ])
            ->addColumn('desc', 'string', [
                'length' => 255,
                'comment' => '예약 내용 텍스트'
            ])
            ->addColumn('from_date', 'date', [
                'comment' => '정기 예약 시작일 (YYYY-MM-dd)'
            ])
            ->addColumn('to_date', 'date', [
                'comment' => '정기 예약 종료일 (YYYY-MM-dd)'
            ])
            ->addColumn('from_time', 'time', [
                'comment' => '정기 예약 시작시간 (hh:mm:ss)'
            ])
            ->addColumn('to_time', 'time', [
                'comment' => '정기 예약 종료시간 (hh:mm:ss)'
            ])
            ->addColumn('days_of_week', 'string', [
                'comment' => '예약한 요일들을 ,로 묶은 문자열. (ex: 월, 수, 금 예약인 경우 값은 \'1,3,5\')'
            ])
            ->addColumn('deleted_at', 'timestamp', [
                'null' => true,
                'comment' => '삭제된 시간 (삭제되지 않은 경우 null)'
            ])
            ->addForeignKey('uid', 'users', 'uid')
            ->addIndex('room_id', ['name' => 'rid'])
            ->create();
    }
}
