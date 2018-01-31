import moment from 'moment';
import React from 'react';
import PropTypes from 'prop-types';
import DateTime from 'react-datetime';
import 'react-datetime/css/react-datetime.css';
import Select from 'react-select';
import 'react-select/dist/react-select.css';
import { Modal, Button, Checkbox, Col, ControlLabel, Form, FormControl, FormGroup } from 'react-bootstrap';
import OverlayBinder from '../OverlayBinder';

const DATE_FORMAT = 'YYYY-MM-DD';
const TIME_FORMAT = 'HH:mm';

class EventGroupEditModal extends React.Component {
  constructor(props) {
    super(props);

    this.daysOfWeekInputs = new Array(7).fill(undefined);

    this.state = {
      uid: {},
      roomId: {},
      fromDate: {},
      toDate: {},
      fromTime: {},
      toTime: {},
      desc: {},
    };

    this.handleChangeDateTime = this.handleChangeDateTime.bind(this);
    this.handleChangeSeletor = this.handleChangeSeletor.bind(this);
    this.handleChangeDesc = this.handleChangeDesc.bind(this);
    this.handleEnter = this.handleEnter.bind(this);
    this.handleClose = this.handleClose.bind(this);
  }

  getDaysOfWeek() {
    const daysOfWeekStr = this.daysOfWeekInputs
      .map((input, i) => ((input && input.checked) ? i : null))
      .filter(value => value !== null)
      .join(',');

    return daysOfWeekStr === '' ? '1,2,3,4,5' : daysOfWeekStr;
  }

  setDaysOfWeek(daysOfWeekStr) {
    daysOfWeekStr.split(',')
      .forEach((day) => {
        this.daysOfWeekInputs[day].checked = true;
      });
  }

  handleChangeDateTime(key, changed, format) {
    if (typeof changed === 'string') {
      return;
    }

    this.setState({
      [key]: Object.assign({}, this.state[key], {
        value: changed.format(format),
      }),
    });
  }

  handleChangeSeletor(key, changed) {
    this.setState({
      [key]: Object.assign({}, this.state[key], {
        value: changed ? changed.value : undefined,
      }),
    });
  }

  handleChangeDesc(event) {
    this.setState({
      desc: Object.assign({}, this.state.desc, {
        value: event.target.value,
      }),
    });
  }

  handleEnter() {
    const {
      uid, roomId, fromDate, toDate, daysOfWeek, fromTime, toTime, desc,
    } = this.props;

    this.setDaysOfWeek(daysOfWeek);

    this.setState({
      uid: { value: uid },
      roomId: { value: roomId },
      fromDate: { value: fromDate },
      toDate: { value: toDate },
      fromTime: { value: fromTime },
      toTime: { value: toTime },
      desc: { value: desc },
    });
  }

  handleClose(isSaved) {
    if (isSaved) {
      if (!this.validateOutput()) {
        return;
      }

      this.props.onClose({
        uid: this.state.uid.value,
        roomId: this.state.roomId.value,
        fromDate: this.state.fromDate.value,
        toDate: this.state.toDate.value,
        daysOfWeek: this.getDaysOfWeek(),
        fromTime: this.state.fromTime.value,
        toTime: this.state.toTime.value,
        desc: this.state.desc.value,
      });
    } else {
      this.props.onClose();
    }
  }

  showError(key, text) {
    this.setState({
      [key]: Object.assign({}, this.state[key], {
        showError: true,
        errorText: text,
      }),
    });

    setTimeout(() => {
      this.setState({
        [key]: Object.assign({}, this.state[key], {
          showError: false,
          errorText: text,
        }),
      });
    }, 1500);
  }

  validateOutput() {
    if (this.state.uid.value === undefined) {
      this.showError('uid', '직원을 선택해주세요.');
      return false;
    }

    if (this.state.roomId.value === undefined) {
      this.showError('roomId', '회의실을 선택해주세요.');
      return false;
    }

    const fromDate = moment(this.state.fromDate.value, DATE_FORMAT);
    if (!fromDate.isValid()) {
      this.showError('fromDate', '올바른 형식이 아닙니다.');
      return false;
    }

    const toDate = moment(this.state.toDate.value, DATE_FORMAT);
    if (!toDate.isValid()) {
      this.showError('toDate', '올바른 형식이 아닙니다.');
      return false;
    }

    if (fromDate.valueOf() >= toDate.valueOf()) {
      this.showError('fromDate', '예약기간 시작 날짜가 종료 날짜보다 앞서야 합니다.');
      return false;
    }

    const fromTime = moment(this.state.fromTime.value, TIME_FORMAT);
    if (!fromTime.isValid()) {
      this.showError('fromTime', '올바른 형식이 아닙니다.');
      return false;
    }

    const toTime = moment(this.state.toTime.value, TIME_FORMAT);
    if (!toTime.isValid()) {
      this.showError('toTime', '올바른 형식이 아닙니다.');
      return false;
    }

    if (fromTime.valueOf() >= toTime.valueOf()) {
      this.showError('fromTime', '예약시간 시작 시간이 종료 시간보다 앞서야 합니다');
      return false;
    }

    return true;
  }

  renderSelector(key, labelText, placeholder, options) {
    return (
      <FormGroup>
        <Col componentClass={ControlLabel} sm={3}>{labelText}</Col>
        <Col sm={4}>
          <OverlayBinder
            id={`overlay-${key}`}
            width={150}
            show={this.state[key].showError}
            text={this.state[key].errorText}
          >
            <Select
              value={this.state[key].value}
              placeholder={placeholder}
              backspaceToRemoveMessage=""
              options={options}
              onChange={changed => this.handleChangeSeletor(key, changed)}
            />
          </OverlayBinder>
        </Col>
      </FormGroup>
    );
  }

