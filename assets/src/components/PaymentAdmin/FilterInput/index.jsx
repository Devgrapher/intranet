import React from 'react';
import PropTypes from 'prop-types';
import { Button, InputGroup, Glyphicon } from 'react-bootstrap';
import FormElement from '../FormElement';
import './style.less';

export default class FilterInput extends React.Component {
  static propTypes = {
    onChange: PropTypes.func,
  };

  static defaultProps = {
    onChange: () => {},
  };

  constructor(props) {
    super(props);
    this.state = {
      value: undefined,
      showInput: false,
    };
  }

  openInput = () => {
    this.setState({ showInput: true });
  };

  closeInput = () => {
    this.setState({ showInput: false, value: undefined });
    this.props.onChange();
  };

  handleChange = (value) => {
    this.setState({ value });
    this.props.onChange(value);
  };

  render() {
    const { value, showInput } = this.state;
    return (
      !showInput ? (
        <Button bsSize="small" onClick={this.openInput}>
          <Glyphicon glyph="filter" /> 필터
        </Button>
      ) : (
        <InputGroup className="filter-input component" bsSize="small">
          <InputGroup.Addon>
            <Glyphicon glyph="filter" />
          </InputGroup.Addon>

          <FormElement
            value={value}
            placeholder="키워드"
            focusOnMount
            onChange={this.handleChange}
          />

          <InputGroup.Button>
            <Button onClick={this.closeInput}>
              <Glyphicon glyph="remove" />
            </Button>
          </InputGroup.Button>
        </InputGroup>
      )
    );
  }
}
