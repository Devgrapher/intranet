import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import { Button, CloseButton, Modal } from 'react-bootstrap';
import FormElement from '../FormElement';

export default class RejectPaymentModal extends React.Component {
  static propTypes = {
    className: PropTypes.string,
    onClose: PropTypes.func.isRequired,
    onSubmit: PropTypes.func.isRequired,
  };

  static defaultProps = {
    className: undefined,
  };

  static defaultState = {
    reason: undefined,
  };

  constructor(props) {
    super(props);
    this.state = RejectPaymentModal.defaultState;
  }

  hide = () => {
    const { onClose } = this.props;
    this.setState(RejectPaymentModal.defaultState);
    onClose();
  };

  render() {
    const {
      className,
      onSubmit,
    } = this.props;
    const { reason } = this.state;
    const props = _.omit(this.props, _.keys(RejectPaymentModal.propTypes));
    return (
      <Modal
        className={cn('reject-payment-modal', className)}
        backdrop="static"
        {...props}
      >
        <Modal.Header>
          <CloseButton onClick={this.hide} />
          <Modal.Title>반려</Modal.Title>
        </Modal.Header>

        <Modal.Body>
          <FormElement
            type={FormElement.Types.TEXTAREA}
            value={reason}
            placeholder="반려 사유를 적어주세요."
            focusOnMount
            onChange={value => this.setState({ reason: value })}
          />
        </Modal.Body>

        <Modal.Footer>
          <Button onClick={this.hide}>취소</Button>
          <Button bsStyle="primary" onClick={() => onSubmit(reason)}>확인</Button>
        </Modal.Footer>
      </Modal>
    );
  }
}
