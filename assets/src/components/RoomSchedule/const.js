const LOCALE = {
  date: {
    month_full: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
    month_short: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
    day_full: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
    day_short: ['일', '월', '화', '수', '목', '금', '토'],
  },
  labels: {
    dhx_cal_today_button: '오늘',
    day_tab: '일',
    week_tab: '주',
    month_tab: '월',
    new_event: '',
    icon_save: '저장',
    icon_cancel: '취소',
    icon_details: '상세',
    icon_edit: '편집',
    icon_delete: '삭제',
    confirm_closing: '변경 내역이 전부 사라집니다. 계속 진행할까요?',
    confirm_deleting: '이벤트는 삭제 후 되돌릴 수 없습니다. 계속 진행할까요?',
    section_description: '설명',
    section_time: '시간',
    full_day: '전일',
    message_ok: '확인',
    message_cancel: '취소',
  },
};

const CONFIG = {
  first_hour: 10,
  last_hour: 22,
  time_step: 15,
  hour_size_px: 88,
  default_date: '%Y/%m/%d %D',
  day_date: '%m/%d',
  xml_date: '%Y-%m-%d %H:%i',
  icons_select: ['icon_edit', 'icon_delete'],
  details_on_dblczlick: false,
  mark_now: true,
  multi_day: true,
  show_loading: true,
};

export {
  LOCALE,
  CONFIG,
};
