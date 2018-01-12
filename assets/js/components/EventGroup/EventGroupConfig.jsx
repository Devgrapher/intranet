import moment from 'moment';
import React from 'react';
import { Alert, Button, Col, Grid, Label, Table, Row } from 'react-bootstrap';
import { getEventGroups, addEventGroup, updateEventGroup, deleteEventGroup } from '../../api/admin';
import { getSections } from '../../api/rooms';
import { getUsers } from '../../api/users';
import EventGroupEditModal from './EventGroupEditModal';
import '../../../css/eventGroup.css';

const DAY_MAP = [
  { name: '일', style: 'label-default' },
  { name: '월', style: 'label-mon' },
  { name: '화', style: 'label-tue' },
  { name: '수', style: 'label-wen' },
  { name: '목', style: 'label-thu' },
  { name: '금', style: 'label-fri' },
  { name: '토', style: 'label-default' },
];

class EventGroupConfig extends React.Component {
  constructor() {
    super();

    this.state = {
      showModal: false,
      editingId: null,
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
      await getEventGroups(),
      await getSections(),
      await getUsers(),
    ]);

    const [eventGroups, sections, users] = res;

    this.setState({
      loading: false,
      eventGroups: eventGroups.map(eventGroup => this.convertEventGroupFromServerData(eventGroup)),
      rooms: sections,
      users,
    });
  }

  convertUserName(uid) {
    return this.state.users.find(user => user.uid === uid).name;
  }

  convertRoomName(roomId) {
    const room = this.state.rooms.find(_room => _room.id === roomId);
    if (!room) {
      return `알 수 없는 room (id=${roomId})`;
    }

    return (
      <div>
        {room.name}
        {' '}
        {room.is_visible === 1 ? <Label bsStyle="success">노출</Label> : <Label bsStyle="default">숨김</Label>}
      </div>
    );
  }

  convertDaysOfWeek(indexes) {
    let days;

    // If index is null value, it includes all days, excluding Saturday and Sunday.
    if (indexes === '' || indexes === null) {
      days = [1, 2, 3, 4, 5];
    } else {
      days = indexes.split(',');
    }

    return days.map(day => <Label key={day} className={DAY_MAP[day].style}>{DAY_MAP[day].name}</Label>);
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

  handleCreateModalOpen() {
    this.setState({
      editingId: null,
      showModal: true,
    });
  }

  handleEditModalOpen(id) {
    this.setState({
      editingId: id,
      showModal: true,
    });
  }

  handleModalClose(edited) {
    if (edited) {
      if (this.state.editingId) {
        this.handleEdit(this.state.editingId, edited);
      } else {
        this.handleAdd(edited);
      }
    }

    this.setState({
      editingId: null,
      showModal: false,
    });
  }

  async handleEdit(eventGroupId, edited) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, { [eventGroupId]: true }),
    });

    const result = await updateEventGroup(
      eventGroupId,
      edited.uid,
      edited.roomId,
      edited.fromDate,
      edited.toDate,
      edited.daysOfWeek,
      edited.fromTime,
      edited.toTime,
      edited.desc,
    );

    this.setState({
      eventGroups: this.state.eventGroups.map(eventGroup => (
        eventGroup.id === eventGroupId ? this.convertEventGroupFromServerData(result) : eventGroup
      )),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [eventGroupId]: false,
      }),
    });
  }

  async handleDelete(eventGroupId) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [eventGroupId]: true,
      }),
    });

    await deleteEventGroup(eventGroupId);

    this.setState({
      eventGroups: this.state.eventGroups.filter(eventGroup => eventGroup.id !== eventGroupId),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [eventGroupId]: false,
      }),
    });
  }

  async handleAdd(eventGroup) {
    this.setState({ loading: true });

    const result = await addEventGroup(
      eventGroup.uid,
      eventGroup.roomId,
      eventGroup.fromDate,
      eventGroup.toDate,
      eventGroup.daysOfWeek,
      eventGroup.fromTime,
      eventGroup.toTime,
      eventGroup.desc,
    );

    this.setState({
      loading: false,
      eventGroups: this.state.eventGroups.concat(this.convertEventGroupFromServerData(result)),
    });
  }

  renderModal() {
    const roomList = this.state.rooms.map(room => ({
      key: room.id,
      label: room.is_visible === 1 ? room.name : `[숨김] ${room.name}`,
    }));

    if (this.state.editingId === null) {
      return (
        <EventGroupEditModal
          title="예약 생성"
          show={this.state.showModal}
          rooms={roomList}
          users={this.state.users}
          onClose={this.handleModalClose}
        />
      );
    }

    const editingEvent = this.state.eventGroups.find(eventGroup => eventGroup.id === this.state.editingId);

    return (
      <EventGroupEditModal
        title="예약 편집"
        show={this.state.showModal}
        rooms={roomList}
        users={this.state.users}
        onClose={this.handleModalClose}
        {...editingEvent}
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
          <td>{this.convertUserName(eventGroup.uid)}</td>
          <td>{this.convertRoomName(eventGroup.roomId)}</td>
          <td>{`${eventGroup.fromDate} ~ ${eventGroup.toDate}`}</td>
          <td>{this.convertDaysOfWeek(eventGroup.daysOfWeek)}</td>
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

        <Alert bsStyle="warning">
          <p><b>정기 예약 생성 시, 이미 존재하는 예약들과 시간이 겹쳐서 생성될 수 있습니다.</b></p>
        </Alert>

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
