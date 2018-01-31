import React from 'react';
import { Alert, Button, Col, Grid, Label, Table, Row } from 'react-bootstrap';
import { addRoomSection, updateRoomSection, deleteRoomSection } from '../../api/admin';
import { getSections } from '../../api/rooms';
import RoomEditModal from './RoomEditModal';
import AlertModal from './AlertModal';

class RoomConfig extends React.Component {
  constructor() {
    super();

    this.state = {
      loading: true,
      loadingForEach: {},
      rooms: [],
      deletingId: null,
      editingId: null,
      showEditModal: false,
      showAlertModal: false,
    };

    this.handleCreateModalOpen = this.handleCreateModalOpen.bind(this);
    this.handleEditModalOpen = this.handleEditModalOpen.bind(this);
    this.handleEditModalClose = this.handleEditModalClose.bind(this);
    this.handleEdit = this.handleEdit.bind(this);
    this.handleAdd = this.handleAdd.bind(this);
    this.handleDelete = this.handleDelete.bind(this);
    this.handleAlertModalOpen = this.handleAlertModalOpen.bind(this);
    this.handleAlertModalClose = this.handleAlertModalClose.bind(this);
  }

  async componentDidMount() {
    const rooms = await getSections('all');

    this.setState({
      loading: false,
      rooms: rooms.map(room => this.convertRoomFromServerData(room)),
    });
  }

  convertRoomFromServerData(serverData) {
    return {
      id: serverData.id,
      name: serverData.name,
      isVisible: serverData.is_visible,
    };
  }

  handleCreateModalOpen() {
    this.setState({
      editingId: null,
      showEditModal: true,
    });
  }

  handleEditModalOpen(roomId) {
    this.setState({
      editingId: roomId,
      showEditModal: true,
    });
  }

  async handleEditModalClose(edited) {
    if (edited) {
      const { editingId } = this.state;

      if (editingId) {
        this.handleEdit(editingId, edited);
      } else {
        this.handleAdd(edited);
      }
    }

    this.setState({
      editingId: null,
      showEditModal: false,
    });
  }

  async handleEdit(editingId, edited) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, { [editingId]: true }),
    });

    const result = await updateRoomSection(this.state.editingId, edited.type, edited.name, edited.isVisible);

    this.setState({
      rooms: this.state.rooms.map(room => (
        room.id === editingId ? this.convertRoomFromServerData(result) : room
      )),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [editingId]: false,
      }),
    });
  }

  async handleAdd(edited) {
    this.setState({ loading: true });
    const result = await addRoomSection(edited.type, edited.name, edited.isVisible);
    this.setState({
      loading: false,
      rooms: this.state.rooms.concat(this.convertRoomFromServerData(result)),
    });
  }

  async handleDelete(deletingId) {
    this.setState({
      loadingForEach: Object.assign({}, this.state.loadingForEach, { [deletingId]: true }),
    });

    await deleteRoomSection(deletingId);

    this.setState({
      rooms: this.state.rooms.filter(room => room.id !== deletingId),
      loadingForEach: Object.assign({}, this.state.loadingForEach, {
        [deletingId]: false,
      }),
    });
  }

  handleAlertModalOpen(roomId) {
    this.setState({
      deletingId: roomId,
      showAlertModal: true,
    });
  }

  async handleAlertModalClose(isConfirmed) {
    const { deletingId } = this.state;
    if (isConfirmed && deletingId !== null) {
      this.handleDelete(deletingId);
    }

    this.setState({
      deletingId: null,
      showAlertModal: false,
    });
  }

  renderRoomRows() {
    if (this.state.loading) {
      return <tr><td colSpan={4}>불러오는 중..</td></tr>;
    }

    return this.state.rooms.map((room) => {
      if (this.state.loadingForEach[room.id]) {
        return <tr key={room.id}><td colSpan={4}>불러오는 중..</td></tr>;
      }

      return (
        <tr key={room.id}>
          <td>{room.id}</td>
          <td>
            {room.name}
          </td>
          <td>
            <h4>
              {room.isVisible ? <Label bsStyle="success">노출</Label> : <Label bsStyle="default">숨김</Label>}
            </h4>
          </td>
          <td>
            <Button onClick={() => this.handleEditModalOpen(room.id)}>변경</Button>
            <Button onClick={() => this.handleAlertModalOpen(room.id)}>삭제</Button>
          </td>
        </tr>
      );
    });
  }

  renderEditModal() {
    if (this.state.editingId === null) {
      return (
        <RoomEditModal
          title="회의실 생성"
          show={this.state.showEditModal}
          onClose={this.handleEditModalClose}
        />
      );
    }

    const editingRoom = this.state.rooms.find(room => room.id === this.state.editingId);

    return (
      <RoomEditModal
        title="회의실 편집"
        show={this.state.showEditModal}
        onClose={this.handleEditModalClose}
        {...editingRoom}
      />
    );
  }

  render() {
    return (
      <div>
        { this.renderEditModal() }

        <AlertModal
          show={this.state.showAlertModal}
          onClose={this.handleAlertModalClose}
        />

        <Alert bsStyle="warning">
          <p><b>삭제된 회의실은 되돌릴 수 없습니다.</b></p>
          <ul>
            <li>이름을 바꾸고 싶다면 &#39;변경&#39; 기능을 이용해주세요.</li>
            <li>회의실 예약 메뉴에서 숨기려면 &#39;변경&#39; &gt; &#39;노출/숨김&#39; 에서 숨김을 선택해주세요.</li>
          </ul>
        </Alert>

        <Grid>
          <Row>
            <Col>
              <h2>회의실 설정</h2>
            </Col>
          </Row>
          <Row>
            <Col>
              <Table condensed hover striped responsive>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>이름</th>
                    <th>노출/숨김</th>
                    <th />
                  </tr>
                </thead>
                <tbody>
                  { this.renderRoomRows() }
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

export default RoomConfig;
