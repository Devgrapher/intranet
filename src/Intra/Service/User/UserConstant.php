<?php

namespace Intra\Service\User;

class UserConstant
{
    // Team
    const TEAM_CEO = '공통 / C-lv / CEO';
    const TEAM_CDO = '공통 / C-lv / CDO';
    const TEAM_CFO = '공통 / C-lv / CFO';
    const TEAM_CTO = '공통 / C-lv / CTO';

    const TEAM_PM = '공통 / PM';

    const TEAM_HUMAN_MANAGE = '공통 / 경영지원그룹 / CO팀';
    const TEAM_BI = '공통 / 경영지원그룹 / 사업분석팀';
    const TEAM_CASH_FLOW = '공통 / 경영지원그룹 / 재무팀';

    const TEAM_HUMAN_PLANNING = '공통 / 인사기획팀';
    const TEAM_PRODUCT_PLANNING = '공통 / 상품기획팀';

    const TEAM_DEV = '리디북스 / 개발센터 / 개발센터';
    const TEAM_DATA = '리디북스 / 개발센터 / 데이터팀';
    const TEAM_VIEWER = '리디북스 / 개발센터 / 뷰어팀';
    const TEAM_STORE = '리디북스 / 개발센터 / 스토어팀';
    const TEAM_PAPER = '리디북스 / 개발센터 / 페이퍼팀';
    const TEAM_PLATFORM = '리디북스 / 개발센터 / 플랫폼팀';
    const TEAM_PERFORMANCE = '리디북스 / 개발센터 / 퍼포먼스팀';

    const TEAM_DESIGN = '리디북스 / 사업그룹 / 디자인팀';
    const TEAM_CM = '리디북스 / 사업그룹 / 만화사업부';
    const TEAM_CM_1 = '리디북스 / 사업그룹 / 만화사업부 / 만화팀';
    const TEAM_STORE_OP = '리디북스 / 사업그룹 / 운영지원팀';
    const TEAM_NORMAL = '리디북스 / 사업그룹 / 일반사업부';
    const TEAM_DEVICE = '리디북스 / 사업그룹 / 일반사업부 / 디바이스팀';
    const TEAM_GROWTH = '리디북스 / 사업그룹 / 일반사업부 / 일반Growth팀';
    const TEAM_NORMAL_BOOK = '리디북스 / 사업그룹 / 일반사업부 / 일반도서팀';
    const TEAM_GENRE = '리디북스 / 사업그룹 / 장르사업부';
    const TEAM_GENRE_BL = '리디북스 / 사업그룹 / 장르사업부 / 로맨스/BL팀';
    const TEAM_GENRE_FANTASY = '리디북스 / 사업그룹 / 장르사업부 / 판타지팀';

    const TEAM_SUPPORT = '리디북스 / 사업지원그룹';
    const TEAM_AS = '리디북스 / 사업지원그룹 / AS/물류팀';
    const TEAM_CCPQ = '리디북스 / 사업지원그룹 / CC/PQ팀';
    const TEAM_PCC = '리디북스 / 사업지원그룹 / PCC팀';

    const TEAM_STORY_OPERATION = '리디스토리 / 운영팀';
    const TEAM_STORY_DEVELOP = '리디스토리 / 개발팀';

    // Team Detail
    const TEAM_DETAIL_HUMAN_MANAGE = '인사팀';

    public static $jeditable_key_list = [
        'team' => [
            self::TEAM_CEO,
            self::TEAM_CDO,
            self::TEAM_CFO,
            self::TEAM_CTO,
            self::TEAM_PM,
            self::TEAM_HUMAN_MANAGE,
            self::TEAM_BI,
            self::TEAM_CASH_FLOW,
            self::TEAM_HUMAN_PLANNING,
            self::TEAM_PRODUCT_PLANNING,
            self::TEAM_DEV,
            self::TEAM_DATA,
            self::TEAM_VIEWER,
            self::TEAM_STORE,
            self::TEAM_PAPER,
            self::TEAM_PLATFORM,
            self::TEAM_PERFORMANCE,
            self::TEAM_DESIGN,
            self::TEAM_CM,
            self::TEAM_CM_1,
            self::TEAM_STORE_OP,
            self::TEAM_NORMAL,
            self::TEAM_DEVICE,
            self::TEAM_GROWTH,
            self::TEAM_NORMAL_BOOK,
            self::TEAM_GENRE,
            self::TEAM_GENRE_BL,
            self::TEAM_GENRE_FANTASY,
            self::TEAM_SUPPORT,
            self::TEAM_AS,
            self::TEAM_CCPQ,
            self::TEAM_PCC,
            self::TEAM_STORY_OPERATION,
            self::TEAM_STORY_DEVELOP,
        ],
    ];
}
