import React from 'react';
import PropTypes from 'prop-types';
import $ from 'jquery';
import { getSections, addEvent, updateEvent, deleteEvent } from '../../api/rooms';
import { LOCALE, CONFIG } from './const';
import '../../../lib/dhtmlxScheduler/dhtmlxscheduler';
import '../../../lib/dhtmlxScheduler/ext/dhtmlxscheduler_limit';
import '../../../lib/dhtmlxScheduler/ext/dhtmlxscheduler_units';
import '../../../lib/dhtmlxScheduler/dhtmlxscheduler.css';
import '../../../lib/dhtmlxScheduler/dhtmlxscheduler_flat.css';
import '../../../css/scheduler.css';

class DHtmlScheduler extends React.Component {
  constructor() {
    super();

    const { scheduler } = window;
    this.scheduler = scheduler;

    this.onEventAdded = this.onEventAdded.bind(this);
    this.onBeforeEdit = this.onBeforeEdit.bind(this);
    this.onEventChanged = this.onEventChanged.bind(this);
    this.onEventCreated = this.onEventCreated.bind(this);
    this.onBeforeEventDelete = this.onBeforeEventDelete.bind(this);
    this.renderEvent = this.renderEvent.bind(this);
  }

  componentDidMount() {
    $(async () => {
      const sections = await getSections();
      this.initScheduler(sections.filter(section => section.is_visible === 1));
    });
  }

  onBeforeEdit(eventId) {
    if (!eventId) {
      return true;
    }

    const event = this.scheduler.getEvent(eventId);
    if (!event || event.group) {
      return false;
    }

    this.editingDateFrom = new Date(event.start_date);
    this.editingDateTo = new Date(event.end_date);
    this.editingRoomId = event.room_id;
    return true;
  }

  async onEventChanged(eventId) {
    if (eventId) {
      const event = this.scheduler.getEvent(eventId);
      if (event.group) {
        return false;
      }
    }

    if (this.isDuplicatedEvent(eventId)) {
      alert('이미 예약된 시간입니다');
      this.resetEditingEvent(eventId);
      return false;
    }

    const event = this.scheduler.getEvent(eventId);

    const result = await updateEvent(
      eventId,
      event.text,
      this.convertToDateStr(event.start_date),
      this.convertToDateStr(event.end_date),
      event.room_id,
    );

    if (result !== 1) {
      alert(result);
      this.resetEditingEvent(eventId);
    }

    return true;
  }

  async onEventAdded(eventId) {
    if (this.isDuplicatedEvent(eventId)) {
      alert('이미 예약된 시간입니다');
      this.scheduler.deleteEvent(eventId);
      return false;
    }

    const event = this.scheduler.getEvent(eventId);
    try {
      const result = await addEvent(
        event.text,
        this.convertToDateStr(event.start_date),
        this.convertToDateStr(event.end_date),
        event.room_id,
      );

      const newEventId = parseInt(result, 10);
      if (Number.isNaN(newEventId) || newEventId === 0) {
        alert(result);
        this.scheduler.deleteEvent(eventId);
      } else {
        this.scheduler.changeEventId(eventId, newEventId);
      }
    } catch (e) {
      alert('이벤트 추가가 실패하였습니다');
      this.scheduler.deleteEvent(eventId);
      return false;
    }

    return true;
  }

  onEventCreated(eventId) {
    this.scheduler.getEvent(eventId).text = `[예약자] ${this.props.userName}\n[예약내용] `;
    this.scheduler.updateEvent(eventId);
    return true;
  }

  async onBeforeEventDelete(eventId, event) {
    if (event.group) {
      return false;
    }

    await deleteEvent(eventId);
    return true;
  }

