import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import Table from './Table';
import Row from './Row';
import HeaderCell from './HeaderCell';
import DataCell from './DataCell';
import './style.less';

export default class EditableTable extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    className: PropTypes.string,
    columns: PropTypes.arrayOf(PropTypes.shape({
      key: PropTypes.string,
      displayName: PropTypes.string,
      sortable: PropTypes.bool,

      getHeaderCellProps: PropTypes.func,
      getDataCellProps: PropTypes.func,
      renderHeaderCell: PropTypes.func,
      renderDataCell: PropTypes.func,
    })).isRequired,
    rows: PropTypes.arrayOf(PropTypes.object),
    filterString: PropTypes.string,

    renderEmptyContent: PropTypes.func,
  };

  static defaultProps = {
    children: undefined,
    className: undefined,
    rows: undefined,
    filterString: undefined,

    renderEmptyContent: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      sortOptions: {
        key: undefined,
        order: undefined,
      },
    };
  }

  switchSortOptions(key) {
    const { sortOptions } = this.state;
    if (key !== sortOptions.key) {
      this.setState({ sortOptions: { key, order: 'asc' } });
      return;
    }
    if (sortOptions.order === 'asc') {
      this.setState({ sortOptions: { key, order: 'desc' } });
      return;
    }
    this.setState({ sortOptions: {} });
  }

  filterRows(rows) {
    const { filterString } = this.props;
    const keywords = _.filter(_.split(_.toLower(filterString), ' '), _.identity);

    if (!_.size(keywords)) {
      return rows;
    }

    return _.filter(rows, row => (
      _.some(row, (value) => {
        if (!(_.isString(value) || _.isFinite(value))) {
          return false;
        }
        const stringValue = _.toLower(value);
        return _.every(keywords, keyword => _.includes(stringValue, keyword));
      })
    ));
  }

  sortRows(rows) {
    const { sortOptions: { key, order } } = this.state;
    if (!key) {
      return rows;
    }
    return _.orderBy(rows, [key], [order]);
  }

  renderHeaderCell(column, columnIndex) {
    const { sortOptions } = this.state;
    const {
      key,
      displayName,
      sortable,
      className: columnClassName,
      getHeaderCellProps = () => ({}),
      renderHeaderCell,
    } = column;
    const { className: headerCellClassName, ...props } = getHeaderCellProps(column);
    return (
      <HeaderCell
        key={key || columnIndex}
        className={cn(key, columnClassName, headerCellClassName, {
          sortable,
          [`sort-order-${sortOptions.order}`]: key === sortOptions.key,
        })}
        onClick={() => sortable && this.switchSortOptions(key)}
        {...props}
      >
        {renderHeaderCell ? renderHeaderCell() : displayName}
        {sortable && key === sortOptions.key && (
          <span className="sort-order">
            {sortOptions.order === 'asc' ? '▲' : '▼'}
          </span>
        )}
      </HeaderCell>
    );
  }

  renderDataCell(row, column, columnIndex) {
    const {
      key,
      className: columnClassName,
      getDataCellProps = () => ({}),
      renderDataCell,
    } = column;

    const {
      className: dataCellClassName,
      ...props
    } = getDataCellProps(row, column);

    return (
      <DataCell
        key={key || columnIndex}
        className={cn(key, columnClassName, dataCellClassName)}
        {...props}
      >
        {renderDataCell ? renderDataCell(row, column) : (
          row[key]
        )}
      </DataCell>
    );
  }

  render() {
    const {
      className,
      children,
      columns,
      rows: inputRows,
      renderEmptyContent,
    } = this.props;
    const rows = this.sortRows(this.filterRows(inputRows));
    return (
      <Table className={cn('editable-table component', className)}>
        <Row className="header">
          {_.map(columns, (column, columnIndex) => this.renderHeaderCell(column, columnIndex))}
        </Row>
        {_.isEmpty(rows) ? (
          <Row className="empty">
            <DataCell editable={false}>
              {renderEmptyContent()}
            </DataCell>
          </Row>
        ) : (
          _.map(rows, (row, rowIndex) => (
            <Row key={rowIndex}>
              {_.map(columns, (column, columnIndex) => this.renderDataCell(row, column, columnIndex))}
            </Row>
          ))
        )}
        {children}
      </Table>
    );
  }
}