  renderDatePicker(key, labelText) {
    return (
      <FormGroup>
        <Col componentClass={ControlLabel} sm={3}>{labelText}</Col>
        <Col sm={4}>
          <OverlayBinder
            id={`overlay-${key}`}
            width={200}
            show={this.state[key].showError}
            text={this.state[key].errorText}
          >
            <DateTime
              closeOnSelect
              value={this.state[key].value}
              inputProps={{ placeholder: DATE_FORMAT }}
              dateFormat={DATE_FORMAT}
              timeFormat={false}
              onChange={changed => this.handleChangeDateTime(key, changed, DATE_FORMAT)}
            />
          </OverlayBinder>
        </Col>
      </FormGroup>
    );
  }

  renderTimePicker(key, labelText) {
    return (
      <FormGroup>
        <Col componentClass={ControlLabel} sm={3}>{labelText}</Col>
        <Col sm={4}>
          <OverlayBinder
            id={`overlay-${key}`}
            width={150}
            show={this.state[key].showError}
            text={this.state[key].errorText}
          >
            <DateTime
              defaultValue="10:00"
              value={this.state[key].value}
              inputProps={{ placeholder: TIME_FORMAT }}
              dateFormat={false}
              timeFormat={TIME_FORMAT}
              timeConstraints={{
                hours: { min: 10, max: 21 },
                minutes: { step: 15 },
              }}
              onChange={changed => this.handleChangeDateTime(key, changed, TIME_FORMAT)}
            />
          </OverlayBinder>
        </Col>
      </FormGroup>
    );
  }

  render() {
    const {
      show, title, rooms, users,
    } = this.props;

    return (
      <div>
        <Modal
          autoFocus
          keyboard
          show={show}
          onEnter={this.handleEnter}
          onHide={() => this.handleClose(false)}
        >
          <Modal.Header closeButton>
            <Modal.Title>{title}</Modal.Title>
          </Modal.Header>

          <Modal.Body>
            <Form horizontal>
              {
                this.renderSelector(
                  'uid', '예약자', '직원선택',
                  users.map(user => ({ label: user.name, value: user.uid })),
                )
              }
              {
                this.renderSelector(
                  'roomId', '회의실', '회의실 선택',
                  rooms.map(room => ({ label: room.label, value: room.key })),
                )
              }
              { this.renderDatePicker('fromDate', '예약기간 시작') }
              { this.renderDatePicker('toDate', '예약기간 종료') }
              <FormGroup>
                <Col componentClass={ControlLabel} sm={3}>요일 지정</Col>
                <Col sm={5}>
                  <Checkbox inputRef={(ref) => { this.daysOfWeekInputs[1] = ref; }} inline>월</Checkbox>
                  {' '}
                  <Checkbox inputRef={(ref) => { this.daysOfWeekInputs[2] = ref; }} inline>화</Checkbox>
                  {' '}
                  <Checkbox inputRef={(ref) => { this.daysOfWeekInputs[3] = ref; }} inline>수</Checkbox>
                  {' '}
                  <Checkbox inputRef={(ref) => { this.daysOfWeekInputs[4] = ref; }} inline>목</Checkbox>
                  {' '}
                  <Checkbox inputRef={(ref) => { this.daysOfWeekInputs[5] = ref; }} inline>금</Checkbox>
                </Col>
              </FormGroup>
              { this.renderTimePicker('fromTime', '예약시간 시작') }
              { this.renderTimePicker('toTime', '예약시간 종료') }
              <FormGroup>
                <Col componentClass={ControlLabel} sm={3}>내용</Col>
                <Col sm={8}>
                  <FormControl
                    type="text"
                    value={this.state.desc.value}
                    placeholder="내용을 적어주세요."
                    onChange={this.handleChangeDesc}
                  />
                </Col>
              </FormGroup>
            </Form>
          </Modal.Body>

          <Modal.Footer>
            <Button onClick={() => this.handleClose(false)}>취소</Button>
            <Button bsStyle="primary" onClick={() => this.handleClose(true)}>확인</Button>
          </Modal.Footer>
        </Modal>
      </div>
    );
  }
}

const doNothing = () => {};

EventGroupEditModal.defaultProps = {
  show: false,
  uid: undefined,
  roomId: undefined,
  fromDate: moment(new Date()).format(DATE_FORMAT),
  toDate: moment(new Date()).format(DATE_FORMAT),
  daysOfWeek: '1,2,3,4,5',
  fromTime: '10:30',
  toTime: '11:00',
  desc: '',
  onClose: doNothing,
};

EventGroupEditModal.propTypes = {
  title: PropTypes.string.isRequired,
  show: PropTypes.bool,
  uid: PropTypes.number,
  roomId: PropTypes.number,
  fromDate: PropTypes.string,
  toDate: PropTypes.string,
  daysOfWeek: PropTypes.string,
  fromTime: PropTypes.string,
  toTime: PropTypes.string,
  desc: PropTypes.string,
  rooms: PropTypes.arrayOf(PropTypes.object).isRequired,
  users: PropTypes.arrayOf(PropTypes.object).isRequired,
  onClose: PropTypes.func,
};

export default EventGroupEditModal;
