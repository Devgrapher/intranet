import React from 'react';
import * as ReactStickyTable from 'react-sticky-table';
import cn from 'classnames';

export default class HeaderCell extends ReactStickyTable.Cell {
  render() {
    const element = super.render();
    return React.cloneElement(element, {
      className: cn(element.props.className, 'th'),
    });
  }
}
