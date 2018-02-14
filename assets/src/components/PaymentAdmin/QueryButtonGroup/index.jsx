import React from 'react';
import PropTypes from 'prop-types';
import cn from 'classnames';
import './style.less';

export default class QueryButtonGroup extends React.Component {
  static propTypes = {
    className: PropTypes.string,
    onQueryButtonClick: PropTypes.func.isRequired,
    onDownloadButtonClick: PropTypes.func.isRequired,
    onDownloadBankTransferOnlyButtonClick: PropTypes.func.isRequired,
  };

  static defaultProps = {
    className: '',
  };

  renderQueryButton() {
    return (
      <button
        className="query btn btn-primary"
        type="button"
        onClick={this.props.onQueryButtonClick}
      >
        <span className="glyphicon glyphicon-search" aria-hidden />
        조회
      </button>
    );
  }

  renderDownloadButton() {
    return (
      <div className="btn-group btn-group-sm" role="group">
        <button
          className="download btn btn-primary dropdown-toggle"
          type="button"
          data-toggle="dropdown"
          aria-haspopup
          aria-expanded="false"
        >
          <span className="glyphicon glyphicon-download-alt" aria-hidden />
          &nbsp;
          <span className="caret" />
        </button>
        <ul className="download dropdown-menu dropdown-menu-right">
          <li>
            <button onClick={this.props.onDownloadButtonClick}>
              전체항목
            </button>
            <button onClick={this.props.onDownloadBankTransferOnlyButtonClick}>
              계좌정보
            </button>
          </li>
        </ul>
      </div>
    );
  }

  render() {
    const { className } = this.props;
    return (
      <div className={cn('query-button-group btn-group btn-group-sm', className)}>
        {this.renderQueryButton()}
        {this.renderDownloadButton()}
      </div>
    );
  }
}
