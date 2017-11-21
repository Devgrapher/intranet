<?php
declare(strict_types=1);

use Intra\Service\Mail\MailRecipient;
use Phinx\Seed\AbstractSeed;

class Recipients extends AbstractSeed
{
    public function run()
    {
        $recipients = [
            [ 'keyword' => MailRecipient::HOLIDAY, 'name' => '휴가' ],
            [ 'keyword' => MailRecipient::PAYMENT, 'name' => '결제요청' ],
            [ 'keyword' => MailRecipient::SUPPORT_ALL, 'name' => '지원요청 - 전체' ],
            [ 'keyword' => MailRecipient::SUPPORT_DEVICE, 'name' => '지원요청 - 업무환경' ],
            [ 'keyword' => MailRecipient::SUPPORT_FAMILY_EVENT, 'name' => '지원요청 - 경조지원' ],
            [ 'keyword' => MailRecipient::SUPPORT_BUISINESS_CARD, 'name' => '지원요청 - 명함신청' ],
            [ 'keyword' => MailRecipient::SUPPORT_DEPOT, 'name' => '지원요청 - 구매요청' ],
            [ 'keyword' => MailRecipient::SUPPORT_GIFT_CARD_PURCHASE, 'name' => '지원요청 - 상품권구매' ],
            [ 'keyword' => MailRecipient::SUPPORT_TRAINING, 'name' => '지원요청 - 사외수강' ],
            [ 'keyword' => MailRecipient::SUPPORT_VPN, 'name' => '지원요청 - VPN신청' ],
        ];

        $this->table('recipients')
            ->insert($recipients)
            ->save();
    }
}
