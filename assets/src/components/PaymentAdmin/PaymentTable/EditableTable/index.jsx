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

  renderHeaderCell(column, columnIndex) {
    const {
      key,
      displayName,
      getHeaderCellProps = () => ({}),
      renderHeaderCell,
    } = column;
    const { className, ...props } = getHeaderCellProps(column);
    return (
      <HeaderCell
        key={key || columnIndex}
        className={cn(key, className)}
        {...props}
      >
        {renderHeaderCell ? renderHeaderCell() : displayName}
      </HeaderCell>
    );
  }

  renderDataCell(row, column, columnIndex) {
    const {
      key,
      getDataCellProps = () => ({}),
      renderDataCell,
    } = column;

    const {
      className,
      ...props
    } = getDataCellProps(row, column);

    return (
      <DataCell
        key={key || columnIndex}
        className={cn(key, className)}
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
      rows,
      renderEmptyContent,
    } = this.props;
    const filteredRows = this.filterRows(rows);
    return (
      <Table className={cn('editable-table component', className)}>
        <Row className="header">
          {_.map(columns, this.renderHeaderCell)}
        </Row>
        {_.isEmpty(filteredRows) ? (
          <Row className="empty">
            <DataCell editable={false}>
              {renderEmptyContent()}
            </DataCell>
          </Row>
        ) : (
          _.map(filteredRows, (row, rowIndex) => (
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
