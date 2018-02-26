import React from 'react';
import cn from 'classnames';
import EditableCell from './EditableCell';

export default class DataCell extends EditableCell {
  render() {
    const element = super.render();
    return React.cloneElement(element, {
      className: cn(element.props.className, 'td'),
    });
  }
}
