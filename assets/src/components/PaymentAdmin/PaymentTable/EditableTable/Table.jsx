import React from 'react';
import { StickyTable } from 'react-sticky-table';
import 'react-sticky-table/dist/react-sticky-table.css';
import cn from 'classnames';

export default class Table extends StickyTable {
  static defaultProps = {
    ...StickyTable.defaultProps,
    stickyHeaderCount: 1,
    stickyColumnCount: 0,
  };

  getStickyColumns(rows) {
    const columnsCount = this.props.stickyColumnCount;
    return rows.map((row, r) => {
      const cells = React.Children.toArray(row.props.children).slice(0, columnsCount);
      return React.cloneElement(row, { id: '', key: r }, cells);
    });
  }

  getStickyHeader(rows) {
    const row = rows[0];
    const cells = React.Children.toArray(row.props.children).map((cell, c) => (
      React.cloneElement(cell, { id: `sticky-header-cell-${c}`, key: c })
    ));
    return React.cloneElement(row, { id: 'sticky-header-row' }, cells);
  }

  getStickyCorner(rows) {
    const row = rows[0];
    const columnsCount = this.props.stickyColumnCount;
    const cells = React.Children.toArray(row.props.children).slice(0, columnsCount);
    return [React.cloneElement(row, { id: '', key: 0 }, cells)];
  }

  render() {
    const element = super.render();
    return React.cloneElement(element, {
      className: cn(element.props.className, 'table'),
    });
  }
}
