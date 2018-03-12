import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import clickOutside from 'react-click-outside';
import 'react-select/dist/react-select.css';
import { Cell } from 'react-sticky-table';
import FormElement from '../../../FormElement';
import './style.less';

class EditableCell extends React.Component {
  static propTypes = {
    className: PropTypes.string,
    children: PropTypes.node,
    data: PropTypes.objectOf(PropTypes.shape({
      type: PropTypes.string,
      value: PropTypes.any,
      placeholder: PropTypes.string,
      options: PropTypes.array,
      precision: PropTypes.number,
      label: PropTypes.string,
      isVisible: PropTypes.func,
    })),
    inline: PropTypes.bool,
    fetching: PropTypes.bool,
    editable: PropTypes.bool,
    onBeforeSubmit: PropTypes.func,
    onSubmit: PropTypes.func,
  };

  static defaultProps = {
    className: undefined,
    children: undefined,
    data: {},
    inline: true,
    fetching: false,
    editable: true,
    onBeforeSubmit: data => data,
    onSubmit: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      editing: false,
      data: props.data,
    };
  }

  openEditor = () => {
    this.setState({
      editing: true,
      data: this.props.data,
    });
  };

  closeEditor = () => {
    this.setState({
      editing: false,
    });
  };

  updateData(key, value) {
    this.setState({
      data: {
        ...this.state.data,
        [key]: {
          ...this.state.data[key],
          value,
        },
      },
    });
  }

  handleClickOutside() {
    this.closeEditor();
  }

  handleCellDoubleClick = () => {
    const { editable } = this.props;
    if (!editable) {
      return;
    }
    this.openEditor();
    window.getSelection().removeAllRanges();
  };

  handleSubmit = (e) => {
    const {
      data,
      onBeforeSubmit,
      onSubmit,
    } = this.props;

    try {
      e.preventDefault();
      if (_.isEqual(this.state.data, data)) {
        return;
      }
      const transformedData = onBeforeSubmit(_.mapValues(this.state.data, ({ value }) => value));
      if (!transformedData) {
        return;
      }
      onSubmit(transformedData);
    } finally {
      this.closeEditor();
    }
  };

  renderProgress(message) {
    return (
      <div className="progress">
        <div
          className="progress-bar progress-bar-striped active"
          role="progressbar"
          aria-valuenow="100"
          aria-valuemin="0"
          aria-valuemax="100"
          style={{ width: '100%' }}
        >
          {message}
        </div>
      </div>
    );
  }

  renderData() {
    const { data } = this.state;
    return _.map(data, (v, key) => {
      const {
        type,
        value,
        placeholder,
        label,
        isVisible,
      } = v;
      if (isVisible instanceof Function && !isVisible(data)) {
        return null;
      }
      const id = `input[${key}]`;
      return (
        <div key={key} className={cn('form-group form-group-sm', key)}>
          {(() => {
            const props = {
              id,
              name: key,
              onChange: newValue => this.updateData(key, newValue),
              ..._.omit(v, ['label', 'isVisible']),
            };
            switch (type) {
              case FormElement.Types.DATE:
              case FormElement.Types.MONTH:
                return (
                  <React.Fragment>
                    <FormElement
                      value={value}
                      placeholder={placeholder}
                      focusOnMount
                      onChange={newValue => this.updateData(key, newValue)}
                    />
                    <FormElement input={false} {...props} />
                  </React.Fragment>
                );
              default:
                return (
                  <FormElement focusOnMount {...props}>{label}</FormElement>
                );
            }
          })()}
        </div>
      );
    });
  }

  render() {
    const {
      className,
      inline,
      fetching,
      editable,
      children,
    } = this.props;
    const props = _.omit(this.props, _.keys(EditableCell.propTypes));
    return (
      <Cell
        className={cn('editable-cell component', className, {
          editing: this.state.editing,
          fetching,
          editable,
        })}
        onDoubleClick={this.handleCellDoubleClick}
        {...props}
      >
        {fetching ? (
          this.renderProgress()
        ) : (
          children
        )}

        {this.state.editing && (
          <div className={cn('editor popover bottom', { inline })}>
            <div className="arrow" />
            <div className="popover-content">
              <form onSubmit={this.handleSubmit}>
                {this.renderData()}
                <button type="submit" className="btn btn-xs btn-primary">
                  <span className="glyphicon glyphicon-ok" />
                </button>
              </form>
            </div>
          </div>
        )}

        <div className="toolbox-container">
          <div className="toolbox">
            <button
              className="edit btn btn-xs btn-primary"
              onClick={this.openEditor}
            >
              <span className="glyphicon glyphicon-pencil" />
            </button>
          </div>
        </div>
      </Cell>
    );
  }
}

export default clickOutside(EditableCell);
