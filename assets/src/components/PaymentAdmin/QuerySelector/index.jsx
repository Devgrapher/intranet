import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment';
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
    query: PropTypes.shape({
      path: PropTypes.string,
      params: PropTypes.object,
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
    query: {},

    onQueryChange: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      items: undefined,
      selectedItem: undefined,
    };
  }

  componentWillMount() {
    const items = [
      {
        name: '오늘 결제 (결제 대기 중)',
        path: '/',
        type: 'today',
        getDefaultParams: ({ type }) => ({ type }),
        render: () => {
          const {
            data: {
              todayQueuedCost,
              todayQueuedCount,
              todayConfirmedQueuedCost,
              todayConfirmedQueuedCount,
              todayUnconfirmedQueuedCost,
              todayUnconfirmedQueuedCount,
            },
          } = this.props;
          return (
            <select
              className="form-control"
              name="type"
              value={this.props.query.params.type}
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
        path: '/',
        type: 'month',
        getDefaultParams: ({ type }) => ({ type }),
        render: () => {},
      },
      {
        name: '이번 달 결제 (결제 대기 중)',
        path: '/',
        type: 'monthQueued',
        getDefaultParams: ({ type }) => ({
          type,
          month: moment().format('YYYY-MM'),
        }),
        render: () => {},
      },
      {
        name: '결제 대기 중',
        path: '/',
        type: 'remain',
        getDefaultParams: ({ type }) => ({ type }),
        render: () => {},
      },

      { type: 'separator' },

      {
        name: '세금 계산서 기간 별',
        path: '/',
        type: 'taxDate',
        getDefaultParams: ({ type }) => ({
          type,
          month: moment().format('YYYY-MM'),
        }),
        render: () => this.renderMonth('month', this.props.query.params.month),
      },
      {
        name: '귀속 월 별',
        path: '/',
        type: 'month',
        getDefaultParams: ({ type }) => ({
          type,
          month: moment().format('YYYY-MM'),
        }),
        render: () => this.renderMonth('month', this.props.query.params.month),
      },
      {
        name: '귀속 부서 별',
        path: '/',
        type: 'team',
        getDefaultParams: ({ type }) => ({
          type,
          team: this.props.data.const.team[0],
        }),
        render: () => (
          <select
            className="form-control"
            name="team"
            value={this.props.query.params.team}
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
        path: '/',
        type: 'category',
        getDefaultParams: ({ type }) => ({
          type,
          category: this.props.data.const.category[0],
        }),
        render: () => (
          <select
            className="form-control"
            name="category"
            value={this.props.query.params.category}
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
        path: '/',
        type: 'requestDate',
        getDefaultParams: ({ type }) => ({
          type,
          begin_date: moment().format('YYYY-MM-DD'),
          end_date: moment().format('YYYY-MM-DD'),
        }),
        render: () => (
          <React.Fragment>
            {this.renderDate('begin_date', this.props.query.params.begin_date)}
            <span>~</span>
            {this.renderDate('end_date', this.props.query.params.end_date)}
          </React.Fragment>
        ),
      },
      {
        name: '결제 일 별',
        path: '/',
        type: 'payDate',
        getDefaultParams: ({ type }) => ({
          type,
          begin_date: moment().format('YYYY-MM-DD'),
          end_date: moment().format('YYYY-MM-DD'),
        }),
        render: () => (
          <React.Fragment>
            {this.renderDate('begin_date', this.props.query.params.begin_date)}
            <span>~</span>
            {this.renderDate('end_date', this.props.query.params.end_date)}
          </React.Fragment>
        ),
      },
    ];
    this.setState({ items });
  }

  componentDidMount() {
    this.setItemByQuery(this.props.query);
  }

  componentWillReceiveProps(nextProps) {
    const { query } = nextProps;
    if (_.isEqual(query, this.props.query)) {
      return;
    }
    this.setItemByQuery(query);
  }

  setItemByQuery({ path, params = {} } = {}) {
    const item = _.find(this.state.items, (i) => {
      if (i.path !== path) {
        return false;
      }
      switch (i.type) {
        case 'today':
          return _.includes([
            'today',
            'todayConfirmed',
            'todayUnconfirmed',
          ], params.type);
        case 'month':
          return (
            i.type === params.type
            && _.has(i.getDefaultParams(i), 'month') === _.has(params, 'month')
          );
        default:
          return i.type === params.type;
      }
    });
    if (!item) {
      const defaultItem = this.state.items[0];
      this.selectItem(defaultItem, defaultItem.getDefaultParams(defaultItem));
      return;
    }
    this.selectItem(item, params);
  }

  setParams(params) {
    if (_.isEqual(params, this.props.query.params)) {
      return;
    }
    this.props.onQueryChange({ ...this.props.query, params });
  }

  selectItem(item, params) {
    if (item === this.state.selectedItem) {
      return;
    }
    const { path } = item;
    this.setState({ selectedItem: item });
    this.props.onQueryChange({ path, params: params || item.getDefaultParams(item) });
  }

  renderMonth(name = 'month', value = new Date()) {
    return (
      <FormElement
        type={FormElement.Types.MONTH}
        name={name}
        value={value}
        onChange={v => this.setParams(_.set({ ...this.props.query.params }, name, v))}
      />
    );
  }

  renderDate(name = 'date', value = new Date()) {
    return (
      <FormElement
        type={FormElement.Types.DATE}
        name={name}
        value={value}
        onChange={v => this.setParams(_.set({ ...this.props.query.params }, name, v))}
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
