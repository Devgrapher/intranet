import React from 'react';
import axios from 'axios';
import Dropzone from 'react-dropzone';
import '../../../css/myInfo.css';

class MyInfo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      info: {},
      inputValues: {},
      editMode: {},
      saving: {},
      uploadProgress: 0,
    };
  }

  componentDidMount() {
    (async () => {
      const { data: info } = await axios.get('/users/me/info');
      this.setState({ info });
    })();
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
          className="btn btn-xs save"
          disabled={this.state.saving[key]}
          onClick={() => this.save(key)}
        >
          {this.state.saving[key] ? '기록 중..' : '저장'}
        </button>
        <button
          className="btn btn-xs cancel"
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
      <React.Fragment>
        <dt>
          <h5 className="input_title">{name}</h5>
        </dt>
        <dd className="list-group-item">
          {editMode[key] ? (
            <div className="edit">
              <input
                className="content"
                type="text"
                value={inputValues[key] || ''}
                onChange={e => this.updateInputValue(key, e.target.value)}
              />
              <div className="pull-right">
                {this.renderSaveCancelButton(key)}
              </div>
            </div>
          ) : (
            <div className="normal">
              <span>{info[key]}</span>
              {!readOnly && (
                <div className="pull-right">
                  <button
                    className="btn btn-xs edit"
                    onClick={() => this.setEditMode(key, true)}
                  >
                    편집
                  </button>
                </div>
              )}
            </div>
          )}
        </dd>
      </React.Fragment>
    );
  }

  render() {
    const { info, inputValues, editMode } = this.state;
    return (
      <div className="container">
        <div className="myinfo">
          <header className="page-header">
            <h1 className="page-title">{info.name}</h1>
          </header>
          <div className="row">
            <div className="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
              <div className="panel panel-default">

                <div className="panel-heading">
                  <div className="row">
                    <div className="col-lg-12">
                      <div className="col-xs-12 col-sm-4">
                        <Dropzone
                          ref={(ref) => {
                            this.dropzone = ref;
                          }}
                          style={{ border: 'none' }}
                          accept="image/jpeg, image/png, image/gif"
                          multiple={false}
                          onDrop={(...args) => this.onDropImage(...args)}
                        >
                          <figure>
                            <img
                              className="img-responsive"
                              src={info.image || 'https://placehold.it/300x300'}
                              alt=""
                            />
                          </figure>
                        </Dropzone>

                        <button
                          className="btn btn-xs btn-primary upload-button"
                          onClick={() => this.dropzone.open()}
                        >
                          <i className="glyphicon glyphicon-upload" />
                          <span>사진 변경..</span>
                          <span style={{ display: 'none' }}>{this.state.uploadProgress}%</span>
                        </button>
                      </div>

                      <div className="col-xs-12 col-sm-8">
                        <dl className="dl-horizontal">
                          {this.renderDataListItem({ name: '이름', key: 'name', readOnly: true })}
                          {this.renderDataListItem({ name: '팀', key: 'team', readOnly: true })}
                          {this.renderDataListItem({ name: '생년월일', key: 'birth' })}
                          {this.renderDataListItem({ name: '전화번호', key: 'mobile' })}
                          {this.renderDataListItem({ name: '이메일', key: 'email', readOnly: true })}
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="panel-body">
                  <h4>소개</h4>
                  <div id="comment" className="list-group-item">
                    {editMode.comment ? (
                      <div className="edit">
                        <textarea
                          rows="5"
                          cols="100"
                          className="content"
                          value={inputValues.comment || ''}
                          onChange={e => this.updateInputValue('comment', e.target.value)}
                        />
                        <div className="pull-right">
                          {this.renderSaveCancelButton('comment')}
                        </div>
                      </div>
                    ) : (
                      <div className="normal">
                        <span>{info.comment || '[내용이 없습니다.]'}</span>
                        <button
                          className="btn btn-xs pull-right edit"
                          onClick={() => this.setEditMode('comment', true)}
                        >
                          편집
                        </button>
                      </div>
                    )}
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>

      </div>
    );
  }
}

export default MyInfo;
