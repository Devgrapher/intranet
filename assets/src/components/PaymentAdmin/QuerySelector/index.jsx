import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment';
import DateTime from 'react-datetime';
import 'react-datetime/css/react-datetime.css';
import { formatCurrency } from '../../../utils';
import './style.less';

export default class QuerySelector extends React.Component {
  static propTypes = {
    className: PropTypes.string,

    teams: PropTypes.arrayOf(PropTypes.string).isRequired,
    categories: PropTypes.arrayOf(PropTypes.string).isRequired,

    todayQueuedCost: PropTypes.number.isRequired,
    todayQueuedCount: PropTypes.number.isRequired,
    todayConfirmedQueuedCost: PropTypes.number.isRequired,
    todayConfirmedQueuedCount: PropTypes.number.isRequired,
    todayUnconfirmedQueuedCost: PropTypes.number.isRequired,
    todayUnconfirmedQueuedCount: PropTypes.number.isRequired,

    onQueryChange: PropTypes.func,
  };

  static defaultProps = {
    className: undefined,

    onQueryChange: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      items: undefined,
      selectedItem: undefined,
      params: undefined,
    };
  }

  componentDidMount() {
    const items = [
      {
        name: '오늘 결제 (결제 대기 중)',
        url: '/',
        getDefaultParams: () => ({
          type: 'today',
        }),
        render: () => {
          const {
            todayQueuedCost,
            todayQueuedCount,
            todayConfirmedQueuedCost,
            todayConfirmedQueuedCount,
            todayUnconfirmedQueuedCost,
            todayUnconfirmedQueuedCount,
          } = this.props;
          return (
            <select
              className="form-control input-sm"
              name="type"
              value={this.state.params.type}
              onChange={e => this.setParams({ type: e.target.value })}
            >
              <option value="today">
                전체 ({formatCurrency(todayQueuedCost)}원, {todayQueuedCount}건)
              </option>
              <option value="todayConfirmed">
                승인 ({formatCurrency(todayConfirmedQueuedCost)}원, {todayConfirmedQueuedCount}건)
              </option>
              <option value="todayUnconfirmed">
                미승인 ({formatCurrency(todayUnconfirmedQueuedCost)}원, {todayUnconfirmedQueuedCount}건)
              </option>
            </select>
          );
        },
      },
      {
        name: '이번 달 결제 (전체)',
        url: '/',
        getDefaultParams: () => ({
          type: 'month',
          month: moment().format('YYYY-MM'),
        }),
        render: () => {},
      },
      {
        name: '이번 달 결제 (결제 대기 중)',
        url: '/',
        getDefaultParams: () => ({
          type: 'monthQueued',
          month: moment().format('YYYY-MM'),
        }),
        render: () => {},
      },
      {
        name: '결제 대기 중',
        url: '/',
        getDefaultParams: () => ({
          type: 'remain',
        }),
        render: () => {},
      },

      { type: 'separator' },

      {
        name: '세금 계산서 기간 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'taxDate',
          month: moment().format('YYYY-MM'),
        }),
        render: () => this.renderMonth('month', this.state.params.month),
      },
      {
        name: '귀속 월 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'month',
          month: moment().format('YYYY-MM'),
        }),
        render: () => this.renderMonth('month', this.state.params.month),
      },
      {
        name: '귀속 부서 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'team',
          team: this.props.teams[0],
        }),
        render: () => (
          <select
            className="form-control input-sm"
            name="team"
            value={this.state.params.team}
            onChange={e => this.setParams({ type: 'team', team: e.target.value })}
          >
            {_.map(this.props.teams, (team, key) => (
              <option key={key} value={team}>{team}</option>
            ))}
          </select>
        ),
      },
      {
        name: '분류 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'category',
          category: this.props.categories[0],
        }),
        render: () => (
          <select
            className="form-control input-sm"
            name="category"
            value={this.state.params.category}
            onChange={e => this.setParams({ type: 'category', category: e.target.value })}
          >
            {_.map(this.props.categories, (category, key) => (
              <option key={key} value={category}>{category}</option>
            ))}
          </select>
        ),
      },
      {
        name: '요청 기간 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'requestDate',
          begin_date: moment().format('YYYY-MM-DD'),
          end_date: moment().format('YYYY-MM-DD'),
        }),
        render: () => (
          <React.Fragment>
            {this.renderDate('begin_date', this.state.params.begin_date)}
            <span>~</span>
            {this.renderDate('end_date', this.state.params.end_date)}
          </React.Fragment>
        ),
      },
      {
        name: '결제 일 별',
        url: '/',
        getDefaultParams: () => ({
          type: 'payDate',
          begin_date: moment().format('YYYY-MM-DD'),
          end_date: moment().format('YYYY-MM-DD'),
        }),
        render: () => (
          <React.Fragment>
            {this.renderDate('begin_date', this.state.params.begin_date)}
            <span>~</span>
            {this.renderDate('end_date', this.state.params.end_date)}
          </React.Fragment>
        ),
      },
    ];
    this.setState({ items });
    this.selectItem(items[0]);
  }

  setParams(params) {
    this.setState({ params });
    this.props.onQueryChange(this.state.selectedItem.url, params);
  }

  selectItem(item) {
    const params = item.getDefaultParams();
    this.setState({
      selectedItem: item,
      params,
    });
    this.props.onQueryChange(item.url, params);
  }

  renderMonth(name = 'month', value = new Date()) {
    return (
      <DateTime
        viewMode="months"
        dateFormat="YYYY-MM"
        value={value}
        onChange={(v) => {
          const m = moment(v, 'YYYY-MM', true);
          this.setParams(_.set(
            { ...this.state.params },
            name,
            m.isValid() ? m.format('YYYY-MM') : v,
          ));
        }}
        inputProps={{ className: 'form-control input-sm', name }}
        renderMonth={(props, month) => <td {...props}>{month + 1}</td>}
      />
    );
  }

  renderDate(name = 'date', value = new Date()) {
    return (
      <DateTime
        viewMode="days"
        dateFormat="YYYY-MM-DD"
        timeFormat={false}
        value={value}
        onChange={(v) => {
          const m = moment(v, 'YYYY-MM-DD', true);
          this.setParams(_.set(
            { ...this.state.params },
            name,
            m.isValid() ? m.format('YYYY-MM-DD') : v,
          ));
        }}
        inputProps={{ className: 'form-control input-sm', name }}
        renderMonth={(props, month) => <td {...props}>{month + 1}</td>}
      />
    );
  }

  render() {
    const { className } = this.props;
    const { items, selectedItem = { render: () => {} } } = this.state;
    return (
      <div className={cn('query-selector', className)}>
        <div className="query-selector__container">
          <div className="btn-group btn-group-sm">
            <button
              className="btn btn-default dropdown-toggle"
              type="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              {selectedItem.name} <span className="caret" />
            </button>
            <ul className="dropdown-menu">
              {_.map(items, (item, key) => (
                item.type === 'separator' ? (
                  <li key={key} role="separator" className="divider" />
                ) : (
                  <li key={key}>
                    <button onClick={() => { this.selectItem(item); }}>
                      {item.name}
                    </button>
                  </li>
                )
              ))}
            </ul>
          </div>

          <div className="query-selector__query-type">
            {selectedItem.render()}
          </div>
        </div>
      </div>
    );
  }
}
