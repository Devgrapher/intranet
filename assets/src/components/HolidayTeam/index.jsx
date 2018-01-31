import React from 'react';
import qs from 'query-string';
import _ from 'lodash';
import holidayApi from '../../api/holiday';

export default class HolidayTeam extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  async componentDidMount() {
    const { team, year } = qs.parse(window.location.search);
    const data = await holidayApi.getByTeam(team, year);
    this.setState({ data });
  }

  renderProgress() {
    return (
      <div className="progress">
        <div
          className="progress-bar progress-bar-striped active"
          role="progressbar"
          aria-valuenow="100"
          aria-valuemin="0"
          aria-valuemax="100"
          style={{ width: '100%' }}
        >
          로딩중..
        </div>
      </div>
    );
  }

  renderHeader(teamName, year) {
    return (
      <div className="page-header">
        <h1>팀 휴가현황 <small>{teamName}</small></h1>
        <ul className="breadcrumb">
          <li><a href={`/holidays/?team=${teamName}&year=${year - 1}`}>{year - 1}</a></li>
          <li className="active">{year}</li>
          <li><a href={`/holidays/?team=${teamName}&year=${year + 1}`}>{year + 1}</a></li>
        </ul>
      </div>
    );
  }

  renderHolidays(holidays) {
    return (
      <table className="table table-striped table-hover">
        <caption><h2>사용내역</h2></caption>

        <thead>
          <tr>
            <th>신청날짜</th>
            <th>사원번호</th>
            <th>신청자</th>
            <th>결재자</th>
            <th>종류</th>
            <th>사용날짜</th>
            <th>소모연차</th>
            <th>업무인수인계자</th>
            <th>비상시연락처</th>
            <th>비고</th>
          </tr>
        </thead>

        <tbody>
          {_.map(holidays, value => (
            <tr key={value.holidayid}>
              <td>{value.request_date}</td>
              <td>{value.personcode}</td>
              <td>{value.uid_name}</td>
              <td>{value.manager_uid_name}</td>
              <td>{value.type}</td>
              <td>{value.date}</td>
              <td>{value.cost}</td>
              <td>{value.keeper_uid_name}</td>
              <td>{value.phone_emergency}</td>
              <td>{value.memo}</td>
            </tr>
          ))}
        </tbody>
      </table>
    );
  }

  renderSummaries(year, summaries) {
    return (
      <table className="table table-striped table-hover">
        <caption><h2>잔여일수</h2></caption>

        <thead>
          <tr>
            <th>연도</th>
            <th>사원번호</th>
            <th>이름</th>
            <th>입사일자</th>
            <th>퇴사일자</th>
            <th>연차부여</th>
            <th>사용일수</th>
            <th>조정일수</th>
            <th>잔여일수</th>
          </tr>
        </thead>

        <tbody>
          {_.map(summaries, value => (
            <tr key={value.uid}>
              <td>{year}</td>
              <td>{value.personcode}</td>
              <td>{value.name}</td>
              <td>{value.on_date}</td>
              <td>{value.off_date}</td>
              <td>{value.full_cost}</td>
              <td>{value.used_cost}</td>
              <td>{value.mod_cost}</td>
              <td>{value.remain_cost}</td>
            </tr>
          ))}
        </tbody>
      </table>
    );
  }

  render() {
    const { data } = this.state;
    return (
      <div className="container">
        {!data ? (
          this.renderProgress()
        ) : (
          <React.Fragment>
            {this.renderHeader(data.team_name, data.year)}
            {this.renderHolidays(data.holidays)}
            {this.renderSummaries(data.year, data.summaries)}
          </React.Fragment>
        )}
      </div>
    );
  }
}
