<?php

namespace Intra\Service\Support;

use Intra\Service\Support\Column\SupportColumn;
use Intra\Service\Support\Column\SupportColumnAccept;
use Intra\Service\Support\Column\SupportColumnAcceptDatetime;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnByValueCallback;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\Support\Column\SupportColumnCompleteDatetime;
use Intra\Service\Support\Column\SupportColumnCompleteUser;
use Intra\Service\Support\Column\SupportColumnDate;
use Intra\Service\Support\Column\SupportColumnDatetime;
use Intra\Service\Support\Column\SupportColumnFile;
use Intra\Service\Support\Column\SupportColumnMoney;
use Intra\Service\Support\Column\SupportColumnMutual;
use Intra\Service\Support\Column\SupportColumnReadonly;
use Intra\Service\Support\Column\SupportColumnRegisterUser;
use Intra\Service\Support\Column\SupportColumnSum;
use Intra\Service\Support\Column\SupportColumnTeam;
use Intra\Service\Support\Column\SupportColumnText;
use Intra\Service\Support\Column\SupportColumnTextDetail;
use Intra\Service\Support\Column\SupportColumnWorker;
use Intra\Service\User\UserConstant;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserJoinService;

class SupportPolicy
{
    const TYPE_DEVICE = 'device';
    const TYPE_FAMILY_EVENT = 'familyevent';
    const TYPE_BUSINESS_CARD = 'businesscard';
    const TYPE_DEPOT = 'depot';
    const TYPE_GIFT_CARD_PURCHASE = 'giftcard_purchase';

    const DB_TABLE = [
        self::TYPE_DEVICE => 'device',
        self::TYPE_FAMILY_EVENT => 'family_event',
        self::TYPE_BUSINESS_CARD => 'business_card',
        self::TYPE_DEPOT => 'depot',
        self::TYPE_GIFT_CARD_PURCHASE => 'gift_card_purchase',
    ];

    /**
     * @var SupportColumn[][]
     */
    private static $column_fields;
    private static $column_titles;

    /**
     * @param $target
     *
     * @return SupportColumn[]
     */
    public static function getColumnFields($target)
    {
        self::initColumnFields();

        return self::$column_fields[$target];
    }

    public static function getColumnFieldsTestUserDto($target, $self)
    {
        self::initColumnFields();
        $return_columns = self::$column_fields[$target];
        foreach ($return_columns as $key => $return_column) {
            if (!$return_column->isVisible($self)) {
                unset($return_columns[$key]);
            }
            $return_column->updateEditableForUser($self);
        }

        return $return_columns;
    }

    /**
     * @param $target
     *
     * @return SupportColumn[]
     */
    public static function getColumnTitle($target)
    {
        self::initColumnFields();

        return self::$column_titles[$target];
    }

    public static function getColumn($target, $key)
    {
        foreach (self::getColumnFields($target) as $column) {
            if ($column->key == $key) {
                return $column;
            }
        }
        throw new \Exception('invalid column ' . $target . ', ' . $key);
    }

    public static function getExplain($target)
    {
        //'업무환경 불편사항 문의',
        if ($target == self::TYPE_DEVICE) {
            return
'1. 사내 전산 H/W 장애문의
  -업무용 PC 및 노트북 등 H/W 장애 문의

2. SW 설치문의
  -업무용 SW 설치 필요 시 문의

3. 기타 장애문의
  -사무환경 및 사무집기 장애 문의';

            //'경조 지원'
        } elseif ($target == self::TYPE_FAMILY_EVENT) {
            return
'1. 공통
  -화환 수령 정보 상세 기재
  -증빙서류 업로드 필수

2. 거래처
  -거래처 [화환 종류]는 ‘기타’ 선택 후 [화환 상세]에 기재 (ex. 조화)

3. 임직원
  -적용 대상 : 수습직원을 포함한 정직원 (TA 의 경우 사망에 한해 유급휴가만 적용)
  -경조사 발생시 절차
    A. 본인 or 해당 부서장을 통한 경조사 등록
    B. 긴급하게 발생하는 조사의 경우 비상 연락망에 따라 연락 주시면, 선 경조규정 적용 후 등록 가능
    C. 비상연락망 : 본인 -> 팀장 -> 인사팀 -> 대표이사
  -경조휴가일 계산 : 휴일포함 (경조사 발생일 기준, 결혼에 한해 평일 기준 5일 적용)';

            //'명함 신청'
        } elseif ($target == self::TYPE_BUSINESS_CARD) {
            return
'1. 공통
  -매월 말일 제작 (불가피하게 급한 건만 제작(예정)일 설정)
  -필요한 정보만 입력하고 나머지 공란으로 둠

2. 항목 설명
  -대상자
    A. 직원 : 재직 중인 직원
    B. 현재 미입사 : 입사 예정인 직원

  -영문명 : 이름, 성의 각 첫 글자만 대문자로 입력 (ex.Gildong Hong)
  -직급(한글/영문) : 필요한 경우 기재
  -PHONE(내선) : 내선번호 있는 경우 기재';

            //'구매 요청'
        } elseif ($target == self::TYPE_DEPOT) {
            return
'1. 업무 상 필요한 자산 및 비품 구매 요청
2. 수령희망일은 배송기간 감안하여 설정';

            //'상품권 구매'
        } elseif ($target == self::TYPE_GIFT_CARD_PURCHASE) {
            return
'입금계좌 : 기업은행 477-016864-01-057 리디 주식회사
※ 거래처로부터 구매 문의를 받으신 경우, 재무팀에 문의하여 주세요.

1. 리디캐시 상품권의 종류와 구매가
   1) 1만원권 : 직원 구매가 9,500원
   2) 5만원권 : 직원 구매가 46,500원

2. 신청 안내
   1) 입금자명을 정확하게 기재하고 입금예정일시는 24시간내로 설정하여 입금을 진행해주세요.
   2) 권종 별로 각각 신청해주세요.

