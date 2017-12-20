<?php
declare(strict_types=1);

use Intra\Service\User\UserPolicy;
use Phinx\Seed\AbstractSeed;

class Policies extends AbstractSeed
{
    public function run()
    {
        $policies = [
            ['keyword' => UserPolicy::POLICY_RECIPIENT_EDITTABLE, 'name' => '권한설정 & 메일수신자 설정'],
            ['keyword' => UserPolicy::USER_SPOT_EDITABLE, 'name' => '직원찾기'],
            ['keyword' => UserPolicy::USER_MANAGER, 'name' => '직원목록'],
            ['keyword' => UserPolicy::PRESS_MANAGER, 'name' => '보도자료'],
            ['keyword' => UserPolicy::HOLIDAY_EDITABLE, 'name' => '휴가'],
            ['keyword' => UserPolicy::POST_ADMIN, 'name' => '공지사항'],
            ['keyword' => UserPolicy::PAYMENT_ADMIN, 'name' => '결제요청'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_ALL, 'name' => '모든 지원요청'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_DEVICE, 'name' => '지원요청 - 업무환경'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_FAMILY_EVENT, 'name' => '지원요청 - 경조지원'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_BUSINESS_CARD, 'name' => '지원요청 - 명함신청'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_DEPOT, 'name' => '지원요청 - 구매요청'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_GIFT_CARD_PURCHASE, 'name' => '지원요청 - 상품권구매'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_TRAINING, 'name' => '지원요청 - 사외수강'],
            ['keyword' => UserPolicy::SUPPORT_ADMIN_VPN, 'name' => '지원요청 - VPN신청'],
            ['keyword' => UserPolicy::RECEIPTS_ADMIN, 'name' => '비용정산'],
            ['keyword' => UserPolicy::TA, 'name' => 'TA 제한'],
            ['keyword' => UserPolicy::GUEST, 'name' => 'Guest 제한'],
        ];

        $this->table('policy')
            ->insert($policies)
            ->save();
    }
}
