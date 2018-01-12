import React from 'react';
import PropTypes from 'prop-types';
import { Button, Col, ControlLabel, Form, FormControl, FormGroup, Modal } from 'react-bootstrap';
import OverlayBinder from '../OverlayBinder';

class RoomEditModal extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      name: this.props.name,
      isVisible: this.props.isVisible,
      showError: false,
    };

    this.handleChangeName = this.handleChangeName.bind(this);
    this.handleChangeIsVisible = this.handleChangeIsVisible.bind(this);
    this.handleEnter = this.handleEnter.bind(this);
    this.handleClose = this.handleClose.bind(this);
  }

  showError() {
    this.setState({ showError: true });

    setTimeout(() => {
      this.setState({ showError: false });
    }, 1500);
  }

  handleEnter() {
    const { name, isVisible } = this.props;
    this.setState({ name, isVisible });
  }

  handleClose(isSaved) {
    if (isSaved) {
      if (!this.state.name) {
        this.showError();
        return;
      }

      this.props.onClose({
        name: this.state.name,
        isVisible: this.state.isVisible,
      });
    } else {
      this.props.onClose();
    }
  }

  handleChangeName(e) {
    this.setState({ name: e.target.value });
  }

  handleChangeIsVisible(e) {
    this.setState({ isVisible: e.target.value === '1' ? 1 : 0 });
  }

  render() {
    const { title, show } = this.props;

    return (
      <Modal
        autoFocus
        keyboard
        dialogClassName="edit-modal"
        show={show}
        onEnter={this.handleEnter}
        onHide={() => this.handleClose(false)}
      >
        <Modal.Header closeButton>
          <Modal.Title>{title}</Modal.Title>
        </Modal.Header>

        <Modal.Body>
          <Form horizontal>
            <FormGroup>
              <Col componentClass={ControlLabel} sm={3}>회의실 이름</Col>
              <Col sm={8}>
                <OverlayBinder
                  id="overlay-edit"
                  placement="top"
                  width={160}
                  show={this.state.showError}
                  text="입력이 올바르지 않습니다."
                >
                  <FormControl
                    type="text"
                    value={this.state.name}
                    placeholder="회의실 이름을 적어주세요."
                    onChange={this.handleChangeName}
                  />
                </OverlayBinder>
              </Col>
            </FormGroup>
            <FormGroup>
              <Col componentClass={ControlLabel} sm={3}>노출/숨김</Col>
              <Col sm={8}>
                <FormControl
                  componentClass="select"
                  placeholder={1}
                  value={this.state.isVisible}
                  onChange={this.handleChangeIsVisible}
                >
                  <option value={1}>노출</option>
                  <option value={0}>숨김</option>
                </FormControl>
              </Col>
            </FormGroup>
          </Form>
        </Modal.Body>

        <Modal.Footer>
          <Button onClick={() => this.handleClose(false)}>취소</Button>
          <Button bsStyle="primary" onClick={() => this.handleClose(true)}>확인</Button>
        </Modal.Footer>
      </Modal>
    );
  }
}

const doNothing = () => {};

RoomEditModal.defaultProps = {
  show: false,
  name: '',
  isVisible: 1,
  onClose: doNothing,
};

RoomEditModal.propTypes = {
  title: PropTypes.string.isRequired,
  show: PropTypes.bool,
  name: PropTypes.string,
  isVisible: PropTypes.number,
  onClose: PropTypes.func,
};

export default RoomEditModal;
