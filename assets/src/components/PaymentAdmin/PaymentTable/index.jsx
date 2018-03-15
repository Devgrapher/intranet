import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment';
import { DropdownButton, Glyphicon, MenuItem } from 'react-bootstrap';
import { formatCurrency, formatDate } from '../../../utils';
import EditableTable from './EditableTable';
import RejectPaymentModal from './RejectPaymentModal';
import './style.less';

export default class PaymentTable extends React.Component {
  static propTypes = {
    data: PropTypes.objectOf(PropTypes.any),
    fetching: PropTypes.objectOf(PropTypes.any),
    filterString: PropTypes.string,
    onSelectFile: PropTypes.func,
    onRemoveFileButtonClick: PropTypes.func,
    onPaymentChange: PropTypes.func,
    onPaymentReject: PropTypes.func,
    onPaymentRemove: PropTypes.func,
  };

  static defaultProps = {
    data: {},
    fetching: {},
    filterString: undefined,
    onSelectFile: () => {},
    onRemoveFileButtonClick: () => {},
    onPaymentChange: () => {},
    onPaymentReject: () => {},
    onPaymentRemove: () => {},
  };

  static defaultState = {
    rejectPaymentModal: {
      show: false,
      paymentId: undefined,
    },
  };

  constructor(props) {
    super(props);
    this.state = PaymentTable.defaultState;
  }

