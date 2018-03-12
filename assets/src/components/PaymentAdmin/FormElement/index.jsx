import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment/moment';
import 'moment/locale/ko';
import { FormControl, Checkbox } from 'react-bootstrap';
import NumberFormat from 'react-number-format';
import DateTime from 'react-datetime';
import 'react-datetime/css/react-datetime.css';
import Select from 'react-select';
import 'react-select/dist/react-select.css';
import Input from '../../Input';
import './style.less';

function BootstrapInput(props) {
  return (
    <Input
      inputComponent={FormControl}
      {...props}
    />
  );
}

export default class FormElement extends React.Component {
  static Types = {
    TEXT: 'text',
    CHECKBOX: 'checkbox',
    CURRENCY: 'currency',
    SELECT: 'select',
    TEXTAREA: 'textarea',
    DATE: 'date',
    MONTH: 'month',
  };

  static propTypes = {
    id: PropTypes.string,
    className: PropTypes.string,
    type: PropTypes.oneOfType([
      PropTypes.oneOf(_.values(FormElement.Types)),
      PropTypes.string,
    ]),
    name: PropTypes.string,
    value: PropTypes.oneOfType([PropTypes.any]),
    placeholder: PropTypes.string,
    readOnly: PropTypes.bool,
    disabled: PropTypes.bool,
    children: PropTypes.node,

    onChange: PropTypes.func,
  };

  static defaultProps = {
    id: undefined,
    type: undefined,
    className: undefined,
    name: undefined,
    value: undefined,
    placeholder: undefined,
    readOnly: undefined,
    disabled: undefined,
    children: undefined,

    onChange: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const {
      id,
      className: inputClassName,
      type,
      name,
      value,
      placeholder,
      readOnly,
      disabled,
      children,

      onChange,
    } = this.props;
    const props = _.omit(this.props, _.keys(FormElement.propTypes));
    const className = cn('form-element component', type, inputClassName);

    switch (type) {
      case FormElement.Types.CHECKBOX:
        return (
          <BootstrapInput
            id={id}
            className={className}
            inputComponent={Checkbox}
            name={name}
            checked={value}
            readOnly={readOnly}
            disabled={disabled}
            onChange={e => onChange(e.target.checked)}
            {...props}
          >
            {children}
          </BootstrapInput>
        );
      case FormElement.Types.CURRENCY:
        return (
          <BootstrapInput
            id={id}
            className={className}
            name={name}
            value={value}
            placeholder={placeholder}
            readOnly={readOnly}
            disabled={disabled}
            componentClass={NumberFormat}

            thousandSeparator=","
            fixedDecimalScale
            onValueChange={(values) => {
              const { floatValue } = values;
              onChange(!Number.isNaN(floatValue) ? floatValue : undefined);
            }}
            {...props}
          />
        );
      case FormElement.Types.SELECT:
        return (
          <Select
            id={id}
            className={className}
            name={name}
            value={value}
            placeholder={placeholder}
            readOnly={readOnly}
            disabled={disabled}
            onChange={option => onChange(option ? option.value : option)}
            {...props}
          />
        );
      case FormElement.Types.TEXTAREA:
        return (
          <BootstrapInput
            id={id}
            className={className}
            name={name}
            value={value}
            placeholder={placeholder}
            readOnly={readOnly}
            disabled={disabled}
            componentClass="textarea"
            onChange={e => onChange(e.target.value || '')}
            {...props}
          />
        );
      case FormElement.Types.DATE:
        return (
          <DateTime
            className={className}
            viewMode="days"
            dateFormat="YYYY-MM-DD"
            timeFormat={false}
            value={value}
            inputProps={{
              id,
              className: inputClassName,
              name,
              placeholder,
              readOnly,
              disabled,
            }}
            renderInput={BootstrapInput}
            onChange={(v) => {
              const m = moment(v, 'YYYY-MM-DD', true);
              onChange(m.isValid() ? m.format('YYYY-MM-DD') : v);
            }}
            {...props}
          />
        );
      case FormElement.Types.MONTH:
        return (
          <DateTime
            className={className}
            viewMode="months"
            dateFormat="YYYY-MM"
            value={value}
            inputProps={{
              id,
              className: inputClassName,
              name,
              placeholder,
              readOnly,
              disabled,
            }}
            renderInput={BootstrapInput}
            onChange={(v) => {
              const m = moment(v, 'YYYY-MM', true);
              onChange(m.isValid() ? m.format('YYYY-MM') : v);
            }}
            {...props}
          />
        );
      default:
        return (
          <BootstrapInput
            id={id}
            className={className}
            type={type}
            name={name}
            value={value}
            placeholder={placeholder}
            readOnly={readOnly}
            disabled={disabled}
            onChange={e => onChange(e.target.value || '')}
            {...props}
          />
        );
    }
  }
}
