import React from 'react';
import PropTypes from 'prop-types';
import { Button, Modal } from 'react-bootstrap';

const AlertModal = ({ show, onClose }) => (
  <Modal
    autoFocus
    keyboard
    show={show}
    onHide={() => onClose(false)}
  >
    <Modal.Header closeButton>
      <Modal.Title>경고</Modal.Title>
    </Modal.Header>

    <Modal.Body>
      <h4>회의실을 삭제하면 해당 회의실 예약 정보도 같이 유실되며 되돌릴 수 없습니다.</h4>
      <p>(임시로 안보이게 하려면 삭제대신 숨김 상태로 변경해주세요.)</p>
      <h4>계속 진행하시겠습니까?</h4>
    </Modal.Body>

    <Modal.Footer>
      <Button onClick={() => onClose(false)}>취소</Button>
      <Button bsStyle="primary" onClick={() => onClose(true)}>확인</Button>
    </Modal.Footer>
  </Modal>
);

const doNothing = () => {};

AlertModal.defaultProps = {
  show: false,
  onClose: doNothing,
};

AlertModal.propTypes = {
  show: PropTypes.bool,
  onClose: PropTypes.func,
};

export default AlertModal;
