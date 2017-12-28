import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import { Overlay, Popover } from 'react-bootstrap';


class OverlayBinder extends React.Component {
  render() {
    const {
      id, children, width, show, placement, text,
    } = this.props;

    return (
      <div ref={(ref) => { this.ref = ref; }}>
        { children }
        <Overlay
          animation
          show={show}
          placement={placement}
          container={this}
          target={() => ReactDOM.findDOMNode(this.ref)}
        >
          <Popover id={id} style={{ width }}>{ text }</Popover>
        </Overlay>
      </div>
    );
  }
}

OverlayBinder.defaultProps = {
  show: false,
  width: undefined,
  placement: 'right',
  text: '',
};

OverlayBinder.propTypes = {
  id: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired,
  show: PropTypes.bool,
  width: PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number,
  ]),
  placement: PropTypes.string,
  text: PropTypes.string,
};

export default OverlayBinder;