3. 참고 사항
   1) 서점 리디포인트 적립률을 준용하여 할인 가격이 책정되었으며, 권면금액별로 할인율이 적용된 점 참고 부탁 드립니다.
   2) 리디캐시 상품권은 유가증권으로 분류되어 신용카드나 휴대폰 등의 결제 수단으로는 구매가 불가능하며, 세법상 현금영수증 및 세금계산서가 발급되지 않습니다.
   3) 임직원께서 리디북스 서점에서 본인 아이디로 리디캐시 충전 시 충전금액의 30%를 비용 지원 받으실 수 있으나 (TA 지원불가), 실물의 리디캐시 상품권 구매 시에는 적용되지 않음을 참고 부탁 드립니다.
';
        } else {
            return "";
        }
    }

    private static function initColumnFields()
    {
        self::$column_titles = [
            self::TYPE_DEVICE => '업무환경 불편사항 문의',
            self::TYPE_FAMILY_EVENT => '경조 지원',
            self::TYPE_BUSINESS_CARD => '명함 신청',
            self::TYPE_DEPOT => '구매 요청',
            self::TYPE_GIFT_CARD_PURCHASE => '상품권 구매',
        ];

        $is_human_manage_team = function (UserDto $user_dto) {
            return $user_dto->team == UserConstant::TEAM_HUMAN_MANAGE;
        };
        $is_cash_flow_team = function (UserDto $user_dto) {
            return $user_dto->team == UserConstant::TEAM_CASH_FLOW;
        };
        $get_team_by_uid = function (SupportDto $support_dto) {
            $uid = $support_dto->dict['uid'];
            return UserJoinService::getTeamByUidSafe($uid);
        };
        self::$column_fields = [
            self::TYPE_DEVICE => [
                '일련번호' => new SupportColumnReadonly('uuid'),
                '일련번호2' => new SupportColumnReadonly('id'),
                '요청일' => new SupportColumnReadonly('reg_date'),
                '요청자' => new SupportColumnRegisterUser('uid'),
                '인사팀 처리' => new SupportColumnComplete('is_completed', $is_human_manage_team),
                '인사팀 처리자' => new SupportColumnCompleteUser('completed_uid', 'is_completed'),
                '인사팀 처리시각' => new SupportColumnCompleteDatetime('completed_datetime', 'is_completed'),
                '귀속부서' => new SupportColumnTeam('team'),
                '구분' => new SupportColumnCategory('category', ['사내 전산 H/W 장애문의', 'SW 설치문의', '기타 장애문의']),
                '상세내용' => new SupportColumnText('detail', '', '상세내용'),
                '조치희망일' => new SupportColumnDate('request_date', date('Y/m/d'), true),
                '비고' => new SupportColumnText('note', '', '비고'),
            ],
            self::TYPE_FAMILY_EVENT => [
                '일련번호' => new SupportColumnReadonly('uuid'),
                '일련번호2' => new SupportColumnReadonly('id'),
                '요청일' => new SupportColumnReadonly('reg_date'),
                '요청자' => new SupportColumnRegisterUser('uid'),
                '승인' => new SupportColumnAccept('is_accepted'),
                '승인자' => new SupportColumnAcceptUser('accept_uid', 'is_accepted'),
                '승인시각' => new SupportColumnAcceptDatetime('accepted_datetime', 'is_accepted'),
                '인사팀 처리' => new SupportColumnComplete('is_completed', $is_human_manage_team),
                '인사팀 처리자' => new SupportColumnCompleteUser('completed_uid', 'is_completed'),
                '인사팀 처리시각' => new SupportColumnCompleteDatetime('completed_datetime', 'is_completed'),
                '대상자' => new SupportColumnMutual(
                    'receiver_area',
                    [
                        '외부' => ['대상 업체(외부)', '대상 업체 담당자(외부)', '거래처 경조 사유(외부)'],
                        '내부' => ['귀속부서', '대상자(직원)', '분류', '분류 상세', '경조금']
                    ]
                ),
                '대상 업체(외부)' => new SupportColumnText('outer_receiver_business'),
                '대상 업체 담당자(외부)' => new SupportColumnText('outer_receiver_name'),
                '거래처 경조 사유(외부)' => new SupportColumnText('outer_receiver_detail'),
                '귀속부서' => new SupportColumnTeam('team'),
                '대상자(직원)' => new SupportColumnWorker('receiver_worker_uid'),
                '분류' => new SupportColumnCategory(
                    'category',
                    [
                        '졸업',
                        '결혼',
                        '자녀출생',
                        '장기근속(3년)',
                        '사망-형제자매 (배우자 형제자매포함)',
                        '사망-부모 (배우자 부모 포함)',
                        '사망-조부모 (배우자 조부모 포함)',
                        '기타'
                    ]
                ),
                '분류 상세' => (new SupportColumnText('category_detail'))->placeholder('나리디님 결혼'),
                '경조금' => (new SupportColumnMoney('cash'))->placeholder('미입력시 자동입력')->isVisibleIf($is_human_manage_team),
                '경조일자' => new SupportColumnDate('request_date', date('Y/m/d'), true),
                '화환 종류' => new SupportColumnCategory('flower_category', ['자동선택', '화환', '과일바구니', '조화', '기타']),
                '화환 상세' => new SupportColumnTextDetail('flower_category_detail', 'flower_category', ['기타', '화환']),
                '화환 수령자' => (new SupportColumnText('flower_receiver', '', '홍길동'))->isRequired(),
                '화환 연락처' => (new SupportColumnText('flower_call', '', '010-1234-5678'))->isRequired(),
                '화환 주소' => (new SupportColumnText('flower_address'))->isRequired(),
                '화환 도착일시' => (new SupportColumnDatetime('flower_datetime'))->placeholder('2016-01-02 07:10')->setTextInputType('datetime-local'),
                '증빙서류' => new SupportColumnFile('paper'),
                '비고' => new SupportColumnText('note', '', '비고'),
            ],
            self::TYPE_BUSINESS_CARD => [
                '일련번호' => new SupportColumnReadonly('uuid'),
                '일련번호2' => new SupportColumnReadonly('id'),
                '요청일' => new SupportColumnReadonly('reg_date'),
                '요청자' => new SupportColumnRegisterUser('uid'),
                '승인' => new SupportColumnAccept('is_accepted'),
                '승인자' => new SupportColumnAcceptUser('accept_uid', 'is_accepted'),
                '승인시각' => new SupportColumnAcceptDatetime('accepted_datetime', 'is_accepted'),
                '인사팀 처리' => new SupportColumnComplete('is_completed', $is_human_manage_team),
                '인사팀 처리자' => new SupportColumnCompleteUser('completed_uid', 'is_completed'),
                '인사팀 처리시각' => new SupportColumnCompleteDatetime('completed_datetime', 'is_completed'),
                '대상자' => new SupportColumnMutual(
                    'receiver_area',
                    [
                        '직원' => ['대상자(직원)'],
                        '현재 미입사' => ['대상자(현재 미입사)'],
                    ]
                ),
                '대상자(직원)' => new SupportColumnWorker('receiver_uid'),
                '대상자(현재 미입사)' => new SupportColumnText('name', '', '홍길동'),
                '영문명' => new SupportColumnText('name_in_english', '', 'Gildong Hong'),
                '부서명' => new SupportColumnTeam('team'),
                '부서명(기타)' => new SupportColumnText('team_detail', '', '외부노출용 직함'),
                '직급(한글)' => new SupportColumnText('grade_korean'),
                '직급(영문)' => new SupportColumnText('grade_english'),
                'MOBILE' => new SupportColumnText('call_extenal', '', '010-1234-5678'),
                'E-MAIL' => (new SupportColumnText('email', '', 'gd.hong@ridi.com'))->setTextInputType('email'),
                'PHONE(내선)' => new SupportColumnText('call_interal', '', '010-1234-5678'),
                'FAX' => new SupportColumnText('fax', '02-565-0332'),
                '주소' => new SupportColumnCategory('address', ['어반벤치빌딩 10층', '어반벤치빌딩 11층']),
                '수량' => new SupportColumnCategory('count', [50, 100, 150, 200, '기타 - 50매 단위']),
                '수량(기타)' => (new SupportColumnTextDetail('count_detail', 'count', ['기타 - 50매 단위']))->setTextInputType('number'),
                '제작(예정)일' => (new SupportColumnDate('date', '', true))->placeholder('미입력시 월말진행'),
            ],
            self::TYPE_DEPOT => [
                '일련번호' => new SupportColumnReadonly('uuid'),
                '일련번호2' => new SupportColumnReadonly('id'),
                '요청일' => new SupportColumnReadonly('reg_date'),
                '요청자' => new SupportColumnRegisterUser('uid'),
                '승인' => new SupportColumnAccept('is_accepted'),
                '승인자' => new SupportColumnAcceptUser('accept_uid', 'is_accepted'),
                '승인시각' => new SupportColumnAcceptDatetime('accepted_datetime', 'is_accepted'),
                '인사팀 처리' => new SupportColumnComplete('is_completed', $is_human_manage_team),
                '인사팀 처리자' => new SupportColumnCompleteUser('completed_uid', 'is_completed'),
                '인사팀 처리시각' => new SupportColumnCompleteDatetime('completed_datetime', 'is_completed'),
                '사용자(직원)' => new SupportColumnWorker('receiver_uid'),
                '분류' => new SupportColumnCategory(
                    'category',
                    [
                        '일반구매 (사무용품, 전산/기타 소모품, 테스트기기 등)',
                        '모니터',
                        'MAC (맥북, 아이맥)',
                        '노트북',
                        '데스크탑',
                        '서버 및 네트워크 장비',
                    ]
                ),
                '품목/수량' => new SupportColumnText('detail'),
                '구매사유' => new SupportColumnText('reason'),
                '수령희망일' => new SupportColumnDate('request_date', date('Y/m/d', strtotime('+7 day')), true),
                'URL 링크' => new SupportColumnText('note', '', '구매 사이트 링크 / 비고'),
                '파일첨부' => new SupportColumnFile('file'),
                '보유여부' => (new SupportColumnCategory('is_exist', ['재고', '신규구매']))->isVisibleIf($is_human_manage_team),
                '라벨번호' => (new SupportColumnText('label'))->isVisibleIf($is_human_manage_team),
            ],
            self::TYPE_GIFT_CARD_PURCHASE => [
                '일련번호' => new SupportColumnReadonly('uuid'),
                '일련번호2' => new SupportColumnReadonly('id'),
                '요청일' => new SupportColumnReadonly('reg_date'),
                '요청자' => new SupportColumnRegisterUser('uid'),
                '귀속부서' => new SupportColumnByValueCallback('team', $get_team_by_uid),
                '재무팀 처리' => new SupportColumnComplete('is_approved_by_cashflow', $is_cash_flow_team),
                '재무팀 처리자' => new SupportColumnCompleteUser('approved_by_cashflow_uid', 'is_approved_by_cashflow'),
                '재무팀 처리시각' => new SupportColumnCompleteDatetime('approved_by_cashflow_datetime', 'is_approved_by_cashflow'),
                '입금상태' => (new SupportColumnCategory('is_deposited', ['N', 'Y']))
                    ->readonly()
                    ->addEditableUserPred($is_cash_flow_team)
                    ->defaultValue('N'),
                '인사팀 처리' => new SupportColumnComplete('is_approved_by_hr', $is_human_manage_team),
                '인사팀 처리자' => new SupportColumnCompleteUser('approved_by_hr_uid', 'is_approved_by_hr'),
                '인사팀 처리시각' => new SupportColumnCompleteDatetime('approved_by_hr_datetime', 'is_approved_by_hr'),
                '권종' => (new SupportColumnCategory('giftcard_category', ['10,000', '50,000'], [9500, 46500]))->defaultValue('10,000'),
                '신청매수' => (new SupportColumnMoney('req_count'))->defaultValue('1'),
                '신청금액' => (new SupportColumnSum('req_sum', ['giftcard_category', 'req_count']))->readonly(),
                '입금자명' => new SupportColumnText('deposit_name', '', ''),
                '입금예정일시(24시간 내)' => new SupportColumnDate('deposit_date', date('Y/m/d H:i', strtotime('+0 day')), true),
                '사용용도' => new SupportColumnText('purpose', ''),
                '봉투수량' => (new SupportColumnMoney('num_envelops'))->defaultValue('1'),
            ],
        ];
    }
}
