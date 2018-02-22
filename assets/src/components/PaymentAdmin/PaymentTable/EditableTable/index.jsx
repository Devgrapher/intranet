import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import { StickyTable, Row, Cell } from 'react-sticky-table';
import 'react-sticky-table/dist/react-sticky-table.css';
import EditableCell from './EditableCell';
import './style.less';

export default class EditableTable extends React.Component {
  static propTypes = {
    className: PropTypes.string,
    children: PropTypes.node,
    columns: PropTypes.arrayOf(PropTypes.shape({
      key: PropTypes.string,
      displayName: PropTypes.string,

      getHeaderCellProps: PropTypes.func,
      getDataCellProps: PropTypes.func,
      renderHeaderCell: PropTypes.func,
      renderDataCell: PropTypes.func,
    })).isRequired,
    rows: PropTypes.arrayOf(PropTypes.object),

    renderEmptyContent: PropTypes.func,
  };

  static defaultProps = {
    className: undefined,
    rows: undefined,
    children: undefined,

    renderEmptyContent: () => {},
  };

  renderHeaderCell(column) {
    const {
      key,
      displayName,
      getHeaderCellProps = () => ({}),
      renderHeaderCell,
    } = column;
    const { className, ...props } = getHeaderCellProps(column);
    return (
      <Cell
        key={key}
        className={cn('th', key, className)}
        {...props}
      >
        {renderHeaderCell ? renderHeaderCell() : displayName}
      </Cell>
    );
  }

  renderDataCell(row, column) {
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
      <EditableCell
        key={key}
        className={cn('td', key, className)}
        {...props}
      >
        {renderDataCell ? renderDataCell(row, column) : (
          row[key]
        )}
      </EditableCell>
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
    return (
      <StickyTable
        className={cn(
          'editable-table component',
          'table',
          className,
        )}
        stickyColumnCount={0}
      >
        <Row className="tr tr-head">
          {_.map(columns, this.renderHeaderCell)}
        </Row>
        {!rows ? (
          <Row className="tr tr-body empty">
            <EditableCell className="td" colSpan={_.size(columns)} editable={false}>
              {renderEmptyContent()}
            </EditableCell>
          </Row>
        ) : (
          _.map(rows, (row, rowIndex) => (
            <Row className="tr tr-body" key={rowIndex}>
              {_.map(columns, column => this.renderDataCell(row, column))}
            </Row>
          ))
        )}
        {children}
      </StickyTable>
    );
  }
}
