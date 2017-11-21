import React from 'react';
import PropTypes from 'prop-types';
import axios from 'axios';
import { Grid, Col, Row, Table, Button } from 'react-bootstrap';
import Select from 'react-select';
import 'react-select/dist/react-select.css';

class UserAssigner extends React.Component {
  constructor() {
    super();

    this.state = {
      loading: true,
      users: [],
      roles: [], // [ { name:'foo', role:'bar' }, ]
      assigned: {}, // { role1: [...uids1], role2: [...uids2], }
    };

    this.handleChangeUser = this.handleChangeUser.bind(this);
    this.handleSave = this.handleSave.bind(this);
  }

  async componentDidMount() {
    const res = await Promise.all([
      await axios(this.props.apiUrl),
      await axios('/users/list'),
    ]);

    const { roles, assigned } = res[0].data;
    this.setState({
      loading: false,
      roles,
      assigned,
      users: res[1].data,
    });
  }

  handleChangeUser(role, selected) {
    this.setState({
      assigned: Object.assign({}, this.state.assigned, {
        [role]: selected.map(user => user.value),
      }),
    });
  }

  async handleSave() {
    this.setState({ loading: true });

    await axios.post(this.props.apiUrl, {
      assigned: this.state.assigned,
    });

    this.setState({ loading: false });
  }

  renderRoleRows() {
    return this.state.roles.map(role => (
      <tr key={role.keyword}>
        <td width={200}>{role.name}</td>
        <td>
          <Select
            multi
            value={this.state.assigned[role.keyword]}
            placeholder="직원 선택"
            backspaceToRemoveMessage=""
            options={this.state.users.map(user => ({ label: user.name, value: user.uid }))}
            isLoading={this.state.loading}
            disabled={this.state.loading}
            onChange={selected => this.handleChangeUser(role.keyword, selected)}
          />
        </td>
      </tr>
    ));
  }

  render() {
    return (
      <Grid>
        <Row>
          <Col>
            <h2>{this.props.name} 설정</h2>
          </Col>
        </Row>
        <Row>
          <Col>
            <Table condensed hover striped responsive>
              <thead>
                <tr>
                  <th>{this.props.name}</th>
                  <th>대상</th>
                </tr>
              </thead>
              <tbody>
                { this.renderRoleRows() }
              </tbody>
            </Table>
          </Col>
        </Row>
        <Row>
          <Button onClick={this.handleSave}>저장</Button>
        </Row>
      </Grid>
    );
  }
}

UserAssigner.propTypes = {
  apiUrl: PropTypes.string.isRequired,
  name: PropTypes.string.isRequired,
};

export default UserAssigner;
