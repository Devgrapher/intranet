import React from 'react';
import * as ReactStickyTable from 'react-sticky-table';
import cn from 'classnames';

export default class Row extends ReactStickyTable.Row {
  render() {
    const element = super.render();
    return React.cloneElement(element, {
      className: cn(element.props.className, 'tr'),
    });
  }
}
