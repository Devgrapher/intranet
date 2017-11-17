import React from 'react';
import PropTypes from 'prop-types';
import { Table, Button } from 'react-bootstrap';

class HolidayAdjustTable extends React.Component {
  renderRows() {
    const {
      initial, loading, rows, onDelete,
    } = this.props;

    if (initial) {
      return <tr><td colSpan="7">직원을 선택해주세요.</td></tr>;
    }

    if (rows.length === 0) {
      return <tr><td colSpan="7">데이터가 없습니다.</td></tr>;
    }

    return rows.map((row, i) => {
      const createdAt = new Date(row.created_at);
      let month = createdAt.getMonth() + 1;
      month = month < 10 ? `0${month}` : month;
      let date = createdAt.getDate();
      date = date < 10 ? `0${date}` : date;
      return (
        <tr key={i}>
          <td>{row.diff_year}</td>
          <td>{`${createdAt.getFullYear()}-${month}-${date}`}</td>
          <td>{row.name}</td>
          <td>{row.managerName}</td>
          <td>{row.diff > 0 ? `+${row.diff}` : row.diff}</td>
          <td>{row.reason}</td>
          <td>
            <Button disabled={loading} onClick={() => onDelete(row.id)}>삭제</Button>
          </td>
        </tr>
      );
    });
  }

  render() {
    return (
      <Table striped bordered condensed hover>
        <thead>
          <tr>
            <th>적용년도</th>
            <th>생성일</th>
            <th>대상</th>
            <th>결제자</th>
            <th>변동</th>
            <th>사유</th>
            <th>-</th>
          </tr>
        </thead>
        <tbody>
          { this.renderRows() }
        </tbody>
      </Table>
    );
  }
}

const doNothing = () => {};

HolidayAdjustTable.defaultProps = {
  initial: true,
  loading: false,
  rows: [],
  onDelete: doNothing,
};

HolidayAdjustTable.propTypes = {
  initial: PropTypes.bool,
  loading: PropTypes.bool,
  rows: PropTypes.arrayOf(PropTypes.object),
  onDelete: PropTypes.func,
};

export default HolidayAdjustTable;
