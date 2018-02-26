import React from 'react';
import _ from 'lodash';
import cn from 'classnames';
import sequence from 'promise-sequence';
import api from '../../api/payment';
import QuerySelector from './QuerySelector';
import QueryButtonGroup from './QueryButtonGroup';
import PaymentTable from './PaymentTable';
import { parseNumber } from '../../utils';
import './style.less';

export default class PaymentAdmin extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      data: undefined,
      query: undefined,
      loadedQuery: undefined,
      fetching: {
        all: false,
        payments: {},
      },
    };
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

  addAttachmentFile = async (files, paymentId) => {
    try {
      this.setFetching(`payments[${paymentId}].file`, true);
      await api.addAttachmentFile(files[0], paymentId);
      await this.reload(true);
    } catch (err) {
      alert('파일을 업로드하지 못했습니다.');
    } finally {
      this.setFetching(`payments[${paymentId}].file`, false);
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

      this.setState({ data, loadedQuery: query });
    } catch (err) {
      alert('정보를 불러오지 못했습니다.');
    } finally {
      if (!omitSetFetching) {
        this.setFetching('all', false);
      }
    }
  };

  reload = async (omitSetFetching = false) => (
    this.request(this.state.loadedQuery, omitSetFetching)
  );

  handleQueryChange = async (path, params) => {
    const query = { path, params };
    this.setState({
      query,
    });
    if (!this.state.data) {
      await this.request(query);
    }
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
            결제 <small>{data && data.title}</small>
          </h1>

          <div className="query-selector-container">
            <QuerySelector
              teams={data ? data.const.team : []}
              categories={data ? data.const.category : []}

              todayQueuedCost={data ? data.todayQueuedCost : 0}
              todayQueuedCount={data ? data.todayQueuedCount : 0}
              todayConfirmedQueuedCost={data ? data.todayConfirmedQueuedCost : 0}
              todayConfirmedQueuedCount={data ? data.todayConfirmedQueuedCount : 0}
              todayUnconfirmedQueuedCost={data ? data.todayUnconfirmedQueuedCost : 0}
              todayUnconfirmedQueuedCount={data ? data.todayUnconfirmedQueuedCount : 0}

              onQueryChange={this.handleQueryChange}
            />
            <QueryButtonGroup
              className={cn({ disabled: !query || fetching.all })}
              onQueryButtonClick={() => this.request(query)}
              onDownloadButtonClick={() => this.download(query, false)}
              onDownloadBankTransferOnlyButtonClick={() => this.download(query, true)}
            />
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
              onSelectFile={this.addAttachmentFile}
              onRemoveFileButtonClick={this.removeAttachmentFile}
              onPaymentChange={this.handlePaymentChange}
              onPaymentRemove={this.handlePaymentRemove}
            />
          )}
        </div>
      </div>
    );
  }
}
