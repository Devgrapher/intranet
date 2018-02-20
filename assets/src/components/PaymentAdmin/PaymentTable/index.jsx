import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import cn from 'classnames';
import moment from 'moment';
import { formatCurrency, formatDate } from '../../../utils';
import EditableCell from './EditableCell';
import './style.less';

export default class PaymentTable extends React.Component {
  static propTypes = {
    data: PropTypes.objectOf(PropTypes.any),
    fetching: PropTypes.objectOf(PropTypes.any),
    onSelectFile: PropTypes.func,
    onRemoveFileButtonClick: PropTypes.func,
    onPaymentChange: PropTypes.func,
  };

  static defaultProps = {
    data: {},
    fetching: {},
    onSelectFile: () => {},
    onRemoveFileButtonClick: () => {},
    onPaymentChange: () => {},
  };

  fileInputs = {};

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

  renderHead() {
    return (
      <thead>
        <tr>
          <th className="request-date">요청일</th>
          <th className="register-name">요청자</th>
          <th className="manager">승인자 <small>확인일자</small></th>
          <th className="account-book-registered">장부</th>

          <th className="month">귀속월</th>
          <th className="team">귀속부서</th>
          <th className="product">프로덕트</th>
          <th className="category">분류</th>
          <th className="desc">상세내역</th>
          <th className="file">파일</th>

          <th className="company-name">업체명</th>
          <th className="price">입금금액</th>
          <th className="pay-date">결제예정일</th>

          <th className="tax">세금계산서</th>

          <th className="bank">입금은행</th>
          <th className="bank-account">입금계좌번호</th>
          <th className="bank-account-owner">예금주</th>

          <th className="note">비고</th>

          <th className="status">결제자 <small>확인일자</small></th>
        </tr>
      </thead>
    );
  }

