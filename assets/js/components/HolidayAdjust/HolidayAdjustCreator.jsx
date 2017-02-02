import React from 'react';
import { Col, Form, FormGroup, FormControl, ControlLabel, Button } from 'react-bootstrap';
import Select from 'react-select';

class HolidayAdjustCreator extends React.Component {
  render() {
    const yearList = [2016, 2017, 2018, 2019, 2020, 2021,];
    const {
      disabled, loadManagerList, managerUid, diffYear, diff, reason,
      onCreate, onYearChange, onManagerChange, onDiffChange, onReasonChange
    } = this.props;

    return (
      <Form horizontal>
        <FormGroup>
          <Col xs={2}>
            <ControlLabel>적용년도</ControlLabel>
            <FormControl
              componentClass="select" value={diffYear} disabled={disabled}
              onChange={e => onYearChange(e.target.value)}
            >
              {
                yearList.map((year, i) => (
                  <option key={i} value={year}>{year}</option>
                ))
              }
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
              type="text" value={diff} disabled={disabled} placeholder="(+/-)숫자"
              onChange={e => onDiffChange(e.target.value)}
            />
          </Col>
        </FormGroup>

        <FormGroup>
          <Col xs={10}>
            <ControlLabel>사유</ControlLabel>
            <FormControl
              type="text" value={reason} disabled={disabled} placeholder="사유를 적어주세요."
              onChange={e => onReasonChange(e.target.value)}
            />
          </Col>
          <Col xs={2}>
            <Button disabled={disabled} onClick={onCreate}>추가</Button>
          </Col>
        </FormGroup>
      </Form>
    );
  }
}

HolidayAdjustCreator.propTypes = {
  disabled: React.PropTypes.bool,
  loadManagerList: React.PropTypes.func,
  diffYear: React.PropTypes.number,
  managerUid: React.PropTypes.number,
  diff: React.PropTypes.string,
  reason: React.PropTypes.string,
  onYearChange: React.PropTypes.func,
  onManagerChange: React.PropTypes.func,
  onDiffChange: React.PropTypes.func,
  onReasonChange: React.PropTypes.func,
  onCreate: React.PropTypes.func,
};

export default HolidayAdjustCreator;
