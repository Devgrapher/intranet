import React from 'react';
import { Grid, Col, Row } from 'react-bootstrap';
import Select from 'react-select';
import axios from 'axios';
import HolidayAdjustCreator from './HolidayAdjustCreator';
import HolidayAdjustTable from './HolidayAdjustTable';

class HolidayAdjust extends React.Component {
  constructor() {
    super();

    this.state = {
      uid: undefined,
      managerUid: undefined,
      diffYear: new Date().getFullYear(),
      diff: '',
      reason: '',
      loading: true,
      rows: [],
    }
  }

  getUserList = (input, callback) => {
    if (this.userList) {
      const userOption = this.userList.map(user => ({
        value: user.uid,
        label: user.name,
      }));
      callback(null, {
        options: userOption,
        complete: true,
      });
    } else {
      setTimeout(() => {
        this.getUserList(input, callback);
      }, 100);
    }
  };

  getManagerList = (input, callback) => {
    if (this.managerList) {
      const managerOption = this.managerList.map(manager => ({
        value: manager.uid,
        label: manager.name,
      }));
      callback(null, {
        options: managerOption,
        complete: true,
      });
    } else {
      setTimeout(() => {
        this.getManagerList(input, callback);
      }, 100);
    }
  };

  onChangeUser = (selected) => {
    if (!selected) {
      this.setState(Object.assign({}, this.state, {
        uid: undefined,
        rows: [],
      }));
      return;
    }

    const uid = selected.value;

    this.setState(Object.assign({}, this.state, {
      loading: true
    }));
    axios.get(`/holidayadmin/uid/${uid}`)
      .then(res => {
        if (res.status == 200) {
          const rows = res.data.map(row => {
            const uidUser = this.userList.filter(user => user.uid === row.uid)[0];
            row.name = uidUser? uidUser.name : `UID: ${row.uid}`;
            const managerUidUser = this.userList.filter(user => user.uid === row.manager_uid)[0];
            row.managerName = managerUidUser? managerUidUser.name : `UID: ${row.uid}`;
            return row;
          });
          this.setState(Object.assign({}, this.state, {
            uid: uid,
            loading: false,
            rows: rows
          }));
        }
      });
  };

  onCreate = () => {
    let diff = parseFloat(this.state.diff);
    if (isNaN(diff)) {
      return;
    }

    if(!this.state.managerUid) {
      alert('결제자를 선택하셔야 합니다.');
      return;
    }

    const data = {
      uid: this.state.uid,
      diffYear: this.state.diffYear,
      managerUid: this.state.managerUid,
      diff: diff,
      reason: this.state.reason
    };

    this.setState(Object.assign({}, this.state, {
      loading: true
    }));
    const uid = this.state.uid;
    axios.post(`/holidayadmin/uid/${uid}`, data)
      .then(res => {
        if (res.status == 201) {
          const newRow = res.data;
          const uidUser = this.userList.filter(user => user.uid === newRow.uid)[0];
          newRow.name = uidUser? uidUser.name : `UID: ${newRow.uid}`;
          const managerUidUser = this.userList.filter(user => user.uid === newRow.manager_uid)[0];
          newRow.managerName = managerUidUser? managerUidUser.name : `UID: ${newRow.uid}`;
          this.setState(Object.assign({}, this.state, {
            uid: uid,
            loading: false,
            rows: this.state.rows.concat([newRow]),
          }));
        }
      });
  };

  onDelete = (id) => {
    const uid = this.state.uid;

    this.setState(Object.assign({}, this.state, {
      loading: true
    }));
    axios.delete(`/holidayadmin/uid/${uid}/id/${id}`)
      .then(res => {
        if (res.status == 200) {
          this.setState(Object.assign({}, this.state, {
            loading: false,
            rows: this.state.rows.filter(row => row.id !== id),
          }));
        }
      });
  };

  onYearChange = (year) => {
    this.setState(Object.assign({}, this.state, {
      diffYear: parseInt(year)
    }));
  };

  onManagerChange = (selected) => {
    if (!selected) {
      this.setState(Object.assign({}, this.state, {
        managerUid: undefined
      }));
      return;
    }

    const managerUid = selected.value;
    this.setState(Object.assign({}, this.state, {
      managerUid: parseInt(managerUid)
    }));
  };

  onDiffChange = (diff) => {
    if (diff !== '') {
      const matched = diff.match(/^([+-])?\d*(\.|(\.5))?$/);
      if (!matched) {
        return;
      }

      if (!matched[1]) {
        diff = `+${diff}`;
      }
    }

    this.setState(Object.assign({}, this.state, {
      diff: diff
    }));
  };

  onReasonChange = (reason) => {
    this.setState(Object.assign({}, this.state, {
      reason: reason
    }));
  };

  componentDidMount() {
    axios('/holidayadmin/list')
      .then((res) => {
        this.userList = res.data.userList;
        this.managerList = res.data.managerList;

        this.setState(Object.assign({}, this.state, {
          loading: false,
        }));
      });
  }

  render() {
    const { uid, managerUid, rows, diffYear, diff, reason, loading } = this.state;

    return (
      <div>
        <Grid>
          <Row>
            <h1>휴가 관리</h1>
          </Row>
          <Row>
            <Col md={4}>
              <Select.Async
                value={uid}
                loadOptions={this.getUserList}
                placeholder="직원 선택"
                disabled={loading}
                onChange={this.onChangeUser}
              />
            </Col>
          </Row>
          <Row>
            <Col md={12}>
              <HolidayAdjustTable
                initial={!uid}
                loading={loading} rows={rows}
                onDelete={this.onDelete}
              />
            </Col>
          </Row>
          <Row>
            <Col md={12}>
              <HolidayAdjustCreator
                disabled={!uid || loading}
                loadManagerList={this.getManagerList}
                managerUid={managerUid}
                diffYear={diffYear}
                diff={diff}
                reason={reason}
                onYearChange={this.onYearChange}
                onManagerChange={this.onManagerChange}
                onDiffChange={this.onDiffChange}
                onReasonChange={this.onReasonChange}
                onCreate={this.onCreate}
              />
            </Col>
          </Row>
        </Grid>
      </div>
    );
  }
}

export default HolidayAdjust;
