<?php

namespace Intra\Service\User;

class UserConstant
{
	// Team
	const TEAM_PLATFORM = '단행본 / 개발센터 / 플랫폼팀';
	const TEAM_STORE = '단행본 / 개발센터 / 스토어팀';
	const TEAM_VIEWER = '단행본 / 개발센터 / 뷰어팀';
	const TEAM_DATA = '단행본 / 개발센터 / 데이터팀';
	const TEAM_PAPER = '단행본 / 개발센터 / 페이퍼팀';

	const TEAM_GROWTH = '단행본 / 콘텐츠그룹 / Growth팀';
	const TEAM_CM_1 = '단행본 / 콘텐츠그룹 / 일반도서팀';
	const TEAM_CM_2 = '단행본 / 콘텐츠그룹 / 판타지팀';
	const TEAM_CM_3 = '단행본 / 콘텐츠그룹 / 로맨스/만화/BL팀';

	const TEAM_STORE_OP = '단행본 / 운영지원그룹 / 운영지원팀';
	const TEAM_CCPQ = '단행본 / 운영지원그룹 / CC/PQ팀';
	const TEAM_DESIGN = '단행본 / 운영지원그룹 / 디자인팀';

	const TEAM_DEVICE = '단행본 / 디바이스팀 / 디바이스팀';

	const TEAM_BI = '공통 / 재무기획그룹 / 사업분석팀';
	const TEAM_CASH_FLOW = '공통 / 재무기획그룹 / 재무팀';

	const TEAM_STORY_OPERATION = '드래곤 / 콘텐츠그룹 / 운영팀';
	const TEAM_STORY_CONTACT = '드래곤 / 콘텐츠그룹 / 제휴팀';
	const TEAM_STORY_DEVELOP = '드래곤 / 프로덕트그룹 / 개발팀';
	const TEAM_STORY_PLANS = '드래곤 / 프로덕트그룹 / 기획팀';

	const TEAM_CEO = '공통 / CEO / CEO';
	const TEAM_CDO = '공통 / CEO / CDO';
	const TEAM_CTO = '공통 / CEO / CTO';
	const TEAM_HUMAN_MANAGE = '공통 / CEO / 인사팀';

	// Team Detail
	const TEAM_DETAIL_HUMAN_MANAGE = '인사팀';

	public static $jeditable_key_list = [
		'team' => [
			self::TEAM_STORE,
			self::TEAM_PLATFORM,
			self::TEAM_VIEWER,
			self::TEAM_DATA,
			self::TEAM_PAPER,
			self::TEAM_GROWTH,
			self::TEAM_CM_1,
			self::TEAM_CM_2,
			self::TEAM_CM_3,
			self::TEAM_STORE_OP,
			self::TEAM_CCPQ,
			self::TEAM_DESIGN,
			self::TEAM_DEVICE,
			self::TEAM_BI,
			self::TEAM_CASH_FLOW,
			self::TEAM_STORY_OPERATION,
			self::TEAM_STORY_CONTACT,
			self::TEAM_STORY_DEVELOP,
			self::TEAM_STORY_PLANS,
			self::TEAM_CEO,
			self::TEAM_CDO,
			self::TEAM_CTO,
			self::TEAM_HUMAN_MANAGE,
		],
	];
}
