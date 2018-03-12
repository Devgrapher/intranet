import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import _ from 'lodash';
import moment from 'moment';
import {
  Button, Col, ControlLabel, Form, FormGroup, ListGroup,
  ListGroupItem,
} from 'react-bootstrap';
import Dropzone from 'react-dropzone';
import FormElement from '../FormElement';
import './style.less';

export default class PaymentForm extends React.Component {
  static propTypes = {
    user: PropTypes.shape({
      uid: PropTypes.number,
      name: PropTypes.string,
      team: PropTypes.string,
    }),
    users: PropTypes.arrayOf(PropTypes.shape({
      uid: PropTypes.number,
      name: PropTypes.string,
      team: PropTypes.string,
    })),
    teams: PropTypes.arrayOf(PropTypes.string),
    teamDetails: PropTypes.arrayOf(PropTypes.string),
    products: PropTypes.arrayOf(PropTypes.string),
    categories: PropTypes.arrayOf(PropTypes.string),
    fetching: PropTypes.bool,

    onSubmit: PropTypes.func,
  };

  static defaultProps = {
    user: undefined,
    users: undefined,
    teams: undefined,
    teamDetails: undefined,
    products: undefined,
    categories: undefined,
    fetching: false,

    onSubmit: () => {},
  };

  constructor(props) {
    super(props);
    const { user = {} } = props;
    this.state = {
      data: {
        uid: user.uid,
        manager_uid: undefined,
        is_account_book_registered: 'N',
        month: moment().format('YYYY-MM'),
        team: user.team,
        team_detail: undefined,
        product: undefined,
        category: undefined,
        desc: '',
        company_name: '',
        price: '',
        pay_date: moment().format('YYYY-MM-DD'),
        tax: 'N/A',
        tax_date: undefined,
        bank: '',
        bank_account: '',
        bank_account_owner: '',
        note: '',
        files: [],
      },
    };
  }

  componentWillReceiveProps(nextProps) {
    const { user } = nextProps;
    if (!_.isEqual(user, this.props.user)) {
      this.updateData('uid', user.uid);
    }
  }

  updateData(key, value) {
    const data = {
      ...this.state.data,
      [key]: value,
    };
    switch (key) {
      case 'uid': {
        const user = _.find(this.props.users, ({ uid }) => uid === value) || {};
        data.team = user.team;
        break;
      }
      case 'tax': {
        data.tax_date = value === 'Y' ? moment().format('YYYY-MM-DD') : undefined;
        break;
      }
      default:
        break;
    }
    this.setState({ data });
  }

  handleFilesDrop = (files) => {
    this.setState({
      data: {
        ...this.state.data,
        files: [
          ...this.state.data.files,
          ...files,
        ],
      },
    });
  };

  removeFile = (file) => {
    this.setState({
      data: {
        ...this.state.data,
        files: _.without(this.state.data.files, file),
      },
    });
  };

  submit = () => {
    ReactDOM.findDOMNode(this.submitButton).click();
  };

  renderPaymentElement = ({ name, children, ...props }) => {
    const { fetching } = this.props;
    const { data } = this.state;
    return (
      <FormElement
        name={name}
        value={data[name]}
        disabled={fetching}
        onChange={v => this.updateData(name, v || '')}
        {...props}
      >
        {children}
      </FormElement>
    );
  };

  renderFieldGroup = ({
    label,
    name,
    children,
    ...props
  }) => {
    const PaymentElement = this.renderPaymentElement;
    return (
      <FormGroup className={name}>
        <Col componentClass={ControlLabel} sm={3}>{label}</Col>
        <Col className="controls-container" sm={9}>
          {children || (
            <PaymentElement
              name={name}
              placeholder={label}
              {...props}
            />
          )}
        </Col>
      </FormGroup>
    );
  };

