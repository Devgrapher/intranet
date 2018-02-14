import DeferredInput from 'react-deferred-input';

const DELAY = 50;

export default class Input extends DeferredInput {
  componentDidMount() {
    if (this.props.focusOnMount) {
      setTimeout(() => {
        this.focus.call(this);
        this.select.call(this);
      }, DELAY);
    }
  }

  componentWillReceiveProps(nextProps) {
    if (this.state.value !== nextProps.value) {
      this.setState({ value: nextProps.value });
    }
  }

  handleChange(event) {
    super.handleChange(event);
    const { onChange = () => {} } = this.props;
    onChange(event);
  }

  handleBlur() {
    this.props.onBlur(this.state.value);
    if (this.props.clearOnChange) {
      this.setState({ value: '' });
    }
  }
}
