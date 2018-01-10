<?php
namespace Intra\Service\Mail;

use Intra\Model\RecipientModel;

class MailRecipient
{
    const PAYMENT = 'payment';
    const HOLIDAY = 'holiday';
    const SUPPORT_ALL = 'support_all';
    const SUPPORT_DEVICE = 'support_device';
    const SUPPORT_FAMILY_EVENT = 'support_familiyevent';
    const SUPPORT_BUISINESS_CARD = 'support_bussinesscard';
    const SUPPORT_DEPOT = 'support_depot';
    const SUPPORT_GIFT_CARD_PURCHASE = 'support_giftcard_purchase';
    const SUPPORT_TRAINING = 'support_training';
    const SUPPORT_VPN = 'support_vpn';
    const SUPPORT_USB = 'support_usb';

    public static function getAllWithUsers(): array
    {
        $recipients = RecipientModel::all();
        $roles = $recipients->toArray();

        $assigned = [];
        foreach ($recipients as $recipient) {
            $assigned[$recipient['keyword']] = $recipient->users->pluck('uid')->all();
        }

        return [
            'roles' => $roles,
            'assigned' => $assigned,
        ];
    }

    public static function setAll(array $assigned)
    {
        foreach ($assigned as $keyword => $uids) {
            self::setRecipient($keyword, $uids);
        }
    }

    public static function setRecipient(string $keyword, array $uids)
    {
        RecipientModel::where('keyword', $keyword)->first()->users()->sync($uids);
    }

    public static function getMails(string $event): array
    {
        $users = RecipientModel::where('keyword', $event)->first()->users;
        if ($users) {
            return [];
        }

        return $users->pluck('email')->toArray();
    }
}
