import React from 'react';
import axios from 'axios';
import Dropzone from 'react-dropzone';
import cn from 'classnames';

class MyInfo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      info: undefined,
      inputValues: {},
      editMode: {},
      saving: {},
      uploadProgress: 0,
    };
  }

  async componentDidMount() {
    const { data: info } = await axios.get('/users/me');
    this.setState({ info });
  }

  async onDropImage(files) {
    if (!files || !files.length) {
      return;
    }

    const file = files[0];

    if (file.size > 5000000) { // 5MB
      alert('파일 용량은 5메가를 초과할 수 없습니다.');
      return;
    }

    const data = new FormData();
    data.append('uid', this.state.info.uid);
    data.append('files[]', file);

    try {
      const { data: imageUrl } = await axios.post('/users/image_upload', data, {
        onUploadProgress: (progressEvent) => {
          const uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          this.setState({ uploadProgress });
        },
      });
      alert('업로드 완료');
      this.updateInfo('image', imageUrl);
    } catch (err) {
      alert('서버와 통신 중 문제가 발생했습니다');
    }
  }

  setEditMode(key, editable) {
    this.setState({
      editMode: {
        ...this.state.editMode,
        [key]: editable,
      },
      inputValues: {
        ...this.state.inputValues,
        [key]: this.state.info[key],
      },
    });
  }

  setSaving(key, saving) {
    this.setState({
      saving: {
        ...this.state.saving,
        [key]: saving,
      },
    });
  }

  updateInfo(key, value) {
    this.setState({
      info: {
        ...this.state.info,
        [key]: value,
      },
    });
  }

  updateInputValue(key, value) {
    this.setState({
      inputValues: {
        ...this.state.inputValues,
        [key]: value,
      },
    });
  }

  async save(key) {
    this.setSaving(key, true);
    const { info, inputValues } = this.state;
    try {
      const { data: result } = await axios.post('/users/edit', {
        pk: info.uid,
        name: key,
        value: inputValues[key],
      });
      this.updateInfo(key, result);
      this.setEditMode(key, false);
    } catch (err) {
      if (err.response) {
        alert(`업데이트 실패! status:${err.response.status} error:${err.response.statusText}`);
      } else if (err.request) {
        alert(`업데이트 실패! status:${err.request.status} error:${err.request.statusText}`);
      } else {
        alert(`업데이트 실패! message:${err.message}`);
      }
    } finally {
      this.setSaving(key, false);
    }
  }

  renderSaveCancelButton(key) {
    return (
      <React.Fragment>
        <button
          className="btn btn-primary btn-sm"
          type="button"
          disabled={this.state.saving[key]}
          onClick={() => this.save(key)}
        >
          {this.state.saving[key] ? '기록 중..' : '저장'}
        </button>
        <button
          className="btn btn-default btn-sm"
          type="button"
          disabled={this.state.saving[key]}
          onClick={() => this.setEditMode(key, false)}
        >
          취소
        </button>
      </React.Fragment>
    );
  }

  renderDataListItem({ name, key, readOnly = false }) {
    const { info, inputValues, editMode } = this.state;
    return (
      <div className="form-group">
        <dt className="col-sm-2 control-label">{name}</dt>
        <dd className="col-sm-10">
          <div className={cn({ 'input-group': !readOnly })}>
            <input
              className="form-control input-sm"
              value={(editMode[key] ? inputValues[key] : info[key]) || ''}
              onChange={e => this.updateInputValue(key, e.target.value)}
              readOnly={readOnly || !editMode[key]}
            />
            {!readOnly && (editMode[key] ? (
              <span className="input-group-btn">
                {this.renderSaveCancelButton(key)}
              </span>
            ) : (
              <span className="input-group-btn">
                <button
                  className="btn btn-default btn-sm"
                  onClick={() => this.setEditMode(key, true)}
                >
                  편집
                </button>
              </span>
            ))}
          </div>
        </dd>
      </div>
    );
  }

  renderContent() {
    const { info, inputValues, editMode } = this.state;
    if (!info) {
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
            로딩중..
          </div>
        </div>
      );
    }
    return (
      <div className="form-horizontal">
        <dl>
          <div className="form-group">
            <div className="col-sm-10 col-sm-offset-2">
              <Dropzone
                ref={(ref) => {
                  this.dropzone = ref;
                }}
                style={{
                  display: 'inline-block',
                  border: 'none',
                  marginBottom: 8,
                }}
                accept="image/jpeg, image/png, image/gif"
                multiple={false}
                onDrop={(...args) => this.onDropImage(...args)}
              >
                <img
                  className="img-responsive img-rounded"
                  src={info.image || 'https://placehold.it/300x300'}
                  alt=""
                />
              </Dropzone>
              <div>
                <button
                  className="btn btn-default btn-xs"
                  onClick={() => this.dropzone.open()}
                >
                  <i className="glyphicon glyphicon-upload" />
                  <span>사진 변경</span>
                  <span style={{ display: 'none' }}>{this.state.uploadProgress}%</span>
                </button>
              </div>
            </div>
          </div>

          {this.renderDataListItem({ name: '이름', key: 'name', readOnly: true })}
          {this.renderDataListItem({ name: '팀', key: 'team', readOnly: true })}
          {this.renderDataListItem({ name: '생년월일', key: 'birth' })}
          {this.renderDataListItem({ name: '전화번호', key: 'mobile' })}
          {this.renderDataListItem({ name: '이메일', key: 'email', readOnly: true })}

          <div className="form-group">
            <dt className="col-sm-2 control-label">소개</dt>
            <dd className="col-sm-10">
              <div className="input-group">
                <textarea
                  className="form-control input-sm"
                  style={{
                    resize: 'vertical',
                    minHeight: 100,
                  }}
                  value={editMode.comment ? (inputValues.comment || '') : (info.comment || '[내용이 없습니다.]')}
                  onChange={e => this.updateInputValue('comment', e.target.value)}
                  readOnly={!editMode.comment}
                />
                {editMode.comment ? (
                  <span className="input-group-addon btn-group-vertical">
                    {this.renderSaveCancelButton('comment')}
                  </span>
                ) : (
                  <span className="input-group-addon btn-group">
                    <button
                      className="btn btn-default btn-sm"
                      onClick={() => this.setEditMode('comment', true)}
                    >
                      편집
                    </button>
                  </span>
                )}
              </div>
            </dd>
          </div>
        </dl>
      </div>
    );
  }

  render() {
    return (
      <div className="container">
        <div className="row">
          <div className="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
            <div className="page-header">
              <h1>내정보</h1>
            </div>
            {this.renderContent()}
          </div>
        </div>
      </div>
    );
  }
}

export default MyInfo;