  getColumns() {
    const {
      data: pageData,
      onRemoveFileButtonClick,
      onPaymentChange,
      onPaymentRemove,
    } = this.props;

    return [
      {
        key: 'request_date',
        displayName: '요청일',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          editable: false,
        }),
        renderDataCell: payment => formatDate(payment.request_date),
      },
      {
        key: 'register_name',
        displayName: '요청자',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          editable: false,
        }),
      },
      {
        key: 'is_manager_accepted',
        className: 'manager',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            manager_uid: {
              value: payment.manager_uid,
              type: 'select',
              options: _.map(pageData.allCurrentUsers, value => ({
                value: value.uid,
                label: value.name,
              })),
              placeholder: '승인자',
            },
            is_manager_accepted: {
              value: !!payment.is_manager_accepted,
              type: 'checkbox',
              label: '승인',
              isVisible: data => (
                data.manager_uid.value === pageData.user.uid && !payment.is_manager_accepted
              ),
            },
          },
          onBeforeSubmit: (data) => {
            if (data.is_manager_accepted && !payment.is_manager_accepted) {
              if (!window.confirm('승인하시겠습니까?')) {
                return false;
              }
            }

            const newData = [];
            if (data.manager_uid !== payment.manager_uid) {
              newData.push({ manager_uid: data.manager_uid });
            }
            if (data.is_manager_accepted) {
              if (data.manager_uid === pageData.user.uid) {
                newData.push({ is_manager_accepted: data.is_manager_accepted });
              }
            }

            return newData;
          },
        }),
        renderHeaderCell: () => (
          <React.Fragment>
            승인자 <small>확인일자</small>
          </React.Fragment>
        ),
        renderDataCell: payment => (
          payment.is_manager_accepted ? (
            <span className="label label-success">
              <span className="positive glyphicon glyphicon-ok" />
              <span className="name">{payment.manager_name}</span>
              <span className="accepted_date">
                {formatDate(payment.manger_accept.created_datetime)}
              </span>
            </span>
          ) : (
            <span className="label label-default">
              <span className="negative glyphicon glyphicon-remove" />
              <span className="name">{payment.manager_name}</span>
            </span>
          )
        ),
      },
      {
        key: 'is_account_book_registered',
        displayName: '장부',
        className: 'account_book_registered',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            is_account_book_registered: {
              value: payment.is_account_book_registered === 'Y',
              type: 'checkbox',
              label: '장부반영',
            },
          },
          onBeforeSubmit: data => ({
            is_account_book_registered: data.is_account_book_registered ? 'Y' : 'N',
          }),
        }),
        renderDataCell: payment => (
          payment.is_account_book_registered === 'Y' && (
            <span className="positive glyphicon glyphicon-ok" />
          )
        ),
      },
      {
        key: 'status',
        displayName: '결제자',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          editable: false,
        }),
        renderHeaderCell: () => (
          <React.Fragment>
            결제자 <small>확인일자</small>
          </React.Fragment>
        ),
        renderDataCell: payment => (
          payment.is_co_accepted && (
            <span className="label label-success">
              <span className="positive glyphicon glyphicon-ok" />
              <span className="acceptor_name">{payment.co_accpeter_name}</span>
              <span className="accepted_date">{formatDate(payment.co_accept.created_datetime)}</span>
            </span>
          )
        ),
      },
      {
        key: 'month',
        displayName: '귀속월',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            month: {
              value: payment.month,
              type: 'month',
            },
          },
          inline: false,
        }),
      },
      {
        key: 'team',
        displayName: '귀속부서',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            team: {
              value: payment.team,
              type: 'select',
              options: _.map(pageData.const.team, value => ({
                value,
                label: value,
              })),
              placeholder: '귀속부서',
            },
            team_detail: {
              value: payment.team_detail,
              type: 'select',
              options: _.map(pageData.const.team_detail, value => ({
                value,
                label: value,
              })),
              placeholder: '부서 세부분류',
            },
          },
          inline: false,
        }),
        renderHeaderCell: undefined,
        renderDataCell: (payment) => {
          const name = _.trim(_.split(payment.team, '/', 1)[0]);
          const subName = _.trim(_.trimStart(payment.team, name), ' /');
          return (
            <div className={cn('content', { 'no-sub-name': !subName })}>
              <span className="name">{name}</span>
              <span className="sub">
                {subName && (
                  <span className="name">{subName}</span>
                )}
                {payment.team_detail && (
                  <span className="detail">{payment.team_detail}</span>
                )}
              </span>
            </div>
          );
        },
      },
      {
        key: 'product',
        displayName: '프로덕트',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            product: {
              value: payment.product,
              type: 'select',
              options: _.map(pageData.const.product, value => ({
                value,
                label: value,
              })),
              placeholder: '프로덕트',
            },
          },
        }),
      },
      {
        key: 'category',
        displayName: '분류',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            category: {
              value: payment.category,
              type: 'select',
              options: _.map(pageData.const.category, value => ({
                value,
                label: value,
              })),
              placeholder: '분류',
            },
          },
        }),
        renderHeaderCell: undefined,
        renderDataCell: (payment) => {
          const name = _.split(payment.category, '(', 1)[0];
          const description = _.trimStart(payment.category, name);
          return (
            <React.Fragment>
              <span className="name">{_.trim(name)}</span>
              <small className="description">{_.trim(description, ' ()')}</small>
            </React.Fragment>
          );
        },
      },
      {
        key: 'desc',
        displayName: '상세내역',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            desc: {
              value: payment.desc,
              type: 'textarea',
              placeholder: '상세내역',
            },
          },
          inline: false,
        }),
      },
      {
        key: 'files',
        displayName: '파일',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          editable: false,
          fetching: this.isFetching(payment.paymentid, 'files'),
        }),
        renderDataCell: payment => (
          <div className="btn-group btn-group-xs">
            {_.isEmpty(payment.files) ? (
              <button
                className="upload btn btn-default"
                onClick={() => { this.openFileDialog(payment.paymentid); }}
              >
                <span className="glyphicon glyphicon-plus" />
              </button>
            ) : (
              <React.Fragment>
                <button
                  className="download btn dropdown-toggle btn-primary"
                  data-toggle="dropdown"
                >
                  <span className="glyphicon glyphicon-file" />
                  {_.size(payment.files) > 1 ? _.size(payment.files) : ' '}
                  <span className="caret" />
                </button>
                <ul className="dropdown-menu">
                  {_.map(payment.files, file => (
                    <li key={file.id} className="file">
                      <a
                        className="name"
                        href={`/payments/file/${file.id}`}
                      >
                        {file.original_filename}
                      </a>
                      <button
                        className="remove btn btn-xs btn-link"
                        onClick={() => {
                          onRemoveFileButtonClick(
                            payment.paymentid,
                            file.id,
                            file.original_filename,
                          );
                        }}
                      >
                        <span className="glyphicon glyphicon-remove" />
                      </button>
                    </li>
                  ))}
                  <li role="separator" className="divider" />
                  <li>
                    <button
                      onClick={() => { this.openFileDialog(payment.paymentid); }}
                    >
                      <span className="glyphicon glyphicon-plus" /> 추가 업로드
                    </button>
                  </li>
                </ul>
              </React.Fragment>
            )}
          </div>
        ),
      },
      {
        key: 'company_name',
        displayName: '업체명',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            company_name: {
              value: payment.company_name,
              placeholder: '업체명',
            },
          },
        }),
      },
      {
        key: 'price',
        displayName: '입금금액',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            price: {
              value: payment.price,
              type: 'currency',
              decimalScale: 2,
              placeholder: '입금금액',
            },
          },
        }),
        renderDataCell: (payment) => {
          const priceStrings = formatCurrency(payment.price).split('.');
          const decimal = priceStrings[0];
          const fractional = priceStrings[1];
          return (
            <div className="value">
              <span className="decimal">{decimal}</span>
              <span className={cn('fractional', { empty: !fractional })}>
                .{fractional || '00'}
              </span>
            </div>
          );
        },
      },
      {
        key: 'pay_date',
        displayName: '결제예정일',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            pay_date: {
              value: formatDate(payment.pay_date),
              type: 'date',
            },
          },
          inline: false,
        }),
        renderDataCell: payment => formatDate(payment.pay_date),
      },
      {
        key: 'tax',
        displayName: '세금계산서',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            tax: {
              value: payment.tax === 'Y',
              type: 'checkbox',
              label: '수취여부',
            },
            tax_date: {
              value: payment.tax_date || moment().format('YYYY-MM-DD'),
              type: 'date',
              isVisible: data => data.tax.value,
            },
          },
          inline: false,
          onBeforeSubmit: data => ({
            tax: data.tax ? 'Y' : 'N/A',
            tax_date: data.tax ? data.tax_date : null,
          }),
        }),
        renderDataCell: payment => (
          payment.tax === 'Y' && (
            <span className="label label-success">
              <span className="positive glyphicon glyphicon-ok" />
              <span className="tax_date">
                {payment.tax_date}
              </span>
            </span>
          )
        ),
      },
      {
        key: 'bank',
        displayName: '입금은행',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            bank: {
              value: payment.bank,
              placeholder: '입금은행',
            },
          },
        }),
      },
      {
        key: 'bank_account',
        displayName: '입금계좌번호',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            bank_account: {
              value: payment.bank_account,
              placeholder: '입금계좌번호',
            },
          },
        }),
      },
      {
        key: 'bank_account_owner',
        displayName: '예금주',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            bank_account_owner: {
              value: payment.bank_account_owner,
              placeholder: '예금주',
            },
          },
        }),
      },
      {
        key: 'note',
        displayName: '비고',
        sortable: true,
        getDataCellProps: payment => this.createDataCellProps(payment, {
          data: {
            note: {
              value: payment.note,
              type: 'textarea',
              placeholder: '비고',
            },
          },
          inline: false,
        }),
      },
      {
        key: 'buttons',
        renderHeaderCell: () => {},
        getDataCellProps: payment => ({
          editable: false,
          fetching: this.isFetching(payment.paymentid, [
            'is_co_accepted',
            'is_manager_accepted',
            'reject',
            'remove',
          ]),
        }),
        renderDataCell: payment => (
          <DropdownButton
            id={`dropdown[${payment.paymentid}]`}
            title={<Glyphicon glyph="option-vertical" />}
            bsSize="xsmall"
            noCaret
            pullRight
          >
            {payment.is_manager_accepted && !payment.is_co_accepted && (
              <React.Fragment>
                <MenuItem
                  className="accept"
                  onSelect={() => {
                    if (window.confirm('승인하시겠습니까?')) {
                      onPaymentChange(payment.paymentid, { is_co_accepted: true });
                    }
                  }}
                >
                  <Glyphicon glyph="ok" /> 결제 완료
                </MenuItem>

                <MenuItem
                  className="reject"
                  onSelect={() => this.showRejectPaymentModal(payment.paymentid)}
                >
                  <Glyphicon glyph="ban-circle" /> 반려
                </MenuItem>
              </React.Fragment>
            )}
            <MenuItem
              className="remove"
              onSelect={() => {
                if (window.confirm(`\
정말 삭제하시겠습니까?

업체명 : ${payment.company_name}
금액 : ${formatCurrency(payment.price)}`)
                ) {
                  onPaymentRemove(payment.paymentid);
                }
              }}
            >
              <Glyphicon glyph="remove" /> 삭제
            </MenuItem>
          </DropdownButton>
        ),
      },
    ];
  }

  createDataCellProps(payment, props = {}) {
    const { onPaymentChange } = this.props;
    return {
      fetching: this.isFetching(payment.paymentid, _.keys(props.data)),
      onSubmit: data => onPaymentChange(payment.paymentid, data),
      ...props,
    };
  }

  isFetching(paymentId, keys) {
    return _.some(_.castArray(keys), key => (
      _.get(this.props.fetching, ['payments', paymentId, key])
    ));
  }

  openFileDialog(paymentId) {
    const { onSelectFile } = this.props;
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('multiple', '');
    input.addEventListener('change', (e) => { onSelectFile(e.target.files, paymentId); });
    input.click();
  }

  showRejectPaymentModal = paymentId => this.setState({
    rejectPaymentModal: {
      show: true,
      paymentId,
    },
  });

  hideRejectPaymentModal = () => this.setState({
    rejectPaymentModal: PaymentTable.defaultState.rejectPaymentModal,
  });

  renderRejectPaymentModal() {
    const {
      rejectPaymentModal: {
        show,
        paymentId,
      },
    } = this.state;
    return (
      <RejectPaymentModal
        show={show}
        onClose={this.hideRejectPaymentModal}
        onSubmit={(reason) => {
          const { onPaymentReject } = this.props;
          onPaymentReject(paymentId, reason);
          this.hideRejectPaymentModal();
        }}
      />
    );
  }

  render() {
    const { data: { payments }, filterString } = this.props;
    return (
      <React.Fragment>
        <EditableTable
          className="payment-table component"
          columns={this.getColumns()}
          rows={payments}
          filterString={filterString}
          renderEmptyContent={() => '내역이 없습니다.'}
        />
        {this.renderRejectPaymentModal()}
      </React.Fragment>
    );
  }
}
