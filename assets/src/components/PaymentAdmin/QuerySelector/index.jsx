import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment';
import 'react-datetime/css/react-datetime.css';
import FormElement from '../FormElement';
import { formatCurrency } from '../../../utils';
import './style.less';

export default class QuerySelector extends React.Component {
  static propTypes = {
    className: PropTypes.string,
    data: PropTypes.shape({
      const: PropTypes.shape({
        team: PropTypes.arrayOf(PropTypes.string),
        category: PropTypes.arrayOf(PropTypes.string),
      }),
      todayQueuedCost: PropTypes.number,
      todayQueuedCount: PropTypes.number,
      todayConfirmedQueuedCost: PropTypes.number,
      todayConfirmedQueuedCount: PropTypes.number,
      todayUnconfirmedQueuedCost: PropTypes.number,
      todayUnconfirmedQueuedCount: PropTypes.number,
    }),

    onQueryChange: PropTypes.func,
  };

  static defaultProps = {
    className: undefined,
    data: {
      const: {
        team: [],
        category: [],
      },
      todayQueuedCost: 0,
      todayQueuedCount: 0,
      todayConfirmedQueuedCost: 0,
      todayConfirmedQueuedCount: 0,
      todayUnconfirmedQueuedCost: 0,
      todayUnconfirmedQueuedCount: 0,
    },

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
          } = this.props.data;
          return (
            <select
              className="form-control"
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
          team: this.props.data.const.team[0],
        }),
        render: () => (
          <select
            className="form-control"
            name="team"
            value={this.state.params.team}
            onChange={e => this.setParams({ type: 'team', team: e.target.value })}
          >
            {_.map(this.props.data.const.team, (team, key) => (
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
          category: this.props.data.const.category[0],
        }),
        render: () => (
          <select
            className="form-control"
            name="category"
            value={this.state.params.category}
            onChange={e => this.setParams({ type: 'category', category: e.target.value })}
          >
            {_.map(this.props.data.const.category, (category, key) => (
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
      <FormElement
        type={FormElement.Types.MONTH}
        name={name}
        value={value}
        onChange={v => this.setParams(_.set({ ...this.state.params }, name, v))}
      />
    );
  }

  renderDate(name = 'date', value = new Date()) {
    return (
      <FormElement
        type={FormElement.Types.DATE}
        name={name}
        value={value}
        onChange={v => this.setParams(_.set({ ...this.state.params }, name, v))}
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

          <div className="query-selector__query-type form-group-sm">
            {selectedItem.render()}
          </div>
        </div>
      </div>
    );
  }
}