  initScheduler(sections) {
    const { scheduler } = this;

    Object.assign(scheduler.config, CONFIG);
    scheduler.locale = LOCALE;
    scheduler.skin = 'flat';

    scheduler.attachEvent('onEventAdded', this.onEventAdded);
    scheduler.attachEvent('onBeforeDrag', this.onBeforeEdit);
    scheduler.attachEvent('onDblClick', this.onBeforeEdit);
    scheduler.attachEvent('onClick', this.onBeforeEdit);
    scheduler.attachEvent('onEventChanged', this.onEventChanged);
    scheduler.attachEvent('onEventCreated', this.onEventCreated);
    scheduler.attachEvent('onBeforeEventDelete', this.onBeforeEventDelete);
    scheduler.renderEvent = this.renderEvent;

    scheduler.templates.event_class = (start, end, event) => (event.group ? 'group' : '');
    scheduler.templates.event_text = (start, end, event) => (
      !event.group ?
        event.text :
        `${this.translateDaysOfWeek(event.days_of_week)} 정기예약<br>${event.text}`
    );

    scheduler.createUnitsView({
      name: 'meeting_rooms',
      property: 'room_id',
      list: sections.map(section => ({ key: section.id, label: section.name })),
      size: window.innerWidth < 980 ? 1 : undefined,
      step: window.innerWidth < 980 ? 1 : undefined,
    });

    scheduler.init('scheduler', null, 'meeting_rooms');
    scheduler.setLoadMode('day');
    scheduler.load(`/rooms/event?room_ids=${sections.map(section => section.id).join(',')}`, 'json');
  }

  resetEditingEvent(eventId) {
    const editing = this.scheduler.getEvent(eventId);
    editing.start_date = this.editingDateFrom;
    editing.end_date = this.editingDateTo;
    editing.room_id = this.editingRoomId;
  }

  isDuplicatedEvent(eventId) {
    const target = this.scheduler.getEvent(eventId);
    const duplicates = this.scheduler.getEvents(target.start_date, target.end_date)
      .filter(duplicated => duplicated.id !== parseInt(eventId, 10) && duplicated.room_id === target.room_id);

    return duplicates.length > 0;
  }

  translateDaysOfWeek(daysOfWeek) {
    const daysOfWeekMap = ['월', '화', '수', '목', '금'];
    let translated = daysOfWeek.split(',')
      .map(day => daysOfWeekMap[day - 1])
      .join(',');

    if (translated === '월,화,수,목,금' || translated.length === 0) {
      translated = '매일';
    } else {
      translated += '요일';
    }

    return translated;
  }

  convertToDateStr(date) {
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}`;
  }

  renderEvent(container, event) {
    if ((event.end_date - event.start_date) / 60000 > 15) {
      return false;
    }

    const containerWidth = container.style.width;
    const containerHeight = parseInt(container.style.height, 10) / 2;
    const eventStartDate = this.scheduler.templates.event_date(event.start_date);
    const eventEndDate = this.scheduler.templates.event_date(event.end_date);
    const eventText = this.scheduler.templates.event_text(event.start_date, event.end_date, event);

    let html = `<div class="dhx_event_move dhx_header" style="margin: 0px; padding: 0px; width: ${containerWidth};"></div>`;
    html += `<div class="dhx_event_move dhx_body" style="margin: 0px; padding: 0px; height: ${containerHeight};">`;
    html += `<span class="dhx_title">${eventStartDate} - ${eventEndDate}</span>`;
    html += `<span style="margin-left: 5px">${eventText}</span>`;
    html += '</div>';
    html += `<div class="dhx_event_resize dhx_footer" style="width: ${containerWidth}"></div>`;
    container.innerHTML = html;
    return true;
  }

  render() {
    return (
      <div id="scheduler" className="dhx_cal_container" style={{ width: '100%', height: '100%', overflow: 'visible' }}>
        <div className="dhx_cal_navline">
          <div className="dhx_cal_prev_button" />
          <div className="dhx_cal_next_button" />
          <div className="dhx_cal_today_button" />
          <div className="dhx_cal_date" />
        </div>

        <div className="dhx_cal_header" />
        <div className="dhx_cal_data" />
      </div>
    );
  }
}

DHtmlScheduler.propTypes = {
  userName: PropTypes.string.isRequired,
};

export default DHtmlScheduler;
