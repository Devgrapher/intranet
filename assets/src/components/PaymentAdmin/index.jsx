import React from 'react';
import createHistory from 'history/createBrowserHistory';
import queryString from 'query-string';
import _ from 'lodash';
import cn from 'classnames';
import sequence from 'promise-sequence';
import { Modal, CloseButton, Button, ProgressBar } from 'react-bootstrap';
import api from '../../api/payment';
import QuerySelector from './QuerySelector';
import QueryButtonGroup from './QueryButtonGroup';
import PaymentTable from './PaymentTable';
import PaymentForm from './PaymentForm';
import { parseNumber } from '../../utils';
import './style.less';

const history = createHistory({
  basename: '/admin/payment',
});

const locationToQuery = ({ pathname, search }) => ({
  path: pathname,
  params: queryString.parse(search),
});

const queryToPath = ({ path, params }) => `${path}?${queryString.stringify(params)}`;

const getCurrentQuery = () => locationToQuery(history.location);

export default class PaymentAdmin extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      data: undefined,
      query: getCurrentQuery(),
      fetching: {
        all: false,
        payments: {},
        newPayment: false,
      },
      showNewPayment: false,
    };

    this.unlistenHistory = history.listen(async (location) => {
      const query = locationToQuery(location);
      this.setState({ query });
      await this.request(query);
    });
  }

  componentWillUnmount() {
    this.unlistenHistory();
  }

  setFetching(path, value) {
    const paths = _.castArray(path);
    const fetching = { ...this.state.fetching };
    _.forEach(paths, (p) => {
      _.set(fetching, p, value);
    });
    this.setState({
      fetching,
    });
  }

  download = async ({ path, params }, bankTransferOnly = false) => {
    try {
      await api.download(path, {
        params: {
          bankTransferOnly: bankTransferOnly ? true : undefined,
          ...params,
        },
        headers: { Accept: 'text/csv' },
      });
    } catch (err) {
      alert('다운로드하지 못했습니다.');
    }
  };

  addAttachmentFiles = async (files, paymentId) => {
    try {
      this.setFetching(`payments[${paymentId}].files`, true);
      await api.addAttachmentFiles(files, paymentId);
      await this.reload(true);
    } catch (err) {
      alert('파일을 업로드하지 못했습니다.');
    } finally {
      this.setFetching(`payments[${paymentId}].files`, false);
    }
  };

  removeAttachmentFile = async (paymentId, fileId, fileName) => {
    if (!window.confirm(`'${fileName}'을 삭제하시겠습니까?`)) {
      return;
    }
    try {
      this.setFetching(`payments[${paymentId}].file`, true);
      await api.removeAttachmentFile(fileId);
      await this.reload(true);
    } catch (err) {
      alert('파일을 삭제하지 못했습니다.');
    } finally {
      this.setFetching(`payments[${paymentId}].file`, false);
    }
  };

  request = async (query, omitSetFetching = false) => {
    if (!omitSetFetching) {
      this.setFetching('all', true);
    }
    try {
      const { path, params } = query;

      const data = await api.get(path, { params });
      data.todayQueuedCost = parseNumber(data.todayQueuedCost);
      data.todayConfirmedQueuedCost = parseNumber(data.todayConfirmedQueuedCost);
      data.todayUnconfirmedQueuedCost = parseNumber(data.todayUnconfirmedQueuedCost);

      this.setState({ data });
    } catch (err) {
      alert('정보를 불러오지 못했습니다.');
    } finally {
      if (!omitSetFetching) {
        this.setFetching('all', false);
      }
    }
  };

  reload = async (omitSetFetching = false) => (
    this.request(getCurrentQuery(), omitSetFetching)
  );

  showNewPayment = () => this.setState({ showNewPayment: true });

  hideNewPayment = () => this.setState({ showNewPayment: false });

  handleQueryChange = async (query) => {
    this.setState({ query });
    if (!this.state.data) {
      history.replace(queryToPath(query));
    }
  };

  handleQueryButtonClick = async () => {
    const { query } = this.state;
    const path = queryToPath(query);
    if (_.isEqual(query, getCurrentQuery())) {
      history.replace(path);
      return;
    }
    history.push(path);
  };

  handlePaymentChange = async (paymentId, data) => {
    const dataArray = _.castArray(data);
    const fetchPaths = _.flatten(_.map(dataArray, d => (
      _.map(d, (value, key) => (
        `payments[${paymentId}][${key}]`
      ))
    )));

    try {
      this.setFetching(fetchPaths, true);
      await sequence(_.map(dataArray, d => (
        Promise.all(_.map(d, (value, key) => (
          api.update(paymentId, key, value)
        )))
      )));
    } catch (err) {
      alert((err.response && err.response.data) || err.message);
    } finally {
      await this.reload(true);
      this.setFetching(fetchPaths, false);
    }
  };

  handlePaymentRemove = async (paymentId) => {
    const fetchPath = `payments[${paymentId}].remove`;
    try {
      this.setFetching(fetchPath, true);
      await api.remove(paymentId);
    } catch (err) {
      alert((err.response && err.response.data) || err.message);
    } finally {
      await this.reload(true);
      this.setFetching(fetchPath, false);
    }
  };

  handleNewPaymentSubmit = async ({ uid, files, ...data }) => {
    const fetchPath = 'newPayment';
    try {
      this.setFetching(fetchPath, true);
      await api.add(uid, data, files);
      this.hideNewPayment();
    } catch (err) {
      alert((err.response && err.response.data) || err.message);
    } finally {
      await this.reload(true);
      this.setFetching(fetchPath, false);
    }
  };

  renderNewPaymentModal() {
    const {
      data,
      fetching,
    } = this.state;
    return (
      <Modal
        className="new-payment"
        show={this.state.showNewPayment}
        backdrop="static"
        onHide={this.hideNewPayment}
      >
        <Modal.Header>
          {!fetching.newPayment && <CloseButton onClick={this.hideNewPayment} />}
          <Modal.Title>새 결제</Modal.Title>
        </Modal.Header>

        <Modal.Body>
          <PaymentForm
            ref={(c) => { this.newPaymentForm = c; }}
            user={data && data.user}
            users={data && data.allCurrentUsers}
            teams={data && data.const.team}
            teamDetails={data && data.const.team_detail}
            products={data && data.const.product}
            categories={data && data.const.category}
            fetching={fetching.newPayment}
            onSubmit={this.handleNewPaymentSubmit}
          />
        </Modal.Body>

        <Modal.Footer>
          {fetching.newPayment ? (
            <ProgressBar active now={100} label="저장중.." />
          ) : (
            <React.Fragment>
              <Button onClick={this.hideNewPayment}>닫기</Button>
              <Button bsStyle="primary" onClick={() => this.newPaymentForm.submit()}>추가</Button>
            </React.Fragment>
          )}
        </Modal.Footer>
      </Modal>
    );
  }

  render() {
    const {
      query,
      data,
      fetching,
    } = this.state;
    return (
      <div className="payment-admin component container-fluid">
        <div className="page-header">
          <h1 className="title">
            결제 <small>{!fetching.all && data && data.title}</small>
          </h1>

          <div className="toolbar">
            <div className="query-selector-container">
              <QuerySelector
                data={data}
                query={query}
                onQueryChange={this.handleQueryChange}
              />
              <QueryButtonGroup
                className={cn({ disabled: !query || fetching.all })}
                onQueryButtonClick={this.handleQueryButtonClick}
                onDownloadButtonClick={() => this.download(query, false)}
                onDownloadBankTransferOnlyButtonClick={() => this.download(query, true)}
              />
            </div>
            <button
              className="add-payment btn btn-sm btn-success"
              onClick={this.showNewPayment}
            >
              <span className="glyphicon glyphicon-plus" /> 추가
            </button>
          </div>
        </div>

        <div className="payment-table-container">
          {fetching.all ? (
            <div className="progress">
              <div
                className="progress-bar progress-bar-striped active"
                role="progressbar"
                aria-valuenow="100"
                aria-valuemin="0"
                aria-valuemax="100"
                style={{ width: '100%' }}
              >
                로딩중..
              </div>
            </div>
          ) : (
            <PaymentTable
              data={data}
              fetching={fetching}
              onSelectFile={this.addAttachmentFiles}
              onRemoveFileButtonClick={this.removeAttachmentFile}
              onPaymentChange={this.handlePaymentChange}
              onPaymentRemove={this.handlePaymentRemove}
            />
          )}
        </div>

        {this.renderNewPaymentModal()}
      </div>
    );
  }
}