  renderBody() {
    const {
      data: pageData,
      onSelectFile,
      onRemoveFileButtonClick,
      onPaymentChange,
    } = this.props;
    if (_.isEmpty(pageData.payments)) {
      return (
        <tbody>
          <tr className="empty">
            <td colSpan="19">
              내역이 없습니다.
            </td>
          </tr>
        </tbody>
      );
    }
    return (
      <tbody>
        {_.map(pageData.payments, (payment, key) => {
          const fetching = _.get(this.props.fetching, `payments[${payment.paymentid}]`);
          return (
            <tr key={key}>
              <td className="request-date">{formatDate(payment.request_date)}</td>
              <td className="register-name">{payment.register_name}</td>
              <EditableCell
                className="manager"
                data={{
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
                }}
                fetching={_.get(fetching, 'manager_uid') || _.get(fetching, 'is_manager_accepted')}
                onSubmit={(data) => {
                  if (data.is_manager_accepted && !payment.is_manager_accepted) {
                    if (!window.confirm('승인하시겠습니까?')) {
                      return;
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

                  onPaymentChange(payment.paymentid, newData);
                }}
              >
                {payment.is_manager_accepted ? (
                  <span className="label label-success">
                    <span className="positive glyphicon glyphicon-ok" />
                    <span className="name">{payment.manager_name}</span>
                    <span className="accepted-date">
                      {formatDate(payment.manger_accept.created_datetime)}
                    </span>
                  </span>
                ) : (
                  <span className="label label-default">
                    <span className="negative glyphicon glyphicon-remove" />
                    <span className="name">{payment.manager_name}</span>
                  </span>
                )}
              </EditableCell>
              <EditableCell
                className="account-book-registered"
                data={{
                  is_account_book_registered: {
                    value: payment.is_account_book_registered === 'Y',
                    type: 'checkbox',
                    label: '장부반영',
                  },
                }}
                fetching={_.get(fetching, 'is_account_book_registered')}
                onSubmit={(data) => {
                  onPaymentChange(payment.paymentid, {
                    is_account_book_registered: data.is_account_book_registered ? 'Y' : 'N',
                  });
                }}
              >
                {payment.is_account_book_registered === 'Y' ? (
                  <span className="positive glyphicon glyphicon-ok" />
                ) : (
                  <span className="negative glyphicon glyphicon-remove" />
                )}
              </EditableCell>

              <EditableCell
                className="month"
                data={{
                  month: {
                    value: payment.month,
                    type: 'month',
                  },
                }}
                inline={false}
                fetching={_.get(fetching, 'month')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.month}
              </EditableCell>
              <EditableCell
                className="team"
                data={{
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
                }}
                fetching={_.get(fetching, 'team') || _.get(fetching, 'team_detail')}
                inline={false}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {(() => {
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
                })()}
              </EditableCell>
              <EditableCell
                className="product"
                data={{
                  product: {
                    value: payment.product,
                    type: 'select',
                    options: _.map(pageData.const.product, value => ({
                      value,
                      label: value,
                    })),
                    placeholder: '프로덕트',
                  },
                }}
                fetching={_.get(fetching, 'product')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.product}
              </EditableCell>
              <EditableCell
                className="category"
                data={{
                  category: {
                    value: payment.category,
                    type: 'select',
                    options: _.map(pageData.const.category, value => ({
                      value,
                      label: value,
                    })),
                    placeholder: '분류',
                  },
                }}
                fetching={_.get(fetching, 'category')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {(() => {
                  const name = _.split(payment.category, '(', 1)[0];
                  const description = _.trimStart(payment.category, name);
                  return (
                    <React.Fragment>
                      <span className="name">{_.trim(name)}</span>
                      <small className="description">{_.trim(description, ' ()')}</small>
                    </React.Fragment>
                  );
                })()}
              </EditableCell>
              <EditableCell
                className="desc"
                data={{
                  desc: {
                    value: payment.desc,
                    type: 'textarea',
                    placeholder: '상세내역',
                  },
                }}
                inline={false}
                fetching={_.get(fetching, 'desc')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.desc}
              </EditableCell>
              <td className="file">
                <input
                  ref={(ref) => { this.fileInputs[payment.paymentid] = ref; }}
                  type="file"
                  onChange={(e) => { onSelectFile(e.target.files, payment.paymentid); }}
                />
                {_.get(fetching, 'file') ? (
                  this.renderProgress()
                ) : (
                  <div className="btn-group btn-group-xs">
                    {_.isEmpty(payment.files) ? (
                      <button
                        className="upload btn btn-default"
                        onClick={() => { this.fileInputs[payment.paymentid].click(); }}
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
                              onClick={() => { this.fileInputs[payment.paymentid].click(); }}
                            >
                              <span className="glyphicon glyphicon-plus" /> 추가 업로드
                            </button>
                          </li>
                        </ul>
                      </React.Fragment>
                    )}
                  </div>
                )}
              </td>

              <EditableCell
                className="company-name"
                data={{
                  company_name: {
                    value: payment.company_name,
                    placeholder: '업체명',
                  },
                }}
                fetching={_.get(fetching, 'company_name')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.company_name}
              </EditableCell>
              <EditableCell
                className="price"
                data={{
                  price: {
                    value: payment.price,
                    type: 'currency',
                    precision: 2,
                    placeholder: '입금금액',
                  },
                }}
                fetching={_.get(fetching, 'price')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {(() => {
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
                })()}
              </EditableCell>
              <EditableCell
                className="pay-date"
                data={{
                  pay_date: {
                    value: formatDate(payment.pay_date),
                    type: 'date',
                  },
                }}
                inline={false}
                fetching={_.get(fetching, 'pay_date')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {formatDate(payment.pay_date)}
              </EditableCell>

              <EditableCell
                className="tax"
                data={{
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
                }}
                inline={false}
                fetching={_.get(fetching, 'tax') || _.get(fetching, 'tax_date')}
                onSubmit={(data) => {
                  onPaymentChange(payment.paymentid, {
                    tax: data.tax ? 'Y' : 'N/A',
                    tax_date: data.tax ? data.tax_date : null,
                  });
                }}
              >
                {payment.tax === 'Y' ? (
                  <span className="label label-success">
                    <span className="positive glyphicon glyphicon-ok" />
                    <span className="tax-date">
                      {payment.tax_date}
                    </span>
                  </span>
                ) : (
                  <span className="negative glyphicon glyphicon-remove" />
                )}
              </EditableCell>

              <EditableCell
                className="bank"
                data={{
                  bank: {
                    value: payment.bank,
                    placeholder: '입금은행',
                  },
                }}
                fetching={_.get(fetching, 'bank')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.bank}
              </EditableCell>
              <EditableCell
                className="bank-account"
                data={{
                  bank_account: {
                    value: payment.bank_account,
                    placeholder: '입금계좌번호',
                  },
                }}
                fetching={_.get(fetching, 'bank_account')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.bank_account}
              </EditableCell>
              <EditableCell
                className="bank-account-owner"
                data={{
                  bank_account_owner: {
                    value: payment.bank_account_owner,
                    placeholder: '예금주',
                  },
                }}
                fetching={_.get(fetching, 'bank_account_owner')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.bank_account_owner}
              </EditableCell>

              <EditableCell
                className="note"
                data={{
                  note: {
                    value: payment.note,
                    type: 'textarea',
                    placeholder: '비고',
                  },
                }}
                inline={false}
                fetching={_.get(fetching, 'note')}
                onSubmit={(data) => { onPaymentChange(payment.paymentid, data); }}
              >
                {payment.note}
              </EditableCell>

              <EditableCell
                className="status"
                editable={!payment.is_co_accepted}
                data={{
                  is_co_accepted: {
                    value: !!payment.is_co_accepted,
                    type: 'checkbox',
                    label: '결제 완료',
                  },
                }}
                fetching={_.get(fetching, 'is_co_accepted') || _.get(fetching, 'status')}
                onSubmit={(data) => {
                  if (!data.is_co_accepted || !window.confirm('승인하시겠습니까?')) {
                    return;
                  }
                  onPaymentChange(payment.paymentid, data);
                }}
              >
                {payment.is_co_accepted ? (
                  <span className="label label-success">
                    <span className="positive glyphicon glyphicon-ok" />
                    <span className="acceptor-name">{payment.co_accpeter_name}</span>
                    <span className="accepted-date">{formatDate(payment.co_accept.created_datetime)}</span>
                  </span>
                ) : (
                  <span className="negative glyphicon glyphicon-remove" />
                )}
              </EditableCell>
            </tr>
          );
        })}
      </tbody>
    );
  }

  render() {
    return (
      <table className="payment-table table table-condensed table-striped table-hover">
        {this.renderHead()}
        {this.renderBody()}
      </table>
    );
  }
}
