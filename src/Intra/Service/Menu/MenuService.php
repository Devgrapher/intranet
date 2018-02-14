<?php

namespace Intra\Service\Menu;

use Intra\Service\Auth\ExceptOuter;
use Intra\Service\Auth\ExceptStudioD;
use Intra\Service\Auth\ExceptTaAuth;
use Intra\Service\Auth\OnlyHolidayEditable;
use Intra\Service\Auth\OnlyPaymentAdmin;
use Intra\Service\Auth\OnlyPolicyRecipientEditable;
use Intra\Service\Auth\OnlyPressManager;
use Intra\Service\Auth\OnlyTeamManager;
use Intra\Service\Auth\OnlyUserManager;
use Intra\Service\Auth\PublicAuth;
use Intra\Service\Support\SupportPolicy;
use Intra\Service\User\UserSession;

class MenuService
{
    const RIDI_GUIDE_URL = 'https://ridicorp.atlassian.net/wiki/spaces/bws/pages';

    public static function getMenuLinkList(): array
    {
        $left_menu_list = [];
        $right_menu_list = [];

        if (UserSession::isLogined()) {
            if ($_ENV['INTRA_DOMAIN'] == 'ridi.com') {
                $left_menu_list = [
                    new Link('직원찾기', '/users/', new ExceptStudioD()),
                    new Link('RIDI PUBLIC', self::RIDI_GUIDE_URL, null, '_blank'),
                    new Link('전사 주간 업무 요약', '/weekly/', new ExceptOuter(), '_blank'),
                    new Link('회의실', '/rooms/', new ExceptTaAuth(), null, 'time'),
                    new LinkList('업무용 서비스', [
                        new Link('아사나 (업무협업)', 'https://app.asana.com', null, '_blank'),
                        new Link('Confluence (위키)', 'https://ridicorp.atlassian.net', null, '_blank'),
                        new Link('비즈플레이 (개인영수관리)', 'https://www.bizplay.co.kr', null, '_blank'),
                        new Link('월급날 (급여관리)', 'http://htms.himgt.net', new ExceptOuter(), '_blank'),
                    ]),
                    new LinkList('근태관리', [
                        new Link('팀 휴가현황', '/holidays/?team=' . UserSession::getSelfDto()->team, new OnlyTeamManager()),
                        new Link('휴가신청', '/holidays/', new ExceptStudioD()),
                        new Link('얼리파마', '/flextime/', new ExceptOuter()),
                    ]),
                    new LinkList('지원요청', [
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DEVICE), '/support/' . SupportPolicy::TYPE_DEVICE, new ExceptStudioD(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_FAMILY_EVENT), '/support/' . SupportPolicy::TYPE_FAMILY_EVENT, new ExceptOuter()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_BUSINESS_CARD), '/support/' . SupportPolicy::TYPE_BUSINESS_CARD, new ExceptOuter()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DEPOT), '/support/' . SupportPolicy::TYPE_DEPOT, (new ExceptOuter())->accept(['hr.ta'])),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_GIFT_CARD_PURCHASE), '/support/' . SupportPolicy::TYPE_GIFT_CARD_PURCHASE),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_TRAINING), '/support/' . SupportPolicy::TYPE_TRAINING, new ExceptOuter()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DELIVERY), '/support/' . SupportPolicy::TYPE_DELIVERY, new ExceptOuter(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_PRESENT), '/support/' . SupportPolicy::TYPE_PRESENT, new ExceptOuter(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_VPN), '/support/' . SupportPolicy::TYPE_VPN, new ExceptOuter()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_USB), '/support/' . SupportPolicy::TYPE_USB, new ExceptOuter()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_BUSSINESSTRIP), '/support/' . SupportPolicy::TYPE_BUSSINESSTRIP, new ExceptOuter(), '_blank'),
                    ]),
                    new Link('결제요청', '/payments/', (new ExceptTaAuth())->accept(['hr.ta', 'device.ta3', 'story.op2']), null),
                    new Link('비용정산', '/receipts/', new ExceptOuter(), null, 'piggy-bank'),
                    new Link('조직도', '/organization/chart', new ExceptOuter(), '_blank'),
                ];
            } else {
                $left_menu_list = [
                    new Link('공지사항', '/posts/notice', new PublicAuth()),
                    new Link('휴가신청', '/holidays/', new PublicAuth()),
                    new LinkList('지원요청', [
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DEVICE), '/support/' . SupportPolicy::TYPE_DEVICE, new PublicAuth()),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_FAMILY_EVENT), '/support/' . SupportPolicy::TYPE_FAMILY_EVENT),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_BUSINESS_CARD), '/support/' . SupportPolicy::TYPE_BUSINESS_CARD),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DEPOT), '/support/' . SupportPolicy::TYPE_DEPOT, (new ExceptTaAuth())->accept(['hr.ta'])),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_GIFT_CARD_PURCHASE), '/support/' . SupportPolicy::TYPE_GIFT_CARD_PURCHASE),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_TRAINING), '/support/' . SupportPolicy::TYPE_TRAINING),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_DELIVERY), '/support/' . SupportPolicy::TYPE_DELIVERY, new PublicAuth(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_PRESENT), '/support/' . SupportPolicy::TYPE_PRESENT, new PublicAuth(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_VPN), '/support/' . SupportPolicy::TYPE_VPN, new PublicAuth(), '_blank'),
                        new Link(SupportPolicy::getColumnTitle(SupportPolicy::TYPE_USB), '/support/' . SupportPolicy::TYPE_USB, new PublicAuth(), '_blank'),
                    ]),
                    new Link('비용정산', '/receipts/', new PublicAuth()),
                    new Link('회의실', '/rooms/', new PublicAuth()),
                    new LinkList('업무용 서비스', [
                        new Link('아사나 (업무협업)', 'https://app.asana.com', null, '_blank'),
                        new Link('Confluence (위키)', 'https://ridicorp.atlassian.net', null, '_blank'),
                        new Link('모두싸인 (전자계약)', 'https://modusign.co.kr', null, '_blank'),
                        new Link('비즈플레이 (개인영수관리)', 'https://www.bizplay.co.kr', null, '_blank'),
                        new Link('월급날(급여관리)', 'http://htms.himgt.net', new ExceptOuter(), '_blank'),
                    ]),
                    new Link('리디 생활 가이드', self::RIDI_GUIDE_URL, null, '_blank'),
                    new Link('급여관리', 'http://htms.himgt.net', new ExceptTaAuth(), '_blank'),
                ];
            }

            $right_menu_list = [
                new LinkList('관리자', [
                    new Link('권한 설정', '/admin/policy', new OnlyPolicyRecipientEditable()),
                    new Link('메일 수신 설정', '/admin/recipient', new OnlyPolicyRecipientEditable()),
                    new Link('직원 목록', '/admin/user', new OnlyUserManager()),
                    new Link('휴가 조정', '/admin/holiday', new OnlyHolidayEditable()),
                    new Link('회의실 설정', '/admin/room', new OnlyPolicyRecipientEditable()),
                    new Link('회의실 정기 예약', '/admin/event_group', new OnlyPolicyRecipientEditable()),
                    new Link('보도자료 관리', '/admin/press', new OnlyPressManager()),
                    new Link('결제', '/admin/payment', new OnlyPaymentAdmin(), null, null, 'beta', 'primary'),
                ], 'wrench'),
                new Link('내정보', '/users/me', new PublicAuth(), null, 'user'),
                new Link('로그아웃', '/usersession/logout', new PublicAuth(), null, 'log-out'),
            ];
        }

        return [
            'left' => $left_menu_list,
            'right' => $right_menu_list
        ];
    }
}
