<?php
namespace Intra\Service\Payment;

use Intra\Service\User\Organization;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

class UserPaymentConst
{
    const CATEGORY_ASSETS = '자산(PC, 공사대금 등)';
    const CATEGORY_OFFICE_SUPPLIES = '소모품비 (페이퍼샵 판매를 위한 포장자재, 기타 소모품)';
    const CATEGORY_WELFARE_EXPENSE = '복리후생비';
    const CATEGORY_USER_BOOK_CANCELMENT = '고객 서점 캐시 환불';
    const CATEGORY_USER_DEVICE_CANCELMENT = '고객 기기 AS비용 환불';
    const CATEGORY_USER_STORY_CANCELMENT = '고객 스토리 코인 환불';
    const CATEGORY_WRITERS_FEE = '원고료';

    public static function getConstValueByKey($key)
    {
        if ($key == 'manager_uid') {
            $ret = [];
            foreach (UserDtoFactory::createAvailableUserDtos() as $user) {
                $ret[$user->uid] = $user->name;
            }

            return json_encode($ret);
        }
        if (!self::isExistByKey($key)) {
            return null;
        }
        $ret = [];
        foreach (self::getByKey($key) as $v) {
            $ret[$v] = $v;
        }

        return json_encode($ret);
    }

    public static function get()
    {
        $const = [];
        $const['team'] = Organization::readTeamNames();
        $const['team_detail'] = Organization::readTeamDetailNames();
        $const['product'] = ['리디북스', '리디스토리', '페이퍼샵', '공통'];
        $const['tax'] = ['Y', 'N/A'];
        $const['is_account_book_registered'] = ['N', 'Y'];
        $const['paytype'] = ['현금', '법인카드'];
        $const['status'] = ['결제 대기중', '결제 완료'];
        $const['category'] = [
            '상품매입 (페이퍼샵 판매용 상품 매입 비용)',
            '운반비 (택배, 퀵서비스 이용대금)',
            '잡급 (단기, 일회성 급여)',
            self::CATEGORY_WELFARE_EXPENSE,
            self::CATEGORY_OFFICE_SUPPLIES,
            self::CATEGORY_ASSETS,
            '도서구입 및 인쇄비 (쿠폰, 상품권, 명함등 인쇄, 도서구입비)',
            '선인세',
            '제작비용 (표지, 일러스트, 편집료 등)',
            '광고선전비',
            '지급수수료',
            '매절(유료판매용)',
            '매절(무료배포용)',
            '저작권양수',
            self::CATEGORY_USER_BOOK_CANCELMENT,
            self::CATEGORY_USER_DEVICE_CANCELMENT,
            self::CATEGORY_USER_STORY_CANCELMENT,
            self::CATEGORY_WRITERS_FEE,
            '거래처 경조사 지원금',
        ];
        if (UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            $const['category'][] = '기타';
        }
        $const['pay_date'] = ['선택해주세요', '7일', '10일', '25일', '월말일', '긴급'];

        return $const;
    }

    public static function isExistByKey($key)
    {
        return isset(self::get()[$key]);
    }

    public static function getByKey($key)
    {
        return self::get()[$key];
    }
}
