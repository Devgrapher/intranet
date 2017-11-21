import React from 'react';
import PropTypes from 'prop-types';
import { Col, Form, FormGroup, FormControl, ControlLabel, Button } from 'react-bootstrap';
import Select from 'react-select';
import 'react-select/dist/react-select.css';

const HolidayAdjustCreator = (props) => {
  const yearList = [2016, 2017, 2018, 2019, 2020, 2021];
  const {
    disabled, loadManagerList, managerUid, adjustYear, diff, reason,
    onCreate, onYearChange, onManagerChange, onDiffChange, onReasonChange,
  } = props;

  return (
    <Form horizontal>
      <FormGroup>
        <Col xs={2}>
          <ControlLabel>적용년도</ControlLabel>
          <FormControl
            componentClass="select"
            value={adjustYear}
            disabled={disabled}
            onChange={e => onYearChange(e.target.value)}
          >
            { yearList.map(year => <option key={year} value={year}>{year}</option>) }
          </FormControl>
        </Col>
        <Col xs={2}>
          <ControlLabel>결제자</ControlLabel>
          <Select.Async
            disabled={disabled}
            loadOptions={loadManagerList}
            value={managerUid}
            placeholder="직원 선택"
            onChange={onManagerChange}
          />
        </Col>
        <Col xs={2}>
          <ControlLabel>변동(일)</ControlLabel>
          <FormControl
            type="text"
            value={diff}
            disabled
            placeholder="(+/-)숫자"
            onChange={e => onDiffChange(e.target.value)}
          />
        </Col>
      </FormGroup>

      <FormGroup>
        <Col xs={10}>
          <ControlLabel>사유</ControlLabel>
          <FormControl
            type="text"
            value={reason}
            disabled
            placeholder="사유를 적어주세요."
            onChange={e => onReasonChange(e.target.value)}
          />
        </Col>
        <Col xs={2}>
          <Button disabled={disabled} onClick={onCreate}>추가</Button>
        </Col>
      </FormGroup>
    </Form>
  );
};

const doNothing = () => {};

HolidayAdjustCreator.defaultProps = {
  disabled: false,
  reason: '',
  managerUid: undefined,
  onYearChange: doNothing,
  onManagerChange: doNothing,
  onDiffChange: doNothing,
  onReasonChange: doNothing,
  onCreate: doNothing,
};

HolidayAdjustCreator.propTypes = {
  adjustYear: PropTypes.number.isRequired,
  diff: PropTypes.string.isRequired,
  disabled: PropTypes.bool,
  loadManagerList: PropTypes.func.isRequired,
  managerUid: PropTypes.number,
  reason: PropTypes.string,
  onYearChange: PropTypes.func,
  onManagerChange: PropTypes.func,
  onDiffChange: PropTypes.func,
  onReasonChange: PropTypes.func,
  onCreate: PropTypes.func,
};

export default HolidayAdjustCreator;
