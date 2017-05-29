<?php

namespace Intra\Service\User;

class UserConstant
{
    // Team
    const TEAM_CEO = '공통 / C-lv / CEO';
    const TEAM_CDO = '공통 / C-lv / CDO';
    const TEAM_CFO = '공통 / C-lv / CFO';
    const TEAM_CTO = '공통 / C-lv / CTO';

    const TEAM_PM = '공통 / PM / PM';
    const TEAM_BI = '공통 / PM / 사업분석팀';
    const TEAM_GROWTH = '공통 / PM / Growth팀';
    const TEAM_PM_BI_GROWTH = '공통 / PM|사업분석팀|Growth팀';

    const TEAM_HUMAN_MANAGE = '공통 / 경영지원그룹 / CO팀';
    const TEAM_CASH_FLOW = '공통 / 경영지원그룹 / 재무팀';

    const TEAM_DEV = '개발센터 / 개발센터';
    const TEAM_PERFORMANCE = '개발센터 / 퍼포먼스팀';
    const TEAM_PLATFORM = '개발센터 / 플랫폼팀';
    const TEAM_VIEWER = '개발센터 / 뷰어팀';
    const TEAM_STORY_DEVELOP = '개발센터 / 스토리개발팀';
    const TEAM_STORE = '개발센터 / 스토어팀';
    const TEAM_DATA = '개발센터 / 데이터팀';
    const TEAM_PAPER = '개발센터 / 페이퍼팀';

    const TEAM_DESIGN_CONTENTS1 = '디자인센터 / 콘텐츠디자인1팀';
    const TEAM_DESIGN_CONTENTS2 = '디자인센터 / 콘텐츠디자인2팀';
    const TEAM_DESIGN_BRAND = '디자인센터 / 브랜드디자인팀';

    const TEAM_NORMAL = '사업그룹 / 일반사업부';
    const TEAM_GENRE_BL = '사업그룹 / 로맨스/BL사업부';
    const TEAM_GENRE_FANTASY = '사업그룹 / 판타지사업부';
    const TEAM_CM = '사업그룹 / 만화사업부';
    const TEAM_DEVICE = '사업그룹 / 디바이스사업부';
    const TEAM_STUDIO_D = '사업그룹 / 스튜디오D';

    const TEAM_STORE_OP = '사업지원그룹 / 운영지원팀';
    const TEAM_CCPQ = '사업지원그룹 / CC/PQ팀';
    const TEAM_AS = '사업지원그룹 / AS/물류팀';
    const TEAM_CONTENTS_DB = '사업지원그룹 / 콘텐츠DB팀';
    const TEAM_PCC = '사업지원그룹 / PCC팀';
    const TEAM_STORY_OPERATION = '사업지원그룹 / 스토리지원팀';
    const TEAM_CCPQPCCAS = '사업지원그룹 / CC/PQ팀|PCC팀|AS/물류팀';

    public static $jeditable_key_list = [
        'team' => [
            self::TEAM_CEO,
            self::TEAM_CDO,
            self::TEAM_CFO,
            self::TEAM_CTO,
            self::TEAM_PM,
            self::TEAM_BI,
            self::TEAM_GROWTH,
            self::TEAM_PM_BI_GROWTH,
            self::TEAM_HUMAN_MANAGE,
            self::TEAM_CASH_FLOW,
            self::TEAM_DEV,
            self::TEAM_PERFORMANCE,
            self::TEAM_PLATFORM,
            self::TEAM_VIEWER,
            self::TEAM_STORY_DEVELOP,
            self::TEAM_STORE,
            self::TEAM_DATA,
            self::TEAM_PAPER,
            self::TEAM_DESIGN_CONTENTS1,
            self::TEAM_DESIGN_CONTENTS2,
            self::TEAM_DESIGN_BRAND,
            self::TEAM_NORMAL,
            self::TEAM_GENRE_BL,
            self::TEAM_GENRE_FANTASY,
            self::TEAM_CM,
            self::TEAM_DEVICE,
            self::TEAM_STUDIO_D,
            self::TEAM_STORE_OP,
            self::TEAM_CCPQ,
            self::TEAM_AS,
            self::TEAM_CONTENTS_DB,
            self::TEAM_PCC,
            self::TEAM_STORY_OPERATION,
            self::TEAM_CCPQPCCAS,
        ],
    ];
}
