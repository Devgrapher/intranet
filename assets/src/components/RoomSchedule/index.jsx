import React from 'react';
import { Alert, Button, Modal } from 'react-bootstrap';
import DHtmlScheduler from './DHtmlScheduler';

class RoomSchedule extends React.Component {
  constructor() {
    super();

    this.state = {
      showModal: false,
    };

    this.handleModalClose = this.handleModalClose.bind(this);
    this.handleModalOpen = this.handleModalOpen.bind(this);
  }

  handleModalOpen() {
    this.setState({ showModal: true });
  }

  handleModalClose() {
    this.setState({ showModal: false });
  }

  render() {
    return (
      <div>
        <Alert bsStyle="info">
          <p>정기 미팅, 장기 미팅은 BWS팀 철민님 통해 예약가능합니다.</p>
          <p><b>회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.</b></p>
        </Alert>

        <Button bsStyle="info" onClick={this.handleModalOpen}>
          <span className="glyphicon glyphicon-question-sign" /> 사용법
        </Button>

        <Modal
          autoFocus
          keyboard
          show={this.state.showModal}
          onHide={this.handleModalClose}
        >
          <Modal.Header closeButton>
            <Modal.Title>회의실 예약 방법</Modal.Title>
          </Modal.Header>

          <Modal.Body>
            <div>
              <h5>회의실 예약 방법</h5>
              <ol>
                <li>인트라넷에 로그인 후, [회의실 예약] 메뉴를 눌러주세요.</li>
                <li>예약할 회의실을 선택한 후, 사용하실 시간을 드래그하여 지정해주세요.</li>
                <li>예약자와 예약내용을 기재하시고, ‘Enter’키를 눌러 저장해주세요.</li>
                <li>삭제 시 내가 저장한 시간을 클릭하고 왼쪽에 ‘휴지통’ 버튼을 눌러주세요. (수정 시에는 ‘펜’ 버튼을 눌러주세요.)</li>
              </ol>
            </div>
            <div>
              <h5>주의사항</h5>
              <ul>
                <li>회의실 예약 후, 미팅이 취소된 경우 다른 사람을 위해 예약 내역을 꼭 삭제해주세요.</li>
                <li>회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.</li>
              </ul>
            </div>
            <div>
              <ul>
                <li>모든 외부 손님의 미팅은 10층에서 진행해주세요. (예. 출판사 미팅, 면접 등)</li>
                <li>11층의 중요한 외부 손님(예. VIP, 중요한 기자 등) 방문의 경우, 방문자 및 방문내용에 대해 대표님 서면 승인이 있어야 출입이 가능하오니 유념하여 주세요.</li>
              </ul>
            </div>
            <p>
              * 관련 문의는 BWS팀에 해주시기 바랍니다.
            </p>
          </Modal.Body>

          <Modal.Footer>
            <Button bsStyle="primary" onClick={this.handleModalClose}>확인</Button>
          </Modal.Footer>
        </Modal>


        <DHtmlScheduler userName={window.userName} />
      </div>
    );
  }
}

export default RoomSchedule;
