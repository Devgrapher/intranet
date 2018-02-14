import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import clickOutside from 'react-click-outside';
import NumberFormat from 'react-number-format';
import DateTime from 'react-datetime';
import Select from 'react-select';
import 'react-select/dist/react-select.css';
import Input from '../../../Input';
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
    onSubmit: PropTypes.func,
  };

  static defaultProps = {
    className: undefined,
    children: undefined,
    data: {},
    inline: true,
    fetching: false,
    editable: true,
    onSubmit: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      editing: false,
      data: props.data,
    };
  }

  handleClickOutside() {
    this.closeEditor();
  }

  openEditor() {
    this.setState({
      editing: true,
      data: this.props.data,
    });
  }

  closeEditor() {
    this.setState({
      editing: false,
    });
  }

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
        options,
        precision,
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
            switch (type) {
              case 'checkbox':
                return (
                  <label htmlFor={id}>
                    <input
                      id={id}
                      type="checkbox"
                      name={key}
                      checked={value}
                      onChange={(e) => {
                        this.updateData(key, e.target.checked);
                      }}
                    />
                    {label}
                  </label>
                );
              case 'currency':
                return (
                  <Input
                    id={id}
                    className="form-control currency"
                    name={key}
                    focusOnMount
                    placeholder={placeholder || '0'}

                    inputComponent={NumberFormat}
                    thousandSeparator=","
                    decimalScale={precision}
                    fixedDecimalScale
                    value={value}
                    onValueChange={(values) => {
                      const { floatValue } = values;
                      this.updateData(key, !Number.isNaN(floatValue) ? floatValue : undefined);
                    }}
                  />
                );
              case 'select':
                return (
                  <Select
                    id={id}
                    name={key}
                    value={value}
                    options={options}
                    placeholder={placeholder}
                    onChange={(selectedOption) => {
                      this.updateData(key, selectedOption ? selectedOption.value : '');
                    }}
                  />
                );
              case 'textarea':
                return (
                  <Input
                    className="form-control"
                    id={id}
                    name={key}
                    value={value}
                    placeholder={placeholder}
                    focusOnMount
                    inputComponent="textarea"
                    maxLength="255"
                    onChange={(e) => {
                      this.updateData(key, e.target.value || '');
                    }}
                  />
                );
              case 'date':
                return (
                  <React.Fragment>
                    <Input
                      className="form-control"
                      id={id}
                      value={value}
                      placeholder={placeholder}
                      focusOnMount
                      onChange={(e) => {
                        this.updateData(key, e.target.value || '');
                      }}
                    />
                    <DateTime
                      viewMode="days"
                      dateFormat="YYYY-MM-DD"
                      timeFormat={false}
                      value={value}
                      placeholder={placeholder}
                      input={false}
                      inputProps={{ className: 'form-control', name: key }}
                      renderMonth={(props, month) => <td {...props}>{month + 1}</td>}
                      onChange={(m) => {
                        this.updateData(key, m.format('YYYY-MM-DD'));
                      }}
                    />
                  </React.Fragment>
                );
              case 'month':
                return (
                  <React.Fragment>
                    <Input
                      className="form-control"
                      id={id}
                      value={value}
                      placeholder={placeholder}
                      focusOnMount
                      onChange={(e) => {
                        this.updateData(key, e.target.value || '');
                      }}
                    />
                    <DateTime
                      viewMode="months"
                      dateFormat="YYYY-MM"
                      value={value}
                      placeholder={placeholder}
                      input={false}
                      inputProps={{ className: 'form-control', name: key }}
                      renderMonth={(props, month) => <td {...props}>{month + 1}</td>}
                      onChange={(m) => {
                        this.updateData(key, m.format('YYYY-MM'));
                      }}
                    />
                  </React.Fragment>
                );
              default:
                return (
                  <Input
                    className="form-control"
                    id={id}
                    type={type}
                    name={key}
                    value={value}
                    placeholder={placeholder}
                    focusOnMount
                    onChange={(e) => {
                      this.updateData(key, e.target.value || '');
                    }}
                  />
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
      onSubmit,
    } = this.props;
    return (
      <td
        className={cn('editable-cell component', className, {
          editing: this.state.editing,
          fetching,
          editable,
        })}
        onDoubleClick={() => {
          if (!editable) {
            return;
          }
          this.openEditor();
          window.getSelection().removeAllRanges();
        }}
      >
        {fetching ? (
          this.renderProgress()
        ) : (
          this.props.children
        )}

        {this.state.editing && (
          <div className={cn('editor popover bottom', { inline })}>
            <div className="arrow" />
            <div className="popover-content">
              <form
                onSubmit={(e) => {
                  e.preventDefault();
                  onSubmit(_.mapValues(this.state.data, ({ value }) => value));
                  this.closeEditor();
                }}
              >
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
              onClick={() => { this.openEditor(); }}
            >
              <span className="glyphicon glyphicon-pencil" />
            </button>
          </div>
        </div>
      </td>
    );
  }
}

export default clickOutside(EditableCell);
