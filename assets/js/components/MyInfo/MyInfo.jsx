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
          disabled={this.state.saving[key]}
          onClick={() => this.save(key)}
        >
          {this.state.saving[key] ? '기록 중..' : '저장'}
        </button>
        <button
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
      <div>
        <dt>{name}</dt>
        {editMode[key] ? (
          <dd>
            <input
              value={inputValues[key] || ''}
              onChange={e => this.updateInputValue(key, e.target.value)}
            />
            <span>
              {this.renderSaveCancelButton(key)}
            </span>
          </dd>
        ) : (
          <dd>
            <span>{info[key]}</span>
            {!readOnly && (
              <button
                onClick={() => this.setEditMode(key, true)}
              >
                편집
              </button>
            )}
          </dd>
        )}
      </div>
    );
  }

  render() {
    const { info, inputValues, editMode } = this.state;
    return (
      <div>
        <div>
          <Dropzone
            ref={(ref) => {
              this.dropzone = ref;
            }}
            style={{ border: 'none' }}
            accept="image/jpeg, image/png, image/gif"
            multiple={false}
            onDrop={(...args) => this.onDropImage(...args)}
          >
            <img
              src={info.image || 'https://placehold.it/300x300'}
              alt=""
            />
          </Dropzone>
          <button
            onClick={() => this.dropzone.open()}
          >
            <i className="glyphicon glyphicon-upload" />
            <span>사진 변경</span>
            <span style={{ display: 'none' }}>{this.state.uploadProgress}%</span>
          </button>
        </div>

        <dl>
          {this.renderDataListItem({ name: '이름', key: 'name', readOnly: true })}
          {this.renderDataListItem({ name: '팀', key: 'team', readOnly: true })}
          {this.renderDataListItem({ name: '생년월일', key: 'birth' })}
          {this.renderDataListItem({ name: '전화번호', key: 'mobile' })}
          {this.renderDataListItem({ name: '이메일', key: 'email', readOnly: true })}
          <div>
            <dt>소개</dt>
            {editMode.comment ? (
              <dd>
                <textarea
                  rows="5"
                  value={inputValues.comment || ''}
                  onChange={e => this.updateInputValue('comment', e.target.value)}
                />
                <span>
                  {this.renderSaveCancelButton('comment')}
                </span>
              </dd>
            ) : (
              <dd>
                <span>{info.comment || '[내용이 없습니다.]'}</span>
                <button
                  onClick={() => this.setEditMode('comment', true)}
                >
                  편집
                </button>
              </dd>
            )}
          </div>
        </dl>
      </div>
    );
  }
}

export default MyInfo;
