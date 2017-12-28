import moment from 'moment';
import React from 'react';
import axios from 'axios';
import { Button, Col, Grid, Table, Row } from 'react-bootstrap';
import EventGroupEditModal from './EventGroupEditModal';

const DAY_MAP = ['일', '월', '화', '수', '목', '금', '토'];
const daysOfWeekStrToArray = (indexes) => {
  if (indexes === '' || indexes === null) {
    return DAY_MAP.join(',');
  }

  return indexes.split(',').map(day => DAY_MAP[day]).join(',');
};

class EventGroupConfig extends React.Component {
  constructor() {
    super();

    this.state = {
      showModal: false,
      editing: null,
      eventGroups: [],
      loading: true,
      loadingForEach: {},
      users: [],
      rooms: [],
    };

    this.handleCreateModalOpen = this.handleCreateModalOpen.bind(this);
    this.handleEditModalOpen = this.handleEditModalOpen.bind(this);
    this.handleModalClose = this.handleModalClose.bind(this);
    this.handleEdit = this.handleEdit.bind(this);
    this.handleDelete = this.handleDelete.bind(this);
    this.handleAdd = this.handleAdd.bind(this);
  }

  async componentDidMount() {
    const res = await Promise.all([
      await axios('/admin/event_group'),
      await axios('/rooms/section'),
      await axios('/users/list'),
    ]);

    const [
      { data: eventGroups },
      { data: rooms },
      { data: users },
    ] = res;

    this.setState({
      loading: false,
      eventGroups: eventGroups.map(eventGroup => this.convertEventGroupFromServerData(eventGroup)),
      rooms,
      users,
    });
  }

  getUserName(uid) {
    return this.state.users.find(user => user.uid === uid).name;
  }

  getRoomName(roomId) {
    return this.state.rooms.find(room => room.key === roomId).name;
  }

  convertEventGroupFromServerData(serverData) {
    return {
      id: serverData.id,
      uid: serverData.uid,
      roomId: serverData.room_id,
      fromDate: moment(serverData.from_date).format('YYYY-MM-DD'),
      toDate: moment(serverData.to_date).format('YYYY-MM-DD'),
      daysOfWeek: serverData.days_of_week,
      fromTime: moment(serverData.from_time, 'HH:mm:ss').format('HH:mm'),
      toTime: moment(serverData.to_time, 'HH:mm:ss').format('HH:mm'),
      desc: serverData.desc,
    };
  }

  convertServerDataFromEventGroup(eventGroup) {
    return {
      id: eventGroup.id,
      uid: eventGroup.uid,
      room_id: eventGroup.roomId,
      from_date: eventGroup.fromDate,
      to_date: eventGroup.toDate,
      days_of_week: eventGroup.daysOfWeek,
      from_time: eventGroup.fromTime,
      to_time: eventGroup.toTime,
      desc: eventGroup.desc,
    };
  }

  handleCreateModalOpen() {
    this.setState({
      editing: null,
      showModal: true,
    });
  }

  handleEditModalOpen(id) {
    this.setState({
      editing: id,
      showModal: true,
    });
  }

  handleModalClose(edited) {
    if (edited) {
      if (this.state.editing) {
        this.handleEdit(this.state.editing, edited);
      } else {
        this.handleAdd(edited);
      }
    }

    this.setState({
      editing: null,
      showModal: false,
    });
  }

  async handleEdit(id, edited) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, { [id]: true }),
    });

    const res = await axios.post(`/admin/event_group/${id}`, {
      uid: edited.uid,
      room_id: edited.roomId,
      from_date: edited.fromDate,
      to_date: edited.toDate,
      days_of_week: edited.daysOfWeek,
      from_time: edited.fromTime,
      to_time: edited.toTime,
      desc: edited.desc,
    });

    this.setState({
      eventGroups: this.state.eventGroups.map(eventGroup => (
        eventGroup.id === id ? this.convertEventGroupFromServerData(res.data) : eventGroup
      )),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [id]: false,
      }),
    });
  }

  async handleDelete(id) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [id]: true,
      }),
    });

    await axios.delete(`/admin/event_group/${id}`);

    this.setState({
      eventGroups: this.state.eventGroups.filter(eventGroup => eventGroup.id !== id),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [id]: false,
      }),
    });
  }

  async handleAdd(eventGroup) {
    const res = await axios.post('/admin/event_group', this.convertServerDataFromEventGroup(eventGroup));

    this.setState({
      eventGroups: this.state.eventGroups.concat(this.convertEventGroupFromServerData(res.data)),
    });
  }

  renderModal() {
    if (this.state.editing === null) {
      return (
        <EventGroupEditModal
          show={this.state.showModal}
          title="예약 편집"
          rooms={this.state.rooms}
          users={this.state.users}
          onClose={this.handleModalClose}
        />
      );
    }

    const editingEvent = this.state.eventGroups.filter(eventGroup => eventGroup.id === this.state.editing)[0];

    return (
      <EventGroupEditModal
        {...editingEvent}
        title="예약 편집"
        show={this.state.showModal}
        rooms={this.state.rooms}
        users={this.state.users}
        onClose={this.handleModalClose}
      />
    );
  }

  renderEventRows() {
    if (this.state.loading) {
      return <tr><td colSpan={7}>불러오는 중..</td></tr>;
    }

    return this.state.eventGroups.map((eventGroup) => {
      if (this.state.loadingForEach[eventGroup.id]) {
        return <tr key={eventGroup.id}><td colSpan={7}>불러오는 중..</td></tr>;
      }

      return (
        <tr key={eventGroup.id}>
          <td>{eventGroup.id}</td>
          <td>{this.getUserName(eventGroup.uid)}</td>
          <td>{this.getRoomName(eventGroup.roomId)}</td>
          <td>{`${eventGroup.fromDate} ~ ${eventGroup.toDate}`}</td>
          <td>{daysOfWeekStrToArray(eventGroup.daysOfWeek)}</td>
          <td>{`${eventGroup.fromTime} ~ ${eventGroup.toTime}`}</td>
          <td>{eventGroup.desc}</td>
          <td>
            <Button onClick={() => this.handleEditModalOpen(eventGroup.id)}>변경</Button>
            <Button onClick={() => this.handleDelete(eventGroup.id)}>삭제</Button>
          </td>
        </tr>
      );
    });
  }

  render() {
    return (
      <div>
        { this.renderModal() }
        <Grid>
          <Row>
            <Col>
              <h2>회의실 정기 예약 설정</h2>
            </Col>
          </Row>
          <Row>
            <Col>
              <Table condensed hover striped responsive>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>예약자</th>
                    <th>회의실</th>
                    <th>예약기간</th>
                    <th>요일</th>
                    <th>예약시간</th>
                    <th>내용</th>
                    <th />
                  </tr>
                </thead>
                <tbody>
                  { this.renderEventRows() }
                </tbody>
              </Table>
            </Col>
          </Row>
          <Row>
            <Button onClick={this.handleCreateModalOpen}>추가</Button>
          </Row>
        </Grid>
      </div>
    );
  }
}

export default EventGroupConfig;
