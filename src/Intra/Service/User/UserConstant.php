<?php

namespace Intra\Service\User;

class UserConstant
{
    // Team
    const TEAM_CEO = '공통 / C-lv / CEO';
    const TEAM_CDO = '공통 / C-lv / CDO';
    const TEAM_CFO = '공통 / C-lv / CFO';
    const TEAM_CTO = '공통 / C-lv / CTO';

    const TEAM_BI = '공통 / 경영지원그룹 / 사업분석팀';
    const TEAM_HUMAN_MANAGE = '공통 / 경영지원그룹 / 인사팀';
    const TEAM_CASH_FLOW = '공통 / 경영지원그룹 / 재무팀';

    const TEAM_DATA = '리디북스 / 개발센터 / 데이터팀';
    const TEAM_VIEWER = '리디북스 / 개발센터 / 뷰어팀';
    const TEAM_STORE = '리디북스 / 개발센터 / 스토어팀';
    const TEAM_PAPER = '리디북스 / 개발센터 / 페이퍼팀';
    const TEAM_PLATFORM = '리디북스 / 개발센터 / 플랫폼팀';

    const TEAM_GROWTH = '리디북스 / 사업그룹 / Growth팀';
    const TEAM_DEVICE = '리디북스 / 사업그룹 / 디바이스팀';
    const TEAM_DESIGN = '리디북스 / 사업그룹 / 디자인팀';
    const TEAM_CM_3 = '리디북스 / 사업그룹 / 로맨스/만화/BL팀';
    const TEAM_STORE_OP = '리디북스 / 사업그룹 / 운영지원팀';
    const TEAM_CM_1 = '리디북스 / 사업그룹 / 일반도서팀';
    const TEAM_CM_2 = '리디북스 / 사업그룹 / 판타지팀';

    const TEAM_AS = '리디북스 / 사업지원그룹 / AS/물류팀';
    const TEAM_CCPQ = '리디북스 / 사업지원그룹 / CC/PQ팀';
    const TEAM_PCC = '리디북스 / 사업지원그룹 / PCC팀';

    const TEAM_STORY_OPERATION = '리디연재 / 콘텐츠그룹 / 콘텐츠그룹';
    const TEAM_STORY_DEVELOP = '리디연재 / 서비스개발그룹 / 서비스개발그룹';

    // Team Detail
    const TEAM_DETAIL_HUMAN_MANAGE = '인사팀';

    public static $jeditable_key_list = [
        'team' => [
            self::TEAM_CEO,
            self::TEAM_CDO,
            self::TEAM_CFO,
            self::TEAM_CTO,
            self::TEAM_BI,
            self::TEAM_HUMAN_MANAGE,
            self::TEAM_CASH_FLOW,
            self::TEAM_DATA,
            self::TEAM_VIEWER,
            self::TEAM_STORE,
            self::TEAM_PAPER,
            self::TEAM_PLATFORM,
            self::TEAM_GROWTH,
            self::TEAM_DEVICE,
            self::TEAM_DESIGN,
            self::TEAM_CM_3,
            self::TEAM_STORE_OP,
            self::TEAM_CM_1,
            self::TEAM_CM_2,
            self::TEAM_AS,
            self::TEAM_CCPQ,
            self::TEAM_PCC,
            self::TEAM_STORY_OPERATION,
            self::TEAM_STORY_DEVELOP,
        ],
    ];
}