  render() {
    const {
      users,
      teams,
      teamDetails,
      products,
      categories,
      fetching,

      onSubmit,
    } = this.props;
    const { data } = this.state;
    const props = _.omit(this.props, _.keys(PaymentForm.propTypes));
    const FieldGroup = this.renderFieldGroup;
    const PaymentElement = this.renderPaymentElement;
    return (
      <Form
        className="payment-form component"
        horizontal
        onSubmit={(e) => {
          e.preventDefault();
          onSubmit(_.omitBy(this.state.data, _.isNil));
        }}
        {...props}
      >
        <FieldGroup
          label="요청자"
          name="uid"
          type={FormElement.Types.SELECT}
          options={_.map(users, v => ({ label: v.name, value: v.uid }))}
        />
        <FieldGroup
          label="승인자"
          name="manager_uid"
          type={FormElement.Types.SELECT}
          options={_.map(users, v => ({ label: v.name, value: v.uid }))}
        />
        <FieldGroup
          label="장부"
          name="is_account_book_registered"
        >
          <PaymentElement
            name="is_account_book_registered"
            type={FormElement.Types.CHECKBOX}
            value={data.is_account_book_registered === 'Y'}
            onChange={v => this.updateData('is_account_book_registered', v ? 'Y' : 'N')}
          >
            반영
          </PaymentElement>
        </FieldGroup>

        <FieldGroup
          label="귀속월"
          name="month"
          type={FormElement.Types.MONTH}
        />
        <FieldGroup
          label="귀속부서"
          name="team"
          type={FormElement.Types.SELECT}
          options={_.map(teams, v => ({ label: v, value: v }))}
        />
        <FieldGroup
          label="부서 세부분류"
          name="team_detail"
          type={FormElement.Types.SELECT}
          options={_.map(teamDetails, v => ({ label: v, value: v }))}
        />
        <FieldGroup
          label="프로덕트"
          name="product"
          type={FormElement.Types.SELECT}
          options={_.map(products, v => ({ label: v, value: v }))}
        />
        <FieldGroup
          label="분류"
          name="category"
          type={FormElement.Types.SELECT}
          options={_.map(categories, v => ({ label: v, value: v }))}
        />
        <FieldGroup
          label="상세내역"
          name="desc"
          type={FormElement.Types.TEXTAREA}
        />
        <FieldGroup
          label="첨부파일"
          name="files"
        >
          <Dropzone
            ref={(c) => { this.dropzone = c; }}
            className="dropzone"
            multiple
            disableClick
            disabled={fetching}
            onDrop={this.handleFilesDrop}
          >
            <ListGroup>
              {_.map(data.files, (file, key) => (
                <ListGroupItem key={key} disabled={fetching}>
                  <span className="file-name">{file.name}</span>
                  <Button
                    className="remove-file"
                    bsSize="xs"
                    bsStyle="danger"
                    disabled={fetching}
                    onClick={() => this.removeFile(file)}
                  >
                    <span className="glyphicon glyphicon-remove" />
                  </Button>
                </ListGroupItem>
              ))}
              <ListGroupItem disabled={fetching} onClick={() => this.dropzone.open()}>
                <span className="glyphicon glyphicon-plus" /> 추가
              </ListGroupItem>
            </ListGroup>
          </Dropzone>
        </FieldGroup>

        <FieldGroup
          label="업체명"
          name="company_name"
        />
        <FieldGroup
          label="입금금액"
          name="price"
          type={FormElement.Types.CURRENCY}
          decimalScale={2}
        />
        <FieldGroup
          label="결제예정일"
          name="pay_date"
          type={FormElement.Types.DATE}
        />

        <FieldGroup
          label="세금계산서"
          name="tax"
        >
          <FormElement
            name="tax_date"
            type={FormElement.Types.DATE}
            value={data.tax_date}
            disabled={data.tax !== 'Y' || fetching}
            placeholder="세금계산서 일자"
            onChange={v => this.updateData('tax_date', v)}
          />
          <FormElement
            name="tax"
            type={FormElement.Types.CHECKBOX}
            value={data.tax === 'Y'}
            disabled={fetching}
            onChange={v => this.updateData('tax', v ? 'Y' : 'N/A')}
          >
            수취
          </FormElement>
        </FieldGroup>

        <FieldGroup
          label="입금은행"
          name="bank"
        />
        <FieldGroup
          label="입금계좌번호"
          name="bank_account"
        />
        <FieldGroup
          label="예금주"
          name="bank_account_owner"
        />

        <FieldGroup
          label="비고"
          name="note"
          type={FormElement.Types.TEXTAREA}
        />

        <FormGroup className="buttons">
          <Col smOffset={3} sm={9}>
            <Button
              ref={(c) => { this.submitButton = c; }}
              type="submit"
              bsStyle="primary"
              disabled={fetching}
            >
              추가
            </Button>
          </Col>
        </FormGroup>
      </Form>
    );
  }
}
